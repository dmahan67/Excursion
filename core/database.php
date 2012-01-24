<?PHP 
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
class DB extends PDO {

	private $_affected_rows = 0;
	private $_count = 0;
	private $_prepare_itself = false;
	private $_tcount = 0;
	private $_xtime = 0;

	public function  __construct($dsn, $username, $passwd, $options = array())
	{
		global $config;
		if (!empty($config['mysqlcharset']) && version_compare(PHP_VERSION, '5.3.0', '!='))
		{
			$collation_query = "SET NAMES '{$config['mysqlcharset']}'";
			if (!empty($config['mysqlcollate']) )
			{
				$collation_query .= " COLLATE '{$config['mysqlcollate']}'";
			}
			$options[PDO::MYSQL_ATTR_INIT_COMMAND] = $collation_query;
		}
		parent::__construct($dsn, $username, $passwd, $options);
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		if (version_compare($this->getAttribute(PDO::ATTR_CLIENT_VERSION), '5.1.0', '<'))
		{
			$this->_prepare_itself = true;
		}
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'affectedRows':
				return $this->_affected_rows;
				break;
			case 'count':
				return $this->_count;
				break;
			case 'timeCount':
				return $this->_tcount;
				break;
			default:
				return null;
		}
	}

	private function _bindParams($statement, $parameters)
	{
		$is_numeric = is_int(key($parameters));
		foreach ($parameters as $key => $val)
		{
			$type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
			$is_numeric ? $statement->bindValue($key + 1, $val, $type) : $statement->bindValue($key, $val, $type);
		}
	}

	private function _parseError(PDOException $e, &$err_code, &$err_message)
	{
		$pdo_message = $e->getMessage();
		if (preg_match('#SQLSTATE\[(\w+)\].*?: (.*)#', $pdo_message, $matches))
		{
            $err_code = $matches[1];
            $err_message = $matches[2];
        }
		else
		{
			$err_code = $e->getCode();
			$err_message = $pdo_message;
		}
		return $err_code > '02';
	}

	private function _prepare($query, $parameters = array())
	{
		if (count($parameters) > 0)
		{
			foreach ($parameters as $key => $val)
			{
				$placeholder = is_int($key) ? '?' : ':' . $key;
				$value = is_int($val) ? $val : $this->quote($val);
				$query = preg_replace('`' . preg_quote($placeholder) . '`', $value, $query, 1);
			}
		}
		return $query;
	}

	private function _startTimer()
	{
		global $config;
		$this->_count++;
		if ($config['showsqlstats'])
		{
			$this->_xtime = microtime();
		}
	}

	private function _stopTimer($query)
	{
		global $config, $usr, $sys;
		if ($config['showsqlstats'])
		{
			$ytime = microtime();
			$xtime = explode(' ',$this->_xtime);
			$ytime = explode(' ',$ytime);
			$this->_tcount += $ytime[1] + $ytime[0] - $xtime[1] - $xtime[0];
			if ($config['devmode'] && $usr['isadmin'])
			{
				$sys['devmode']['queries'][] = array ($this->_count, $ytime[1] + $ytime[0] - $xtime[1] - $xtime[0], $query);
				$sys['devmode']['timeline'][] = $xtime[1] + $xtime[0] - $sys['starttime'];
			}
		}
	}

	public function countRows($table_name)
	{
		return $this->query("SELECT COUNT(*) FROM `$table_name`")->fetchColumn();
	}

	public function delete($table_name, $condition = '', $parameters = array())
	{
		$query = empty($condition) ? "DELETE FROM `$table_name`" : "DELETE FROM `$table_name` WHERE $condition";
		$this->_startTimer();
		try
		{
			if (count($parameters) > 0)
			{
				if ($this->_prepare_itself)
				{
					$res = $this->exec($this->_prepare($query, $parameters));
				}
				else
				{
					$stmt = $this->prepare($query);
					$this->_bindParams($stmt, $parameters);
					$stmt->execute();
					$res = $stmt->rowCount();
				}
			}
			else
			{
				$res = $this->exec($query);
			}
		}
		catch (PDOException $err)
		{
			if ($this->_parseError($err, $err_code, $err_message))
			{
				die('SQL error ' . $err_code . ': ' . $err_message);
			}
		}
		$this->_stopTimer($query);
		return $res;
	}

	function fieldExists($table_name, $field_name)
	{
		return $this->query("SHOW COLUMNS FROM `$table_name` WHERE Field = " . $this->quote($field_name))->rowCount() == 1;
	}

	public function insert($table_name, $data, $insert_null = false)
	{
		if (!is_array($data))
		{
			return 0;
		}
		$keys = '';
		$vals = '';

		$arr_keys = array_keys($data);
		$multiline = is_numeric($arr_keys[0]);

		if ($multiline)
		{
			$rowset = &$data;
		}
		else
		{
			$rowset = array($data);
		}
		$keys_built = false;
		$cnt = count($rowset);
		for ($i = 0; $i < $cnt; $i++)
		{
			$vals .= ($i > 0) ? ',(' : '(';
			$j = 0;
			if (is_array($rowset[$i]))
			{
				foreach ($rowset[$i] as $key => $val)
				{
					if ($j > 0) $vals .= ',';
					if (!$keys_built)
					{
						if ($j > 0) $keys .= ',';
						$keys .= "`$key`";
					}
					if (is_null($val) && $insert_null)
					{
						$vals .= 'NULL';
					}
					elseif ($val === 'NOW()')
					{
						$vals .= 'NOW()';
					}
					elseif (is_int($val) || is_float($val))
					{
						$vals .= $val;
					}
					else
					{
						$vals .= $this->quote($val);
					}
					$j++;
				}
			}
			$vals .= ')';
			$keys_built = true;
		}
		if (!empty($keys) && !empty($vals))
		{
			$query = "INSERT INTO `$table_name` ($keys) VALUES $vals";
			$this->_startTimer();
			try
			{
				$res = $this->exec($query);
			}
			catch (PDOException $err)
			{
				if ($this->_parseError($err, $err_code, $err_message))
				{
					die('SQL error ' . $err_code . ': ' . $err_message);
				}
			}
			$this->_stopTimer($query);
			return $res;
		}
		return 0;
	}

	public function prep($str)
	{
		return preg_replace("#^'(.*)'\$#", '$1', $this->quote($str));
	}

	public function runScript($script)
	{
		global $table_prefix;
		$error = '';

		$script = preg_replace('#^/\*.*?\*/#m', '', $script);
		$script = preg_replace('#^--.*?$#m', '', $script);

		$queries =  preg_split('#;\r?\n#', $script);
		foreach ($queries as $query)
		{
			$query = trim($query);
			if (!empty($query))
			{
				if ($table_prefix != 'ex_')
				{
					$query = str_replace('`ex_', '`'.$table_prefix, $query);
				}
				$result = $this->query($query);
				if (!$result)
				{
					return $this->error . '<br />' . htmlspecialchars($query) . '<hr />';
				}
				elseif ($result instanceof PDOStatement)
				{
					$result->closeCursor();
				}
			}
		}
		return '';
	}

	public function query($query, $parameters = array())
	{
		$this->_startTimer();
		try
		{
			if (count($parameters) > 0)
			{
				if ($this->_prepare_itself)
				{
					$result = parent::query($this->_prepare($query, $parameters));
				}
				else
				{
					$result = parent::prepare($query);
					$this->_bindParams($result, $parameters);
					$result->execute();
				}
			}
			else
			{
				$result = parent::query($query);
			}
		}
		catch (PDOException $err)
		{
			if ($this->_parseError($err, $err_code, $err_message))
			{
				die('SQL error ' . $err_code . ': ' . $err_message);
			}
		}
		$this->_stopTimer($query);

		$result->setFetchMode(PDO::FETCH_ASSOC);
		$this->_affected_rows = $result->rowCount();
		return $result;
	}

	public function update($table_name, $data, $condition ='', $parameters = array(), $update_null = false)
	{
		if(!is_array($data))
		{
			return 0;
		}
		$upd = '';
		if ($this->_prepare_itself && !empty($condition) && count($parameters) > 0)
		{
			$condition = $this->_prepare($condition, $parameters);
			$parameters = array();
		}
		$condition = empty($condition) ? '' : 'WHERE '.$condition;
		foreach ($data as $key => $val)
		{
			if (is_null($val) && !$update_null)
			{
				continue;
			}
			$upd .= "`$key`=";
			if (is_null($val))
			{
				$upd .= 'NULL,';
			}
			elseif ($val === 'NOW()')
			{
				$upd .= 'NOW(),';
			}
			elseif (is_int($val) || is_float($val))
			{
				$upd .= $val.',';
			}
			else
			{
				$upd .= $this->quote($val) . ',';
			}

		}
		if (!empty($upd))
		{
			$upd = mb_substr($upd, 0, -1);
			$query = "UPDATE `$table_name` SET $upd $condition";
			$this->_startTimer();
			try
			{
				if (count($parameters) > 0)
				{
					$stmt = $this->prepare($query);
					$this->_bindParams($stmt, $parameters);
					$stmt->execute();
					$res = $stmt->rowCount();
				}
				else
				{
					$res = $this->exec($query);
				}
			}
			catch (PDOException $err)
			{
				if ($this->_parseError($err, $err_code, $err_message))
				{
					die('SQL error ' . $err_code . ': ' . $err_message);
				}
			}
			$this->_stopTimer($query);
			return $res;
		}
		return 0;
	}
}

?>