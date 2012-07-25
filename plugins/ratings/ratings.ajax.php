<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=ajax
[END_PLUGIN]
==================== */

$expire = time() + 99999999;
$domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false; // make cookies work with localhost

if($_POST)
{
	
	$rating = $excursion->import('rating','P','INT');
	$id = $excursion->import('id','P','INT');
	
	if($rating <= 5 && $rating >= 1)
	{
		if(isset($_COOKIE['has_voted_'.$id]))
		{
			echo 'already_voted';
		} 
		else 
		{
			$insert['rating_id'] = $id;
			$insert['rating_num'] = $rating;
			$insert['user'] = $user['id'];

			$db->insert(ratings, $insert);
			
			setcookie('has_voted_'.$id,$id,$expire,'/',$domain,false);
			
			$total = 0;
			$rows = 0;
			
			$sql = $db->query("SELECT rating_num FROM ratings WHERE rating_id = '$id'");
			while ($data = $sql->fetch())
			{
				$total = $total + $data['rating_num'];
				$rows++;
			}
			
			$perc = ($total/$rows) * 20;
			
			echo round($perc,2);
		}
	}
}

?>