<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

class XTemplate
{
	public $filename = '';
	public $vars = array();
	protected $blocks = array();
	protected $displayed_blocks = array();
	protected $index = array();
	protected static $cache_enabled = false;
	protected static $cache_dir = '';
	protected static $debug_mode = false;
	private $found = false;

	public function __construct($path = NULL)
	{
		if (is_string($path))
		{
			$this->restart($path);
		}
	}

	public function __toString()
	{
		$str = '';
		foreach ($this->blocks as $name => $block)
		{
			$str .= "<!-- BEGIN: $name -->\n" . $block->__toString() . "<!-- END: $name -->\n";
		}
		return $str;
	}

	public function assign($name, $val = NULL, $prefix = '')
	{
		if (is_array($name))
		{
			foreach ($name as $key => $val)
			{
				$this->vars[$prefix.$key] = $val;
			}
		}
		else
		{
			$this->vars[$prefix.$name] = $val;
		}
	}

	public static function debug_var($name, $value, $length_limit = 60)
	{
		if (is_numeric($value))
		{
			$val_disp = (string) $value;
		}
		else
		{
			if (!is_string($value))
			{
				$value = (string) $value;
			}
			if (mb_strlen($value) > $length_limit)
			{
				$value = mb_substr($value, 0, $length_limit) . '...';
			}
			$val_disp = '&quot;' . htmlspecialchars($value) . '&quot;';
		}
		return  '<li>{' . htmlspecialchars($name) . '} =&gt; <em>' . $val_disp . '</em></li>';
	}

	public function get($name)
	{
		return $this->vars[$name];
	}

	public static function init($enable_cache = false, $cache_dir = '', $debug_mode = false, $cleanup = false)
	{
		self::$debug_mode = $debug_mode;
		self::$cache_enabled = $enable_cache && !$debug_mode;
		self::$cache_dir = $cache_dir;
		Cotpl_data::init($cleanup);
	}

	private static function restart_include_files($m)
	{
		if (preg_match('`\.tpl$`i', $m[2]))
		{
			$fname = preg_replace_callback('`\{([\w\.]+)\}`', 'XTemplate::substitute_var', $m[2]);
			if (file_exists($fname))
			{
				$code = cotpl_read_file($fname);
				if ($code[0] == chr(0xEF) && $code[1] == chr(0xBB) && $code[2] == chr(0xBF)) $code = mb_substr($code, 0);
				$code = preg_replace_callback('`\{FILE\s+("|\')(.+?)\1\}`', 'XTemplate::restart_include_files', $code);
				return $code;
			}
			return $fname;
		}

		return $m[0];
	}

	private function restart_root_blocks($m)
	{
		$name = $m[1];
		$text = trim($m[2]);
		$this->index[$name] = array($name);
		$this->blocks[$name] = new Cotpl_block($text, $this->index, array($name));
		$this->found = true;
		return '';
	}

	public function restart($path)
	{
		if (!file_exists($path))
		{
			throw new Exception("Template file not found: $path");
			return false;
		}
		$this->filename = $path;
		$this->vars = array();
		$cache_path = self::$cache_dir . '/templates/' . str_replace(array('./', '/'), '_', $path);
		$cache_idx = $cache_path . '.idx';
		if (!self::$cache_enabled || !file_exists($cache_path) || filemtime($path) > filemtime($cache_path))
		{
			$this->blocks = array();
			$this->index = array();
			$code = cotpl_read_file($path);
			if ($code[0] == chr(0xEF) && $code[1] == chr(0xBB) && $code[2] == chr(0xBF)) $code = mb_substr($code, 0);
			$code = preg_replace_callback('`\{FILE\s+("|\')(.+?)\1\}`', 'XTemplate::restart_include_files', $code);
			do
			{
				$this->found = false;
				$code = preg_replace_callback('`<!--\s*BEGIN:\s*([\w_]+)\s*-->(.*?)<!--\s*END:\s*\1\s*-->`s',
					array($this, 'restart_root_blocks'), $code);
			} while($this->found);

			if (self::$cache_enabled)
			{
				if (is_writeable(self::$cache_dir . '/templates/'))
				{
					file_put_contents($cache_path, serialize($this->blocks));
					file_put_contents($cache_idx, serialize($this->index));
				}
				else
				{
					throw new Exception('Your "' . self::$cache_dir . '/templates/" is not writable');
				}
			}
		}
		else
		{
			$this->blocks = unserialize(cotpl_read_file($cache_path));
			$this->index = unserialize(cotpl_read_file($cache_idx));
		}
	}

