<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

set_time_limit(0);
date_default_timezone_set('UTC');
header('Content-type: application/xml');
include_once('cls_oai.php');
/*$loghandle=fopen('log.txt','a');
fwrite($loghandle,date('c').' '.$_SERVER[REQUEST_URI]."\r\n");
fclose($loghandle);*/
$possibleverbs=array('Identify','ListRecords','ListMetadataFormats','ListSets','ListIdentifiers');
$possiblearguments=array('verb','metadataPrefix','identifier','from','until','set','resumptionToken','setSpec');
$setspec=false;
if(isset($_REQUEST['set'])){
    $repositoryname=rawurlencode($_REQUEST['set']);   
    $setspec=true;
}
if(isset($_REQUEST['from']))
    $from=$_REQUEST['from'];
if(isset($_REQUEST['until']))
    $until=$_REQUEST['until'];
    
if(isset($_REQUEST['resumptionToken']))
    $resumptiontoken=$_REQUEST['resumptionToken'];
    
$oai=new oai($from,$until);
$oai->setspec=$setspec;

#print_r($oai->repositories);
foreach ($_REQUEST as $argname => $argval){
    if(!in_array($argname,$possiblearguments)){
        $oai->getError('badArgument','Illegal argument passed');
        exit;
    }
}

if(isset($_REQUEST['verb'])){  
    $verb=$_REQUEST['verb'];
    #$updates=GetModifiedDates($gatewayfolder.'/'.$repositoryname);
    if(in_array($verb, $possibleverbs)){
        if(isset($repositoryname)){
            $validset=false;            
            foreach($oai->repositories as $set){
                if($repositoryname==$set) $validset=true;
            }
            if($validset===false){               
                $oai->getError('badArgument','Unknown Set: '.$repositoryname);
            }
            else {                
                $oai->repositoryname=$repositoryname;
                unset($oai->repositories);
                $oai->repositories[0]=$repositoryname;
            }
            
        }else {
            $oai->repositoryname=$oai->repositories[0];
        }
        
        $oai->getProviderInfo();
        #echo $oai->repositoryname;
        if(!$oai->error){
            if($verb=='Identify') {echo $oai->Identify();}
            elseif($verb=='ListSets') {echo $oai->ListSets();}
            elseif($verb=='ListRecords') {
                if(!isset($_REQUEST['metadataPrefix'])&&!isset($resumptiontoken))
                    $oai->getError('badArgument','metadataPrefix argument missing');                    
                elseif(!in_array($_REQUEST['metadataPrefix'],$oai->allowedprefixes)&&!isset($resumptiontoken))
                    $oai->getError('cannotDisseminateFormat');
                else {
                    $oai->ListRecords($resumptiontoken,$from,$until);
                }
            }
            elseif($verb=='ListIdentifiers') {
                $oai->ListIdentifiers($resumptiontoken,$from,$until);
            }
            elseif($verb=='ListMetadataFormats') {echo $oai->ListMetadataFormats();}
            else {$oai->getError('badVerb','Function not implemented');}
        }
    }else $oai->getError('badVerb');
}else $oai->getError('badVerb','verb argument required');