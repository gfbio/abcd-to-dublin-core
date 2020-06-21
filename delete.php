<?php
$abcdrootdir='ABCD';
$archivejson=json_decode(file_get_contents($abcdrootdir.'/archives.txt'));
if(isset($_GET['archive'])){
	$foundinJSON=false;
	if(file_exists($abcdrootdir.'/'.$_GET['archive'])){
		foreach($archivejson as $existingarchives){
			if($existingarchives->archive_folder==$_GET['archive']){
				$existingarchives->harvest_status='delete';
				$foundinJSON=true;
				echo 'Marked '.$_GET['archive'].' as deleted in JSON';
			}
		}
		if(!$foundinJSON){
		$del= new stdClass();
			$del->harvest_status='delete';
			$del->archive_folder=$_GET['archive'];
			array_unshift($archivejson, $del);
			echo 'Added '.$_GET['archive'].' as deleted record to the JSON';
		}
		file_put_contents($abcdrootdir.'/archives.txt',json_encode($archivejson));
		echo'<br> Saved modified JSON..';
		#print_r($archivejson);
	}else echo 'Archive does not exist';
}else echo 'Please enter a ABCD Archive Folder';
echo'<hr>Marked for deletion:<br>';
foreach($archivejson as $existingarchives){
			if($existingarchives->harvest_status=='delete'){
				echo $existingarchives->archive_folder.'<br>';
			}
		}
?>