	private static function substitute_var($m)
	{
		$var = new Cotpl_var($m[1]);
		return $var->evaluate($this);
	}

	public function out($block = 'MAIN')
	{
		if (!self::$debug_mode)
		{
			echo $this->text($block);
		}
	}

	public function parse($block = 'MAIN')
	{
		global $theme_reload;
		if(is_array($theme_reload))
		{
			foreach($theme_reload as $key_reload => $val_reload)
			{
				$GLOBALS[$key_reload] = (is_array($GLOBALS[$key_reload]) && is_array($val_reload)) ? array_merge($GLOBALS[$key_reload], $val_reload) : $val_reload;
			}
		}
		$path = $this->index[$block];
		if ($path)
		{
			$blk = $this->blocks[array_shift($path)];
			foreach ($path as $node)
			{
				if (is_object($blk))
				{
					$blk =& $blk->blocks[$node];
				}
				else
				{
					$blk =& $blk[$node];
				}
			}
			$blk->parse($this);
		}

		if (self::$debug_mode)
		{
			if (!in_array($block, $this->displayed_blocks))
			{
				$block_level = substr_count($block, '.');
				$block_name = basename($this->filename) . ' / ' . str_replace('.', ' / ', $block);
				echo "<h2>$block_name</h2>";
				echo "<ul>";
				$tags = $this->vars;
				ksort($tags);
				foreach ($tags as $key => $val)
				{
					if (is_array($val))
					{
						foreach ($val as $key2 => $val2)
						{
							echo self::debug_var($key . '.' . $key2, $val2);
						}
					}
					else
					{
						echo self::debug_var($key, $val);
					}
				}
				echo "</ul>";
				$this->displayed_blocks[] = $block;
			}
		}
	}

	public function reset($block = 'MAIN')
	{
		$path = $this->index[$block];
		if ($path)
		{
			$blk = $this->blocks[array_shift($path)];
			foreach ($path as $node)
			{
				if (is_object($blk))
				{
					$blk =& $blk->blocks[$node];
				}
				else
				{
					$blk =& $blk[$node];
				}
			}
			$blk->reset();
		}
	}

	public function text($block = 'MAIN')
	{
		$path = $this->index[$block];
		if ($path)
		{
			$blk = $this->blocks[array_shift($path)];
			foreach ($path as $node)
			{
				if (is_object($blk))
				{
					$blk =& $blk->blocks[$node];
				}
				else
				{
					$blk =& $blk[$node];
				}
			}
			return $blk->text($this);
		}
		else
		{
			return '';
		}
	}
}

class Cotpl_block
{
	protected $data = array();
	public $blocks = array();

	public function __construct($code, &$index, $path)
	{
		$this->compile($code, $this->blocks, $index, $path);
	}

	public function  __toString()
	{
		return $this->blocks_toString($this->blocks);
	}

	protected function blocks_toString(&$blocks)
	{
		$str = '';
		foreach ($blocks as $name => $block)
		{
			if (is_string($name) && !is_numeric($name))
			{
				$str .= "<!-- BEGIN: $name -->\n" . $block->__toString() . "<!-- END: $name -->\n";
			}
			else
			{
				$str .= $block->__toString();
			}
		}
		return $str;
	}

