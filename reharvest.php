<?php
function getFile($url, $zip=false,$tmpziphandle=null){
    $ch = curl_init();    
    curl_setopt($ch, CURLOPT_URL, trim($url));
    
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if($zip){
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
        curl_setopt($ch, CURLOPT_FILE, $tmpziphandle);
    }
    $data = curl_exec($ch);
    echo $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);    
    return ($httpcode>=200 && $httpcode<300) ? $data : false;
}

$abcdrootdir='ABCD';
$abcddir='ABCD';
$archivejson=json_decode(file_get_contents($abcdrootdir.'/archives.txt'));
$tempzip='ABCD/tmp.zip';

if(isset($_GET['archive'])){
	$foundinJSON=false;
	#
	if(file_exists($abcdrootdir.'/'.$_GET['archive'])){
		foreach($archivejson as $aid=>$existingarchives){
			if($existingarchives->archive_folder==$_GET['archive']){
				$archivezip=$existingarchives->archive_url;
				$extraxtzipdir=basename($archivezip,'.zip');
				$extractdir=preg_replace('![^A-Za-z]+!','_', $extraxtzipdir);
				echo $extractdir.'<br><hr>';
				if($extractdir==$_GET['archive']){
					echo '<br>Opening ZIP Archive: '.$archivezip.' to be extracted to: '.$extractdir.'<br>'."\r\n";;
					$ziphandle = fopen($tempzip, "w"); if($ziphandle===false) {echo 'Insufficient rights  to create temp ZIP..';exit;}
					$zipfile=getFile($archivezip,true,$ziphandle);
					$zip = new ZipArchive;
					$zipres = $zip->open($tempzip);
					if($zipres===true){
						echo'reharvesting..<br>';
						if(!file_exists($abcddir.'/'.$extractdir)){
							mkdir($abcddir.'/'.$extractdir);
						}elseif(!is_dir($abcddir.'/'.$extractdir)){
							if(filesize($abcddir.'/'.$extractdir)==0){
								unlink($abcddir.'/'.$extractdir);
								mkdir($abcddir.'/'.$extractdir);
							}
						}
						echo 'Extracting ZIP Archive to '.$abcddir.'/'.$extractdir.'<br>'."\r\n";
						//delete old files
						echo 'deleting old files..'.'<br>'."\r\n";
						$deletefiles = glob($abcddir.'/'.$extractdir.'/*'); // get all file names
						print_r($deletefiles);
						foreach($deletefiles as $delfile){ // iterate files
							echo ($delfile);
						  if(is_file($delfile))
							unlink($delfile); // delete file
						}
						//extract and rename
						echo 'extract and rename..'.'<br>'."\r\n";
						for($f=0;$fst=$zip->statIndex($f);$f++){
							#print_r($zip->statIndex($f));
							$filename = $zip->getNameIndex($f);
							$fileinfo = pathinfo($filename);
							echo $newname=str_pad($f+1, 5 ,'0', STR_PAD_LEFT);
							//echo $zip->renameIndex($f,'newresponse.'.$newname.'.xml');
							copy("zip://".$tempzip."#".$filename, $abcddir.'/'.$extractdir.'/response.'.$newname.'.xml');
						}
						touch($abcddir.'/'.$extractdir, $zip->statIndex(0)['mtime']);
						$archivejson[$aid]->harvest_status='success';
						$archivejson[$aid]->harvest_time=date ('c');
						$zip->close();
					}
					
				}
				foreach($existingarchives->xml_archives as $archive){
					if($archive->latest==1){
						echo $archivezip= $archive->xml_archive;
						echo'<br>';
						
						#
											
						#$extractdir=preg_replace('![^A-Za-z]+!','_', $extraxtzipdir);
						#if()
						
						#
						#$zip = new ZipArchive;
					}
				}
				$foundinJSON=true;
				echo 'Marked '.$_GET['archive'].' for reharvesting';
			}
		}
		if(!$foundinJSON){
			echo 'No entry in JSON File..';
		}
		
		echo'<br> Saved modified XML files..';
		print_r($archivejson);
		file_put_contents($abcdrootdir.'/archives.txt',json_encode($archivejson));
	}else echo 'Archive does not exist';
}else echo 'Please enter a ABCD Archive Folder';
?>