<html>
    <head>
        <style>
            table {border-collapse: collapse}
            td { border:solid 1px grey}
        </style>
        
    </head>
    <body>
<?php
error_reporting( E_ALL );
function getindexedrecords(){
$json='{
   "size": 0,
    "aggs": {
       "dataCenterStats": {
          "terms": {
             "field": "dataCenterFacet",
             "size": 64
          }
       }
    }
}';

$url='https://ws.pangaea.de/es/dataportal-gfbio/pansimple/_search';
$options = array(
  'http' => array(
    'method'  => 'POST',
    'content' => $json,
    'header'=>  "Content-Type: application/json\r\n" .
                "Accept: application/json\r\n"
    )
);

$context  = stream_context_create( $options );
$result = file_get_contents( $url, false, $context );
$response = json_decode( $result );

$buckets=$response->aggregations->dataCenterStats->buckets;

foreach($buckets as $bucket){    
    $noindexed[$bucket->key]=$bucket->doc_count;
}
return $noindexed;
}

 $touched=false;
if(isset($_GET['dsa'])){
    $dsa=$_GET['dsa'];
}
if(isset($_GET['deldsa'])){
    $deldsa=$_GET['deldsa'];
}
if(isset($_GET['date'])){
    $datestr=$_GET['date'];
    if(strtotime($datestr)!==false){
        $date=strtotime($datestr);
    }else $date=time();
}else $date=time();
$stat=false;
if(isset($_GET['stat'])) $stat=true;

$abcdrootdir='ABCD';
$archivejson=json_decode(file_get_contents($abcdrootdir.'/archives.txt'));
foreach($archivejson as $aid=>$json){
    if($json->dsa==$dsa){
        $archivejson[$aid]->harvest_time=date('c',$date);
        $archivejson[$aid]->new_archive=1;
        $touched=true;
        echo '<br>Touched '.$dsa.' new date for <b>'.$json->dataset.'</b> is: '.date('c',$date);
    }elseif($json->dsa==$deldsa){
        $archivejson[$aid]->harvest_time=date('c',$date);
        $archivejson[$aid]->harvest_status='delete';
    }
}
if($touched===true){   
    $upd=file_put_contents($abcdrootdir.'/archives.txt',json_encode($archivejson));
    if($upd!==false) echo '<br>Update saved..';
    else echo '<br>Update failed..';
} 

$archivesinfo=json_decode(file_get_contents($abcdrootdir.'/archives.txt'),true);
$ai=1;
echo'<table style="border:1px solid grey"><tr><th></th><th>Provider</th><th>DSA</th><th>Title</th><th>Records</th><th>ZIP Date</th><th>Harvest Date</th><th>Harvest Status</th><th>No (files)</th><th></tr></tr>';
foreach($archivesinfo as $aiid=>$archive){
    if($stat===true){#&&$archive['provider_datacenter']=='Data Center DSMZ'){
     $abcddir=$abcdrootdir.'/'.$archive['archive_folder'];
     $files = scandir($abcddir);
      $archivesinfo[$aiid]['nounits']=1;
     foreach($files as $file){
      if(strpos($file,'.xml')){
       #$xml=simplexml_load_file($file);
       $xmlstr=file_get_contents($abcddir.'/'.$file);
       $xml=simplexml_load_string($xmlstr);
       if(sizeof($xml->children('http://www.tdwg.org/schemas/abcd/2.06')->DataSet)>0){
        $dataset=$xml->children('http://www.tdwg.org/schemas/abcd/2.06');
       }
       else{
        $dataset=$xml->children('http://www.tdwg.org/schemas/abcd/2.1');
       }
        $archivesinfo[$aiid]['nounits']+=sizeof($dataset->DataSet->Units->Unit);

       
       if(isset($nounits[$archive['provider_datacenter']]))
        $nounits[$archive['provider_datacenter']]+=sizeof($dataset->DataSet->Units->Unit);
       else
        $nounits[$archive['provider_datacenter']]=sizeof($dataset->DataSet->Units->Unit)+1;
      }
     }
      
    }
            
            
    echo'<tr><td>'.$ai.'</td><td>'.$archive['provider_datacenter'].'</td><td>'.$archive['archive_folder'].'</td><td>'.$archive['dataset'].'</td><td>'.$archive['nounits'].'</td><td>'.substr($archive['zip_time'],0,10).
    '</td><td>'.substr($archive['harvest_time'],0,10).'</td><td>'.$archive['harvest_status'].'</td><td>'.$archive['file_number'].'</td><td><a href="?dsa='.$archive['dsa'].'">touch</a></td></tr>';
    $ai++;
}
echo'</table>';

$noindexed=getindexedrecords();

if($stat===true){
    echo 'saving..';
echo  file_put_contents($abcdrootdir.'/archives.txt',json_encode($archivesinfo));
echo ' bytes..';
 echo'<br><table><tr><th>datacenter</th><th>No (units)</th><th>No (indexed records)</th></tr>';
 foreach($nounits as $arch=>$nounit){
  echo'<tr><td>'.$arch.'</td><td>'.$nounit.'</td><td>';
  foreach($noindexed as $inarch=>$innounit)
   if($inarch==$arch) echo $innounit;
  echo'</td></tr>';
 }
 echo'</table>';
}
?>
</body>