	protected function compile($code, &$blocks, &$index, $path)
	{
		$i = 0;
		do
		{
			$block_found = false;
			$loop_found = false;
			$log_found = false;
			if (preg_match('`<!--\s*BEGIN:\s*([\w_]+)\s*-->(.*?)<!--\s*END:\s*\1\s*-->`s', $code, $mt))
			{
				$block_found = true;
				$block_pos = mb_strpos($code, $mt[0]);
				$block_mt = $mt;
				$block_name = $mt[1];
			}
			if (preg_match('`<!--\s*FOR\s+(.+?)\s*-->`', $code, $mt))
			{
				$loop_found = true;
				$loop_pos = mb_strpos($code, $mt[0]);
				$loop_len = mb_strlen($mt[0]);
				$loop_mt = $mt;
			}
			if (preg_match('`<!--\s*IF\s+(.+?)\s*-->`', $code, $mt))
			{
				$log_found = true;
				$log_pos = mb_strpos($code, $mt[0]);
				$log_len = mb_strlen($mt[0]);
				$log_mt = $mt;
			}
			if ($block_found
					&& (!$loop_found || $block_pos < $loop_pos)
					&& (!$log_found || $block_pos < $log_pos))
			{
				if ($block_pos > 0)
				{
					$chunk = trim(mb_substr($code, 0, $block_pos), "\t\r\n");
					if (!empty($chunk))
					{
						$blocks[$i++] = new Cotpl_data($chunk);
					}
				}
				$bpath = $path;
				array_push($bpath, $block_name);
				$index[cotpl_index_glue($bpath)] = $bpath;
				$blocks[$block_name] = new Cotpl_block(trim($block_mt[2]), $index, $bpath);
				$code = trim(mb_substr($code, $block_pos + mb_strlen($block_mt[0])));
			}
			elseif ($loop_found
					&& (!$log_found || $loop_pos < $log_pos))
			{
				if ($loop_pos > 0)
				{
					$chunk = trim(mb_substr($code, 0, $loop_pos), "\t\r\n");
					if (!empty($chunk))
					{
						$blocks[$i++] = new Cotpl_data($chunk);
					}
				}
				$scope = 1;
				$loop_code = '';
				$code = mb_substr($code, $loop_pos + $loop_len);
				while ($scope > 0 && preg_match('`<!--\s*(FOR\s+.+?|ENDFOR)\s*-->`', $code, $m))
				{
						$m_pos = mb_strpos($code, $m[0]);
						$m_len = mb_strlen($m[0]);
						if ($m[1] === 'ENDFOR')
						{
							$scope--;
						}
						else
						{
							$scope++;
						}
						$postfix_len = $scope === 0 ? 0 : $m_len;
						$loop_code .= mb_substr($code, 0, $m_pos + $postfix_len);
						$code = mb_substr($code, $m_pos + $m_len);
				}
				if ($scope === 0)
				{
					$bpath = $path;
					array_push($bpath, $i);
					$blocks[$i++] = new Cotpl_loop($loop_mt[1], $loop_code, $index, $bpath);
					$code = trim($code, "\t\r\n");
				}
				else
				{
					throw new Exception('Loop ' . htmlspecialchars($loop_mt[0]) . ' not closed');
				}
			}
			elseif ($log_found)
			{
				if ($log_pos > 0)
				{
					$chunk = trim(mb_substr($code, 0, $log_pos), "\t\r\n");
					if (!empty($chunk))
					{
						$blocks[$i++] = new Cotpl_data($chunk);
					}
				}
				$scope = 1;
				$if_code = '';
				$else_code = '';
				$else = false;
				$code = mb_substr($code, $log_pos + $log_len);
				while ($scope > 0 && preg_match('`<!--\s*(IF\s+.+?|ELSE|ENDIF)\s*-->`', $code, $m))
				{
						$m_pos = mb_strpos($code, $m[0]);
						$m_len = mb_strlen($m[0]);
						if ($m[1] === 'ENDIF')
						{
							$scope--;
						}
						elseif ($m[1] === 'ELSE')
						{
							if ($scope === 1)
							{
								$if_code .= mb_substr($code, 0, $m_pos);
								$else = true;
								$code = mb_substr($code, $m_pos + $m_len);
								continue;
							}
						}
						else
						{
							$scope++;
						}
						$postfix_len = $scope === 0 ? 0 : $m_len;
						if ($else === false)
						{
							$if_code .= mb_substr($code, 0, $m_pos + $postfix_len);
						}
						else
						{
							$else_code .= mb_substr($code, 0, $m_pos + $postfix_len);
						}
						$code = mb_substr($code, $m_pos + $m_len);
				}
				if ($scope === 0)
				{
					$bpath = $path;
					array_push($bpath, $i);
					$blocks[$i++] = new Cotpl_logical($log_mt[1], $if_code, $else_code, $index, $bpath);
					$code = trim($code, "\t\r\n");
				}
				else
				{
					throw new Exception('Logical block ' . htmlspecialchars($log_mt[0]) . ' not closed');
				}
			}
			else
			{
				$code = trim($code, "\t\r\n");
				if (!empty($code))
				{
					$blocks[$i++] = new Cotpl_data($code);
					$code = '';
				}
			}
		}
		while (!empty($code));
	}

