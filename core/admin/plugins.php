<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
$adm['location'] = 'plugins';

$xtpl = new XTemplate('themes/admin/plugins.xtpl');

$plugin = $excursion->import('plugin', 'G', 'ALP', 24);
$dir = 'plugins';

switch($a)
{
	/* =============== */
	case 'details':
	/* =============== */
	
		$ext_config = $dir . '/' . $plugin . '/' . $plugin . '.config.php';
		$ext_options = $dir . '/' . $plugin . '/' . $plugin . '.options.php';
		$plugin_path = "plugins/$plugin";
		$ignore_parts = array('options', 'install', 'config', 'uninstall');
		$options_exists = file_exists($ext_options);
		$config_exists = file_exists($ext_config);
		
		$plug['options'] = (($options_exists) ? true : false);
		
		if($options_exists)
		{
		
			$sql = $db->query("SELECT * FROM config  WHERE part = '$plugin'");
			
			while ($row = $sql->fetch())
			{

				if ($row['type'] == $config['type_string'])
				{
				
					$field = $excursion->inputbox('text', $row['title'], $row['value']);
					
				}
				elseif ($row['type'] == $config['type_select'])
				{
				
					if (!empty($row['variants']))
					{
					
						$cfg_params = explode(',', $row['variants']);
						$cfg_params_titles = (isset($L['cfg_'.$config_name.'_params'])
							&& is_array($L['cfg_'.$config_name.'_params']))
								? $L['cfg_'.$config_name.'_params'] : $cfg_params;
								
					}
					
					$field = (is_array($cfg_params))
						? $excursion->selectbox($row['value'], $row['title'], $cfg_params, $cfg_params_titles, false)
						: $excursion->inputbox('text', $row['title'], $row['value']);
						
				}
				elseif ($row['type'] == $config['type_radio'])
				{
				
					$field = $excursion->radiobox($row['value'], $row['title'], array(1, 0), array('Yes', 'No'), '', ' ');
					
				}
				elseif ($row['type'] == $config['type_text'])
				{
				
					$field = $excursion->textarea($row['title'], $row['value'], 8, 56);
					
				}
				elseif ($config_type == $config['type_range'])
				{
				
					$range_params = preg_split('#\s*,\s*#', $row['variants']);
					$cfg_params = count($range_params) == 3 ? range($range_params[0], $range_params[1], $range_params[2])
						: range($range_params[0], $range_params[1]);
					$field = $excursion->selectbox($row['value'], $row['title'], $cfg_params, $cfg_params, false);
					
				}
			
				$xtpl->assign(array(
					'TITLE' => $row['text'],
					'FORM_OPTION' => $field
				));
				
				$xtpl->parse('MAIN.DETAILS.OPTIONS_ROW');
				
			}
			
			$xtpl->assign('FORM_OPTIONS_ACTION', $excursion->url('admin', 'm=plugins&a=details&plugin='.$plugin.'&action=save'));
			
		}
		
		if($action == 'save')
		{
		
			$sql = $db->query("SELECT * FROM config  WHERE part = '$plugin'");
			
			while ($row = $sql->fetch())
			{
			
				$option_value = $excursion->import($row['title'], 'P', 'NOC');
				$db->update(config, array('value' => $option_value), 
					"title = '".$row['title']."' AND part = '".$plugin."'");
			
			}
			
			header('Location: admin.php?m=plugins&a=details&plugin='.$plugin);
		
		}
		if($action == 'install')
		{
		
			if (file_exists("plugins/".$plugin."/setup/install.sql"))
			{
			
				$db->runScript(file_get_contents("plugins/".$plugin."/setup/install.sql"));
				
			}
		
			if($options_exists)
			{
			
				$info_cfg = $excursion->infoget($ext_options, 'PLUGIN_OPTIONS');
				$options = $excursion->parseConfig($info_cfg, true);
				
				foreach ($options as $x => $option)
				{
					
					$db->insert('config', 
						array(
							'part' => $plugin, 
							'title' => $option['name'],
							'order' => $option['order'], 
							'type' => $option['type'],
							'value' => $option['default'], 
							'default' => $option['default'],
							'variants' => $option['variants'],
							'text' => $option['text']
						)
					);
					
				}
				
			}
			
			$dp = opendir($plugin_path);
			while ($f = readdir($dp))
			{
			
				if (preg_match("#^$plugin(\.([\w\.]+))?.php$#", $f, $mt) && !in_array($mt[2], $ignore_parts))
				{
				
					$part_info = $excursion->infoget($plugin_path . "/$f", 'PLUGIN');
					
					if ($part_info)
					{
					
						if (empty($part_info['Hooks']))
						{
						
							$hooks = 'standalone';
							
						}
						else
						{
						
							$hooks = explode(',', $part_info['Hooks']);
							$hooks = is_array($hooks) ? array_map('trim', $hooks) : array();
							
						}
						
						$i = 0;
						foreach ($hooks as $hook)
						{
							$hook_bindings[] = array(
								'part' => empty($mt[2]) ? 'main' : $mt[2],
								'file' => $f,
								'hook' => $hook,
								'order' => isset($order[$i]) ? (int) $order[$i] : $order
							);
							++$i;
						}
					
					}
			
				}
			
			}
			
			closedir($dp);
			
			$insert_rows = array();
			foreach ($hook_bindings as $binding)
			{
			
				$insert_rows[] = array(
					'hook' => $binding['hook'],
					'code' => $plugin,
					'owner' => 'plug',
					'part' => $binding['part'],
					'file' => empty($binding['file']) ? "$plugin/$plugin.{$binding['part']}.php" : $plugin . '/' . $binding['file'],
					'active' => 1
				);
				
			}
			$db->insert('plugins', $insert_rows);
			
			header('Location: admin.php?m=plugins&a=details&plugin='.$plugin);
		
		}
		if($action == 'uninstall')
		{
		
			if($options_exists)
			{
			
				$sql = $db->delete('config', "part='$plugin'");
			
			}
			
			if (file_exists("plugins/".$plugin."/setup/uninstall.sql"))
			{
			
				$db->runScript(file_get_contents("plugins/".$plugin."/setup/uninstall.sql"));
				
			}
			
			$sql = $db->delete('plugins', "code='$plugin'");
			
			header('Location: admin.php?m=plugins&a=details&plugin='.$plugin);
		
		}
		
		if ($config_exists)
		{
		
			$info = $excursion->infoget($ext_config, 'PLUGIN_CONFIG');
		
		}
		else
		{
		
			header('Location: admin.php?m=plugins');
		
		}
		
		$status_sql = $db->query("SELECT COUNT(*) FROM plugins WHERE code='$plugin' AND owner='plug' AND active='1'")->fetchColumn();
		$status = (($status_sql > 0) ? 'active' : 'inactive');
		$icofile = 'plugins/' . $plugin . '/img/icon-' . $plugin . '.png';
		
		if($status=='active')
		{
		
			$xtpl->assign('UNINSTALL_URL', $excursion->url('admin', "m=plugins&a=details&plugin=$plugin&action=uninstall"));
			$plug['status'] = true;
			
		}
		else
		{
			
			$xtpl->assign('INSTALL_URL', $excursion->url('admin', "m=plugins&a=details&plugin=$plugin&action=install"));
			$plug['status'] = false;
			
		}

		$xtpl->assign(array(
			'NAME' => $info['Name'],
			'CODE' => $plugin,
			'DESC' => empty($L['info_desc']) ? $info['Description'] : $L['info_desc'],
			'ICON' => (file_exists($icofile)) ? $icofile : 'assets/images/icon-plugin_default.png',
			'STATUS' => $status,
			'VERSION' => $info['Version']
		));
		
		$xtpl->parse('MAIN.DETAILS');
		
	break;
	
	/* =============== */
	default:
	/* =============== */
	
		$extensions = $excursion->compile_plugin_info($dir);
		
		foreach ($extensions as $code => $info)
		{
					
			if (empty($info['Error']))
			{
		
				$status_sql = $db->query("SELECT COUNT(*) FROM plugins WHERE code='$code' AND owner='plug' AND active='1'")->fetchColumn();
				$status = (($status_sql > 0) ? 'active' : 'inactive');
				$icofile = 'plugins/' . $code . '/img/icon-' . $code . '.png';

				$xtpl->assign(array(
					'DETAILS_URL' => $excursion->url('admin', "m=plugins&a=details&plugin=$code"),
					'NAME' => $info['Name'],
					'CODE' => $code,
					'DESC' => empty($L['info_desc']) ? $info['Description'] : $L['info_desc'],
					'ICON' => (file_exists($icofile)) ? $icofile : 'assets/images/icon-plugin_default.png',
					'CONFIG_URL' => $excursion->url('admin', "m=config"),
					'STATUS' => $status,
					'VERSION' => $info['Version']
				));
				$xtpl->parse('MAIN.DEFAULT.ROW');
				
			}
						
		}
	
		$xtpl->parse('MAIN.DEFAULT');
		
	break;
}

$xtpl->parse('MAIN');
$xtpl->out('MAIN');
 
?>