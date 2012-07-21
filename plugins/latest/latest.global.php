<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=global
[END_PLUGIN]
==================== */

list($user['auth_read'], $user['auth_write'], $user['isadmin']) = $excursion->checkAuth('plugin', 'latest');
if($user['auth_read'])
{

	function latestPages($template, $mode = 'recent', $maxperpage = 5, $d = 0, $titlelength = 0, $cat = '')
	{
		global $db, $config, $lang, $user, $excursion;
		
		$recentitems = new XTemplate('plugins/latest/tpl/'.$template.'.xtpl');
		
		$whitelist = false;
		
		if (!empty($config['plugin']['latest']['blacklist']))
		{
			$blacklist = array();
			foreach (preg_split('#\r?\n#', $config['plugin']['latest']['blacklist']) as $c)
			{
				$blacklist = array_merge($blacklist, $excursion->structure_children('page', $c, true, true));
				
			}
		}
		else
		{
			$blacklist = false;
		}
		
		if ($blacklist)
		{
			$incat = "AND cat NOT IN ('" . implode("','", $blacklist) . "')";
		}

		if ($mode == 'recent')
		{
			$where = "WHERE state=1  " . $incat;
			$totalrecent['pages'] = $config['plugin']['total']['maxpages'];
		}
		
		$join_columns = '';
		$join_tables = '';

		$sql = $db->query("SELECT * FROM pages $where ORDER by date DESC LIMIT $d, $maxperpage");

		$jj = 0;
		
		foreach ($sql->fetchAll() as $pag)
		{
			list($user['auth_read'], $user['auth_write'], $user['isadmin']) = $excursion->checkAuth('page', $pag['cat']);
			if($user['auth_read'])
			{
				$jj++;
				if ((int)$titlelength > 0 && mb_strlen($pag['title']) > $titlelength)
				{
					$pag['title'] = ($excursion->truncate($pag['title'], $titlelength, false)) . "...";
				}
				$recentitems->assign(array(
					'ID' => $pag['id'],
					'TITLE' => $pag['title'],
					'CAT' => $db->query("SELECT title FROM categories WHERE code='".$pag['cat']."' LIMIT 1")->fetchColumn(),
					'DATE' => date($config['date_medium'], $pag['date']),
					'OWNER' => $excursion->generateUser($pag['owner']),
					'PAGE_FILE' => $pag['page_file']
				));

				$recentitems->parse('MAIN.PAGE_ROW');
			}
		}

		if ($d == 0 && $jj == 0)
		{
			$recentitems->parse('MAIN.NO_PAGES_FOUND');
		}

		$recentitems->parse('MAIN');
		return ($d == 0 || $jj == 0) ? $recentitems->text('MAIN') : '';
	}

	$res = latestPages('latest.pages', 'recent', $config['plugin']['latest']['maxpages'], 0, $config['plugin']['latest']['title_length']);
	$plugin['latest_pages'] = $res;

}

?>