	public function parse($tpl)
	{
		foreach ($this->blocks as $block)
		{
			$data .= $block->text($tpl);
		}
		$this->data[] = $data;
	}

	public function reset($path = array())
	{
		$this->data = array();
	}

	public function text($tpl)
	{
		$text = implode('', $this->data);
		$this->data = array();
		return $text;
	}
}

class Cotpl_data
{
	protected $chunks = array();
	protected static $cleanup_enabled = false;

	public function __construct($code)
	{
		if (self::$cleanup_enabled)
		{
			$code = $this->cleanup($code);
		}
		$chunks = preg_split('`(\{(?:[\w\.]+)(?:\|.+?)?\})`', $code, -1, PREG_SPLIT_DELIM_CAPTURE);
		foreach ($chunks as $chunk)
		{
			if (preg_match('`^\{((?:[\w\.]+)(?:\|.+?)?)\}$`', $chunk, $m))
			{
				$this->chunks[] = new Cotpl_var($m[1]);
			}
			else
			{
				$this->chunks[] = $chunk;
			}
		}
	}

	public function  __toString()
	{
		$str = '';
		foreach ($this->chunks as $chunk)
		{
			if ($chunk instanceof Cotpl_var)
			{
				$str .= $chunk->__toString();
			}
			else
			{
				$str .= $chunk;
			}
		}
		return $str . "\n";
	}

	public static function init($cleanup_enabled = false)
	{
		self::$cleanup_enabled = $cleanup_enabled;
	}

	public function text($tpl)
	{
		$data = '';
		foreach ($this->chunks as $chunk)
		{
			if ($chunk instanceof Cotpl_var)
			{
				$data .= $chunk->evaluate($tpl);
			}
			else
			{
				$data .= $chunk;
			}
		}
		return $data;
	}

	private function cleanup($html)
	{
		$html = preg_replace('#\n\s+#', ' ', $html);
		$html = preg_replace('#[\r\n\t]+<#', '<', $html);
		$html = preg_replace('#>[\r\n\t]+#', '>', $html);
		$html = preg_replace('# {2,}#', ' ', $html);
		return $html;
	}
}

define('COTPL_OP_OPEN', 1);
define('COTPL_OP_CLOSE', 2);
define('COTPL_OP_AND', 11);
define('COTPL_OP_OR', 12);
define('COTPL_OP_XOR', 13);
define('COTPL_OP_NOT', 14);
define('COTPL_OP_EQ', 21);
define('COTPL_OP_NE', 22);
define('COTPL_OP_LT', 23);
define('COTPL_OP_GT', 24);
define('COTPL_OP_LE', 25);
define('COTPL_OP_GE', 26);
define('COTPL_OP_HAS', 31);
define('COTPL_OP_CONTAINS', 32);
define('COTPL_OP_ADD', 41);
define('COTPL_OP_SUB', 42);
define('COTPL_OP_MUL', 43);
define('COTPL_OP_DIV', 44);
define('COTPL_OP_MOD', 45);

class Cotpl_expr
{
	protected $tokens = array();

