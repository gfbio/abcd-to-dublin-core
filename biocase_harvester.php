<?php
set_time_limit(0);

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

function delete_files($target) {
    $deleted=array();
    if(is_dir($target)){
        $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
        
        foreach( $files as $file )
        {
            delete_files( $file );      
        }
      
        rmdir( $target );
    } elseif(is_file($target)) {
        $deleted[]=$target;
        unlink( $target );  
    }
    return $deleted;
}
$tempzip='ABCD/tmp.zip';
$abcddir='ABCD';
if($_GET['delete']==1){
    delete_files($abcddir);
    echo 'All files deleted';
    exit;
}
echo'<h1>Biocase ABCD Archive Harvester</h1>'."\r\n";
echo'Starting... <br>'."\r\n";
$oldarchives=array();
$oldarchivejson=json_decode(file_get_contents($abcddir.'/archives.txt'));
$last_harvest_time=null;
foreach($oldarchivejson as $oldjson){
    foreach($oldjson->xml_archives as $oldarch){
        if ($oldarch->latest===true)
            $oldarchives[$oldarch->xml_archive]=$oldjson;
    }
    if($last_harvest_time<=strtotime($oldjson->harvest_time))
        $last_harvest_time=strtotime($oldjson->harvest_time);
   
}
 echo date('c',$last_harvest_time);
$archivejson=json_decode(file_get_contents('http://bms.gfbio.org/services/xml-archives/'));
$providerjson=json_decode(file_get_contents('http://bms.gfbio.org/services/providers/'));

#print_r($providerjson);
#########deleteing old files##############
#$deletedarchives=delete_files($abcddir);


#########harvesting new files#############
foreach($archivejson as $aid=>$archive){
    if(1==1){
    foreach($providerjson as $provider){       
        if($provider->url==$archive->provider_url){
            foreach($archive->xml_archives as $xmlar){                
                if($xmlar->latest==1||sizeof($archive->xml_archives)==1){
                    $xml_archive=$archivejson[$aid]->archive_url=$xmlar->xml_archive;
                    $xml_archive_id=$xmlar->id;
                    if(!array_key_exists($xmlar->xml_archive,$oldarchives)){
                        $archivejson[$aid]->new_archive=1;
                    }else{
                        $archivejson[$aid]->new_archive=0;
                    }
                    $archivejson[$aid]->providerid=$provider->id;
                    $archivejson[$aid]->provider_name=$provider->name;
                    $archivejson[$aid]->provider_shortname=$provider->shortname;
                    if(isset($provider->biocase_url))
                        $archivejson[$aid]->biocase_url=$provider->biocase_url;
                    if(isset($provider->pywrapper))
                        $archivejson[$aid]->pywrapper=$provider->pywrapper;
                }
            }
        }
    }
    #if(in_array($archivejson[$aid]->providerid, array(1,2,6))){
    $archivezip=$xml_archive;
    #$archivezip=$xml_archive_id;
    $ziphandle = fopen($tempzip, "w"); if($ziphandle===false) {echo 'Insufficient rights  to create temp ZIP..';exit;}
    $extraxtzipdir=basename($archivezip,'.zip');

    $extractdir=preg_replace('![^A-Za-z]+!','_', $extraxtzipdir);
    echo '<br>Opening ZIP Archive: '.$archivezip.' to be extracted to: '.$extractdir.'<br>'."\r\n";;
    $zipfile=getFile($archivezip,true,$ziphandle);
    $zip = new ZipArchive;
    $zipres = $zip->open($tempzip);
    if(isset($oldarchives[$xml_archive]->harvest_time)){
        $oldtimestamp=strtotime($oldarchives[$xml_archive]->harvest_time);
    }
    else
        $oldtimestamp=0;
            
    if($zipres !==TRUE||$zipfile===false){
        //$zfilename = $mtime = $zip->statIndex(0)['name'];
        echo "Error :- Unable to open the Zip File: ".$zipres."\r\n";
        $archivejson[$aid]->new_archive=0;
        $archivejson[$aid]->harvest_time=date('c',$oldtimestamp);
        $archivejson[$aid]->harvest_status='failed';
        
    } else {
       
        if(!file_exists($abcddir.'/'.$extractdir)){
            mkdir($abcddir.'/'.$extractdir);
        } 
        echo 'Extracting ZIP Archive to '.$abcddir.'/'.$extractdir.'<br>'."\r\n";
        
        $archivejson[$aid]->zip_time=date ('c',$zip->statIndex(0)['mtime']);
        $archivejson[$aid]->harvest_status='success';
        if($zip->statIndex(0)['mtime'] > $oldtimestamp){
            rmdir($abcddir.'/'.$extractdir);  
            $archivejson[$aid]->new_archive=1;
            $archivejson[$aid]->harvest_time=date('c');

        }else{
            $archivejson[$aid]->new_archive=0;
            $archivejson[$aid]->harvest_time=date('c',$oldtimestamp);
        }
        $archivejson[$aid]->archive_folder=$extractdir;
        $archivejson[$aid]->file_number=$zip->numFiles;
        //delete old files
        echo 'deleting old files..'.'<br>'."\r\n";
        $deletefiles = glob($abcddir.'/'.$extractdir.'/*'); // get all file names
        foreach($deletefiles as $delfile){ // iterate files
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

       // $extractstatus=$zip->extractTo($abcddir.'/'.$extractdir);
        touch($abcddir.'/'.$extractdir, $zip->statIndex(0)['mtime']);
        $zip->close();
        echo '<hr>';

    }
/* Extract Zip File */
    
   
    fclose($ziphandle);
    unlink($tempzip);
}
    #}else unset($archivejson[$aid]);
}
file_put_contents($abcddir.'/archives.txt',json_encode($archivejson));
chmod($abcddir.'/archives.txt', 0777);
echo 'Finished'."\r\n";
?>