	protected static $operators = array(
		'('		=> COTPL_OP_OPEN,
		')'		=> COTPL_OP_CLOSE,
		'AND'	=> COTPL_OP_AND,
		'OR'	=> COTPL_OP_OR,
		'XOR'	=> COTPL_OP_XOR,
		'!'		=> COTPL_OP_NOT,
		'=='	=> COTPL_OP_EQ,
		'!='	=> COTPL_OP_NE,
		'<'		=> COTPL_OP_LT,
		'>'		=> COTPL_OP_GT,
		'<='	=> COTPL_OP_LE,
		'>='	=> COTPL_OP_GE,
		'HAS'	=> COTPL_OP_HAS,
		'~='	=> COTPL_OP_CONTAINS,
		'+'		=> COTPL_OP_ADD,
		'-'		=> COTPL_OP_SUB,
		'*'		=> COTPL_OP_MUL,
		'/'		=> COTPL_OP_DIV,
		'%'		=> COTPL_OP_MOD
	);

	protected static $precedence = array(
		COTPL_OP_OPEN => -1,
		COTPL_OP_MUL => 1, COTPL_OP_DIV => 1, COTPL_OP_MOD => 1,
		COTPL_OP_ADD => 2, COTPL_OP_SUB => 2,
		COTPL_OP_HAS => 3, COTPL_OP_CONTAINS => 3,
		COTPL_OP_EQ => 4, COTPL_OP_NE => 4, COTPL_OP_LT => 4, COTPL_OP_GT => 4, COTPL_OP_LE => 4, COTPL_OP_GE => 4,
		COTPL_OP_NOT => 5,
		COTPL_OP_AND => 6,
		COTPL_OP_OR => 7, COTPL_OP_XOR => 7,
		COTPL_OP_CLOSE => 99
	);

	public function __construct($text)
	{
		$text = str_replace('(', ' ( ', $text);
		$text = str_replace(')', ' ) ', $text);
		$text = str_replace('!{', ' ! {', $text);
		$text = str_replace('!(', ' ! (', $text);
		$words = cotpl_tokenize($text, array(' ', "\t"));
		$operators = array_keys(self::$operators);
		$tokens = array();
		foreach ($words as $word)
		{
			$token = array();
			if (in_array($word, $operators, true))
			{
				$op = self::$operators[$word];
				$token['op'] = $op;
				$token['prec'] = self::$precedence[$op];
			}
			else
			{
				if (preg_match('`^{(.+?)}$`', $word, $mt))
				{
					$token['var'] = new Cotpl_var($mt[1]);
				}
				elseif (preg_match('`("|\')(.+?)\1`', $word, $mt))
				{
					$token['var'] = $mt[2];
				}
				elseif (is_numeric($word))
				{
					$token['var'] = (double) $word;
				}
				else
				{
					$token['var'] = $word;
				}
				$token['prec'] = 0;
			}
			$tokens[] = $token;
		}
		$lim = count($tokens) - 1;
		for ($i = 0; $i < $lim; $i++)
		{
			if ($tokens[$i]['prec'] > $tokens[$i + 1]['prec'])
			{
				$j = $i;
				$scopes = 0;
				while ($j < $lim && ($scopes > 0 || $tokens[$j]['prec'] > $tokens[$j + 1]['prec']))
				{
					$tmp = $tokens[$j];
					$tokens[$j] = $tokens[$j + 1];
					$tokens[$j + 1] = $tmp;
					$scopes += (($tokens[$j]['op'] == COTPL_OP_OPEN) ? 1 : 0)
						- (($tokens[$j]['op'] == COTPL_OP_CLOSE) ? 1 : 0);
					$j++;
				}
				$i--;
			}
		}

		$this->tokens = $tokens;
	}

	public function  __toString()
	{
		$str = '';
		foreach ($this->tokens as $tok)
		{
			$str .= $tok['var'] ? (string) $tok['var'] : array_search($tok['op'], self::$operators);
		}
		return $str;
	}

	public function evaluate($tpl)
	{
		$stack = array();
		foreach ($this->tokens as $token)
		{
			switch ($token['op'])
			{
				case COTPL_OP_ADD:
					array_push($stack, array_pop($stack) + array_pop($stack));
					break;
				case COTPL_OP_AND:
					array_push($stack, array_pop($stack) && array_pop($stack));
					break;
				case COTPL_OP_CONTAINS:
					$needle = array_pop($stack);
					$haystack = array_pop($stack);
					array_push($stack, is_string($haystack) && is_string($needle)
						&& mb_strpos($haystack, $needle) !== false);
					break;
				case COTPL_OP_DIV:
					$divisor = array_pop($stack);
					$dividend = array_pop($stack);
					array_push($stack, $dividend / $divisor);
					break;
				case COTPL_OP_EQ:
					array_push($stack, array_pop($stack) == array_pop($stack));
					break;
				case COTPL_OP_GE:
					$arg2 = array_pop($stack);
					$arg1 = array_pop($stack);
					array_push($stack, $arg1 >= $arg2);
					break;
				case COTPL_OP_GT:
					$arg2 = array_pop($stack);
					$arg1 = array_pop($stack);
					array_push($stack, $arg1 > $arg2);
					break;
				case COTPL_OP_HAS:
					$needle = array_pop($stack);
					$haystack = array_pop($stack);
					array_push($stack, is_array($haystack) && in_array($needle, $haystack));
					break;
				case COTPL_OP_LE:
					$arg2 = array_pop($stack);
					$arg1 = array_pop($stack);
					array_push($stack, $arg1 <= $arg2);
					break;
				case COTPL_OP_LT:
					$arg2 = array_pop($stack);
					$arg1 = array_pop($stack);
					array_push($stack, $arg1 < $arg2);
					break;
				case COTPL_OP_MOD:
					$arg2 = array_pop($stack);
					$arg1 = array_pop($stack);
					array_push($stack, $arg1 % $arg2);
					break;
				case COTPL_OP_MUL:
					array_push($stack, array_pop($stack) * array_pop($stack));
					break;
				case COTPL_OP_NE:
					array_push($stack, array_pop($stack) != array_pop($stack));
					break;
				case COTPL_OP_NOT:
					array_push($stack, !array_pop($stack));
					break;
				case COTPL_OP_OR:
					$arg2 = array_pop($stack);
					$arg1 = array_pop($stack);
					array_push($stack, ($arg1 || $arg2));
					break;
				case COTPL_OP_SUB:
					$sub = array_pop($stack);
					$min = array_pop($stack);
					array_push($stack, $min - $sub);
					break;
				case COTPL_OP_XOR:
					$arg2 = array_pop($stack);
					$arg1 = array_pop($stack);
					array_push($stack, ($arg1 xor $arg2));
					break;
				case COTPL_OP_OPEN:
				case COTPL_OP_CLOSE:
					break;
				default:
					array_push($stack, is_object($token['var']) ? $token['var']->evaluate($tpl) : $token['var']);
					break;
			}
		}
		return (bool) array_pop($stack);
	}
}

class Cotpl_logical extends Cotpl_block
{
	protected $expr = null;

	public function __construct($expr_str, $if_code, $else_code, &$index, $path)
	{
		$this->expr = new Cotpl_expr($expr_str);
		if (!empty($if_code))
		{
			$bpath = $path;
			array_push($bpath, 0);
			$this->compile($if_code, $this->blocks[0], $index, $bpath);
		}
		if (!empty($else_code))
		{
			$bpath = $path;
			array_push($bpath, 1);
			$this->compile($else_code, $this->blocks[1], $index, $bpath);
		}
	}

	public function  __toString()
	{
		$str = "<!-- IF " . $this->expr->__toString() . " -->\n";
		$str .= $this->blocks_toString($this->blocks[0]);
		if (count($this->blocks[1]) > 0)
		{
			$str .= "<!-- ELSE -->\n" . $this->blocks_toString($this->blocks[1]);
		}
		$str .= "<!-- ENDIF -->\n";
		return $str;
	}

	public function parse($xtpl)
	{
		throw new Exception('Calling parse() on logical block');
	}

	public function reset($dummy = null)
	{
		throw new Exception('Calling reset() on logical block');
	}

	public function text($tpl)
	{
		$data = '';
		if ($this->expr->evaluate($tpl))
		{
			if ($this->blocks[0])
			{
				foreach ($this->blocks[0] as $block)
				{
					$data .= $block->text($tpl);
				}
			}
		}
		elseif ($this->blocks[1])
		{
			foreach ($this->blocks[1] as $block)
			{
				$data .= $block->text($tpl);
			}
		}
		return $data;
	}
}

class Cotpl_loop extends Cotpl_block
{
	protected $key = '';
	protected $set = null;
	protected $val = '';
	
	public function __construct($header, $code, &$index, $path)
	{
		if (preg_match('`^\{(\w+)\}\s*,\s*\{(\w+)\}\s*IN\s*\{((?:[\w\.]+)(?:\|.+?)?)\}$`', $header, $m))
		{
			$this->key = $m[1];
			$this->val = $m[2];
			$this->set = new Cotpl_var($m[3]);
		}
		elseif (preg_match('`^\{(\w+)\}\s*IN\s*\{((?:[\w\.]+)(?:\|.+?)?)\}$`', $header, $m))
		{
			$this->val = $m[1];
			$this->set = new Cotpl_var($m[2]);
		}
		$this->compile($code, $this->blocks, $index, $path);
	}
	
	public function  __toString()
	{
		$header = empty($this->key) ? '{' . $this->val . '}'
				: '{' . $this->key . '}, {' . $this->val . '}';
		$str = "<!-- FOR $header IN " . $this->set->__toString() . " -->\n";
		$str .= $this->blocks_toString($this->blocks);
		$str .= "<!-- ENDFOR -->\n";
		return $str;
	}

	public function parse($xtpl)
	{
		throw new Exception('Calling parse() on a loop');
	}

	public function reset($dummy = null)
	{
		throw new Exception('Calling reset() on a loop');
	}

	public function text($tpl)
	{
		$data = '';
		$set = $this->set->evaluate($tpl);
		if (is_array($set) && $this->blocks)
		{
			foreach ($set as $key => $val)
			{
				$tpl->assign($this->val, $val);
				if (!empty($this->key))
				{
					$tpl->assign($this->key, $key);
				}
				foreach ($this->blocks as $block)
				{
					$data .= $block->text($tpl);
				}
			}
		}
		return $data;
	}
}

class Cotpl_var
{
	protected $name = '';
	protected $keys = null;
	protected $callbacks = null;

	public function __construct($text)
	{
		if (mb_strpos($text, '|') !== false)
		{
			$chain = explode('|', $text);
			$text = array_shift($chain);
			foreach ($chain as $cbk)
			{
				if (mb_strpos($cbk, '(') !== false
					&& preg_match('`(\w+)\s*\((.+?)\)`', $cbk, $mt))
				{
					$this->callbacks[] = array(
						'name' => $mt[1],
						'args' => cotpl_tokenize(trim($mt[2]), array(',', ' '))
					);
				}
				else
				{
					$this->callbacks[] = str_replace('()', '', $cbk);
				}
			}
		}
		if (mb_strpos($text, '.') !== false)
		{
			$keys = explode('.', $text);
			$text = array_shift($keys);
			$this->keys = $keys;
		}
		$this->name = $text;
	}

	public function  __toString()
	{
		$str = '{' . $this->name;
		if (is_array($this->keys))
		{
			$str .= '.' . implode('.', $this->keys);
		}
		if (is_array($this->callbacks))
		{
			foreach ($this->callbacks as $cb)
			{
				if (is_array($cb))
				{
					$str .= '|' . $cb['name'] . '(' . implode(',', $cb['args']) . ')';
				}
				else
				{
					$str .= '|' . $cb;
				}
			}
		}
		$str .= '}';
		return $str;
	}

	private function dump($val)
	{
		$key = $this->keys ? $this->name . '.' . implode('.', $this->keys) : $this->name;
		if ($this->name == 'PHP' && !$this->keys)
		{
			$val =& $GLOBALS;
		}
		return '<ul class="dump">' . self::dump_r($key, $val, 0) . '</ul>';
	}

	private static function dump_r($key, $val, $level)
	{
		if ($level > 5 || $key == 'PHP.GLOBALS')
		{
			return '';
		}
		$ret = '';
		if (is_array($val))
		{
			ksort($val);
			foreach ($val as $key2 => $val2)
			{
				$ret .= self::dump_r($key . '.' . $key2, $val2, $level + 1);
			}
		}
		elseif (is_string($val))
		{
			$ret = XTemplate::debug_var($key, $val);
		}
		return $ret;
	}

	public function evaluate($tpl)
	{
		if ($this->name === 'PHP')
		{
			$var =& $GLOBALS;
			$val = null;
		}
		else
		{
			$val = $tpl->vars[$this->name];
			if ($this->keys && is_array($val))
			{
				$var =& $tpl->vars[$this->name];
			}
		}
		if ($this->keys)
		{
			$keys = $this->keys;
			$last_key = array_pop($keys);
			foreach ($keys as $key)
			{
				if (is_object($var))
				{
					$var =& $var->{$key};
				}
				elseif (is_array($var))
				{
					$var =& $var[$key];
				}
				else
				{
					break;
				}
			}
			if (is_object($var))
			{
				$val = $var->{$last_key};
			}
			elseif (is_array($var))
			{
				$val = $var[$last_key];
			}
			else
			{
				$val = null;
			}
		}
		if ($this->callbacks)
		{
			foreach ($this->callbacks as $func)
			{
				if (is_array($func))
				{
					array_walk($func['args'], 'cotpl_callback_replace', $val);
					$f = $func['name'];
					$a = $func['args'];
					switch (count($a))
					{
						case 0:
							$val = $f();
							break;
						case 1:
							$val = $f($a[0]);
							break;
						case 2:
							$val = $f($a[0], $a[1]);
							break;
						case 3:
							$val =$f($a[0], $a[1], $a[2]);
							break;
						case 4:
							$val = $f($a[0], $a[1], $a[2], $a[3]);
							break;
						default:
							$val = call_user_func_array($f, $a);
							break;
					}
				}
				elseif ($func == 'dump')
				{
					$val = $this->dump($val);
				}
				else
				{
					$val = $func($val);
				}
			}
		}
		return $val;
	}
}

function cotpl_callback_replace(&$arg, $i, $val)
{
	if (mb_strpos($arg, '$this') !== FALSE)
	{
		$arg = str_replace('$this', (string)$val, $arg);
	}
}

function cotpl_read_file($path)
{
	$fp = fopen($path, 'r');
	$code = fread($fp, filesize($path));
	fclose($fp);
	return $code;
}

function cotpl_index_glue($path)
{
	$str = array_shift($path);
	foreach ($path as $node)
	{
		if (!is_numeric($node))
		{
			$str .= '.' . $node;
		}
	}
	return $str;
}

function cotpl_tokenize($str, $delim = array(' '))
{
	$tokens = array();
	$idx = 0;
	$quote = '';
	$prev_delim = false;
	$len = mb_strlen($str);
	for ($i = 0; $i < $len; $i++)
	{
		$c = mb_substr($str, $i, 1);
		if (in_array($c, $delim))
		{
			if ($quote)
			{
				$tokens[$idx] .= $c;
				$prev_delim = false;
			}
			elseif ($prev_delim)
			{
				continue;
			}
			else
			{
				$idx++;
				$prev_delim = true;
			}
		}
		elseif ($c == '"' || $c == "'")
		{
			if (!$quote)
			{
				$quote = $c;
			}
			elseif ($quote == $c)
			{
				$quote = '';
			}
			else
			{
				$tokens[$idx] .= $c;
			}
			$prev_delim = false;
		}
		elseif ($c == '{' && !$quote)
		{
			$quote = $c;
			$tokens[$idx] .= $c;
			$prev_delim = false;
		}
		elseif ($c == '}' && $quote)
		{
			$quote = '';
			$tokens[$idx] .= $c;
			$prev_delim = false;
		}
		else
		{
			$tokens[$idx] .= $c;
			$prev_delim = false;
		}
	}
	return $tokens;
}

?>