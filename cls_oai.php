<?php
class oai{
    var $allowedprefixes=array('oai_dc','pan_dc');
    #$possiblearguments=array('verb','metadataPrefix','resumptionToken');
    var $gatewayfolder='ABCD';
    var $repositoryname=null;
    var $providerinfo=array(); //harvested provider info as delivered by  biocase monitoring service
    var $repositories=array();//sets
    var $modifieddates=array();
    var $error=false;
    var $setspec=false;//is a set specified?

    function __construct($from=null, $until=null){
        $this->getRepositories($from, $until);
        #print_r($this->repositories['name']);exit;        
       # $this->GetModifiedDates();    
    }
    
    function getServerURL() {
      $url = (isset($_SERVER[HTTPS]) && $_SERVER[HTTPS]=='on') ? 'https://' : 'http://';
      return $url.$_SERVER[HTTP_HOST];
    }
    
    function getRequestHeaderXML() {
      $requesturl = $this->getServerURL().parse_url('http://localhost'.$_SERVER[REQUEST_URI], PHP_URL_PATH);
      $xml = '<?xml version="1.0" encoding="UTF-8"?><OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">';
      $xml .= '<responseDate>'.gmdate('Y-m-d\TH:i:s\Z').'</responseDate>';
      $xml .= '<request';
      foreach ($_GET as $key => $value) {
        $xml .= ' '.htmlspecialchars($key).'="'.htmlspecialchars($value).'"';
      }
      $xml .= '>'.htmlspecialchars($requesturl).'</request>';
      return $xml;
    }
    
    function getError($errcode,$errmessage=''){
        echo $this->getRequestHeaderXML().'<error code="'.$errcode.'">'.htmlspecialchars($errmessage).'</error></OAI-PMH>';
        $this->error=true;
    }
    
    function getProviderInfo(){
        $providers=json_decode(file_get_contents($this->gatewayfolder.'/archives.txt'));
        foreach($providers as $provider){
            #echo basename($provider->link);
            #echo  substr(basename($provider->xml_archive),0,-4).'=='.$this->repositoryname."\r\n";exit;
            if($provider->archive_folder==$this->repositoryname){
                $this->providerinfo=$provider;
            }
        }        
    }

    function getRepositories($from=null, $to=null){
        $earliesttimestamp=10000000000000000;
        unset($this->repositories);
        $providerinfo=json_decode(file_get_contents($this->gatewayfolder.'/archives.txt'));
       # $providerinfo=array_merge($providerinfo,json_decode(file_get_contents($this->gatewayfolder.'/deleted.txt')));
        #print_r($providerinfo);exit;
        foreach($providerinfo as $pk=>$provider){
           # echo strtotime($from).' < '.strtotime($provider->harvest_time).'> '.strtotime($to).'<br>'."\r\n";
            $harvest=true;
            if(isset($from)){
                if(strtotime($provider->harvest_time)>=strtotime($from)){
                    $harvest=true;
                }else $harvest=false;
            }
            if(isset($to)){
                if(strtotime($provider->harvest_time)<=strtotime($to)){
                    $harvest=true;
                }else $harvest=false;
            }
            if($provider->harvest_status=='delete'){ $harvest=true;}
            if($harvest===true){
                if(strtotime($provider->harvest_time)<$earliesttimestamp)
                    $this->earliesttimestamp=$provider->harvest_time;
                if(trim($provider->archive_folder)!='')
                    $this->repositories[$pk]=$provider->archive_folder;
                
            }
        } #print_r($this->repositories); exit;
        sort($this->repositories);
       
        
    }
    
    
    function GetArchivesFiles(){
        $archivefiles=array();
        if(sizeof($this->repositories)>0){
        foreach($this->repositories as $rid=>$rep){
            #echo $this->gatewayfolder.'/'.$rep;
            $files = scandir($this->gatewayfolder.'/'.$rep);
            foreach($files as $file){
                if(strpos($file,'.xml')){
                    $archivefiles[]=$this->gatewayfolder.'/'.$rep.'/'.$file;
                }
            }
        }}
        return $archivefiles;
    }
    
    static function getDatasetId($file){
        return $file;
    }
    function GetModifiedDates(){
        $date=date('Y-m-d');
        $dirinfo=$this->fopendir($this->gatewayfolder.'/'.$this->repositoryname);   
        foreach($dirinfo as $file){
            if(file_exists($file)){
                $this->modifieddates[basename($file)]=date('Y-m-d',filemtime($file));
                #$xml=
                #$xml=file_get_contents($file,false,null,0, 2048);
                #if(preg_match('!<abcd:DateModified>(.*?)</abcd:DateModified>!s',$xml,$match)){
                #    $this->modifieddates[basename($file)]=$this->convertdate($match[1]);
                #}
                #else $this->modifieddates[basename($file)]=$date;
            }
        }
    }
    function ListSets(){
        $response=$this->getRequestHeaderXML().'<ListSets>';
     foreach($this->repositories as $sid=>$set){
        if($set['status']!='delete'){
        $response.='<set>
        <setSpec>'.trim($set).'</setSpec>
        <setName>'.trim($set).'</setName>
      </set>';
      }
     }   
     $response.='</ListSets>
    </OAI-PMH>';
    return $response;
    }
    
    function Identify(){    
        $response=$this->getRequestHeaderXML().'<Identify>
        <repositoryName>GFBio ABCD Archives OAI Provider</repositoryName>
        <baseURL>'.$requesturl.'</baseURL>
        <protocolVersion>2.0</protocolVersion>
        <adminEmail>rhuber@uni-bremen.de</adminEmail>
        <earliestDatestamp>'.$this->earliesttimestamp.'</earliestDatestamp>
        <deletedRecord>transient</deletedRecord>
        <granularity>YYYY-MM-DDThh:mm:ssZ</granularity>';
      $response.='</Identify>
    </OAI-PMH>';
    return $response;
    }	
    
    function ListMetadataFormats(){
        $response=$this->getRequestHeaderXML().'<ListMetadataFormats>
            <metadataFormat>
                <metadataPrefix>oai_dc</metadataPrefix>
                <schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</schema>
                <metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/</metadataNamespace>
            </metadataFormat>
            <metadataFormat>
              <metadataPrefix>pan_dc</metadataPrefix>
              <schema>http://ws.pangaea.de/schemas/pansimple/pansimple.xsd</schema>
              <metadataNamespace>http://ws.pangaea.de/schemas/pansimple/</metadataNamespace>
            </metadataFormat>  
         </ListMetadataFormats>
        </OAI-PMH>';
        return $response;
        
    }
    
    function checkResumtionToken($resumptiontoken=null,$from=null, $until=null){

        if(!file_exists($this->gatewayfolder.'/tokens'))
            mkdir($this->gatewayfolder.'/tokens');
        $alltokens=scandir($this->gatewayfolder.'/tokens');
        foreach($alltokens as $oldtoken){
            if ($oldtoken!='.'&&$oldtoken!='..')
                if((time()-filemtime($this->gatewayfolder.'/tokens/'.$oldtoken))>30000)
                   unlink($this->gatewayfolder.'/tokens/'.$oldtoken);
        }
        if($resumptiontoken!=null){
            if(file_exists($this->gatewayfolder.'/tokens/'.$resumptiontoken)){
                $tokeninfostr=file_get_contents($this->gatewayfolder.'/tokens/'.$resumptiontoken);
                #unlink($this->gatewayfolder.'/tokens/'.$resumptiontoken);
                $tokeninfo=json_decode($tokeninfostr,true);
                if(isset($tokeninfo['repositoryname'])){
                    $ret['repositoryname']=$this->repositoryname=$tokeninfo['repositoryname'];
                }
                if(isset($tokeninfo['from']) || isset($tokeninfo['until'])){
                    $ret['from']=$tokeninfo['from'];
                    $ret['until']=$tokeninfo['until'];
                    $this->getRepositories($tokeninfo['from'], $tokeninfo['until']);
                    if(!isset($this->repositoryname))
                        $this->repositoryname=$this->repositories[0];
                }
            }else{
                $this->getError('badResumptionToken','Could not find Token');exit;
            }
        }else{
            if(isset($from))
                $ret['from']=$from;
            if(isset($until))
                $ret['until']=$until;
            $this->getRepositories($from, $until);
            if(!isset($this->repositoryname))
                $this->repositoryname=$this->repositories[0];
        }    
        $nexttoken=uniqid();
        $fileinfo=$this->GetArchivesFiles();
       #print_r($fileinfo);exit;
        sort($fileinfo); 
        if(!isset($resumptiontoken)){                                
            $ret['firstfile']=1;
            //with set spec
            if(isset($this->repositoryname)){
                if(file_exists($this->gatewayfolder.'/'.$this->repositoryname.'/response.00001.xml')){
                    $currentfile=$this->gatewayfolder.'/'.$this->repositoryname.'/response.00001.xml';
                }
                if(file_exists($this->gatewayfolder.'/'.$this->repositoryname.'/response.00002.xml')){
                    $nextfile=$this->gatewayfolder.'/'.$this->repositoryname.'/response.00002.xml';
                    #$nexttoken=md5($this->gatewayfolder.'/'.$this->repositoryname.'/response.00002.xml').$postfix;
                }else  $nextfile=$fileinfo[1];
            }elseif(sizeof($fileinfo)==0){
                $nexttoken=null;
                $currentfile=null;
            }
            else{
                $currentfile=$fileinfo[0];
                $nextfile=$fileinfo[1];
                #$nexttoken=md5($fileinfo[1]).$postfix;
            }
            
        }else{
            $ret['firstfile']=0;
            foreach($fileinfo as $kf=>$fi){
                if(preg_match('/^'.$this->gatewayfolder.'\/(.*?)\//s',$fileinfo[$kf+1],$fma)){
                    $currentarchive=trim($fma[1]);
                }
                if($fi==$tokeninfo['nextfile']){
                    $currentfile=$fi;
                    if(strpos($currentfile,'00001.xml')!==false)
                        $ret['firstfile']=1;
                    #if(isset($fileinfo[$kf+1])){
                    #    if (str_replace(substr(strrchr($fileinfo[$kf+1], '/'), 1),'',$fileinfo[$kf+1])
                     #       ==str_replace(substr(strrchr($fileinfo[$kf], '/'), 1),'',$fileinfo[$kf])){
                        #$nexttoken=md5($fileinfo[$kf+1]).$postfix;
                        $nextfile=$fileinfo[$kf+1];
                    #    }
                   # }
                }
            }

        }
        if(isset($currentfile)){
            
            $this->repositoryname= basename(dirname($currentfile));
            $this->getProviderInfo();
            $ret['file']=$currentfile;
            if(isset($nextfile)){
                $ret['nextfile']=$nextfile;
                $ret['nexttoken']=$nexttoken;
                file_put_contents($this->gatewayfolder.'/tokens/'.$nexttoken, json_encode($ret));
            }
            
               #print_r($ret);exit;
            return $ret;
        }
        else return false;
    }
    
    
    function ListIdentifiers($resumptiontoken=null,$from=null, $to=null){
        $xsltfile='abcd2ids.xslt';
        $n=0;
        if(isset($from)){
            $fromts=strtotime($from);
            if($fromts===false)
                {echo $this->getError('badArgument','Invalid date format');exit;}
        }
        if(isset($to)){
            $tots=strtotime($to);
            if($fromts===false)
                {echo $this->getError('badArgument','Invalid date format');exit;}
        }
        if(isset($tots)&&isset($fromts)){
            if($tots<$fromts)
                {echo $this->getError('badArgument','From date  until date');exit;}
            else{
                
            }
        }
    
        $tokeninfo=$this->checkResumtionToken($resumptiontoken, $from, $to);
        if(!$tokeninfo)echo $this->getError('badResumptionToken');
        else{
            $xmlfile=$tokeninfo['file'];    
            $resumptiontoken=$tokeninfo['nexttoken'];
            
            if(isset($xmlfile)){
                $response= $this->getRequestHeaderXML().'<ListIdentifiers>'; 
            if($xmlfile!='..'&&$xmlfile!='.'&&strpos($xmlfile,'.xml')!==false){libxml_use_internal_errors(true);
                $XML = new DOMDocument();
                
                $xmlstr=file_get_contents('./'.$xmlfile);
                                               //brutal..
                if($xmlstr===false) { echo $this->getError('Internal Error','Problems accessing file : '.$xmlfile);exit;    }                        
                #print($xmlfile);
                if(strpos($xmlstr,'xmlns:abcd21')!==false){
                    $xmlstr=str_replace('abcd21="http://www.tdwg.org/schemas/abcd/2.1','abcd="http://www.tdwg.org/schemas/abcd/2.06',$xmlstr);
                    $xmlstr=str_replace('abcd21','abcd',$xmlstr);
                }

                $XML->loadXML($xmlstr);
                
                $xslt = new XSLTProcessor();
                $xslt->registerPhpFunctions();
                $XSL = new DOMDocument(); 
                $XSL->load( $xsltfile, LIBXML_NOCDATA);
                
                $xslt->importStylesheet( $XSL );                
                $xsltres=$xslt->transformToXML( $XML );
                if($xsltres!==false&&$xsltres!=null){
                    $response.= str_replace('<?xml version="1.0" encoding="UTF-8"?>','',$xsltres);
                }
                else {
                    $libxmlerror='';
                    foreach (libxml_get_errors() as $error) {
                        echo $libxmlerror.= "Libxml error: {$error->message}\n";
                    }
                    echo $this->getError('Internal Error','XSLT Processing : '.$libxmlerror);exit;
                }
                libxml_use_internal_errors(false);
                $n++;
            }else {
                echo $this->getError('Internal Error','file does not exist: '.$xsltfile);exit;
            } 
            if(isset($resumptiontoken))
                $response.= '<resumptionToken>'.$resumptiontoken.'</resumptionToken>';
            else $response.= '<resumptionToken/>';
            echo $response.='</ListIdentifiers>
            </OAI-PMH>';
            #echo $response;
            }else echo $this->getError('Internal Error','XML Missing: '.$xmlfile);exit;
        }
    }
    

    
    function ListRecords($resumptiontoken=null,$from=null, $to=null,$format='oai_dc'){
        $xmloutput='';
        if($format=='oai_dc')
            $xsltfile='abcd2pansimple.xslt';
        elseif($format=='pan_dc')
             $xsltfile='abcd2pansimple.xslt';
         $xsltfile2='abcdmetadata2pansimple.xslt';
         $xsltfiledel='abcd_deleted.xslt';

        if(isset($from)){
            $fromts=strtotime($from);
            if($fromts===false)
                {echo $this->getError('badArgument','Invalid date format');exit;}
        }
        if(isset($to)){
            $tots=strtotime($to);
            if($fromts===false)
                {echo $this->getError('badArgument','Invalid date format');exit;}
        }
        if(isset($tots)&&isset($fromts)){
            if($tots<$fromts)
                {echo $this->getError('badArgument','From date  until date');exit;}
            else{
                
            }
        }
        if($resumptiontoken==null) $firstfile=true;
        else $firstfile=false;
       
        $tokeninfo=$this->checkResumtionToken($resumptiontoken, $from, $to);
        $firstfile=$tokeninfo['firstfile'];
        if($tokeninfo['file']==null)$this->getError('noRecordsMatch');
        elseif(!$tokeninfo)echo $this->getError('badResumptionToken');
        else{
            $xmlfile=$tokeninfo['file'];    
            $resumptiontoken=$tokeninfo['nexttoken'];
            
            if(isset($xmlfile)){
                $xmloutput= $this->getRequestHeaderXML().'<ListRecords>';      
               if($xmlfile!='..'&&$xmlfile!='.'&&strpos($xmlfile,'.xml')!==false){    
                $XML = new DOMDocument();
                
                $xmlstr=file_get_contents($xmlfile);
               if($xmlstr===false) {echo $this->getError('Internal Error','Problems accessing file : '.$xmlfile);exit;   }    
                               //brutal..
                if(strpos($xmlstr,'xmlns:abcd21')!==false){
                    $xmlstr=str_replace('abcd21="http://www.tdwg.org/schemas/abcd/2.1','abcd="http://www.tdwg.org/schemas/abcd/2.06',$xmlstr);
                    $xmlstr=str_replace('abcd21','abcd',$xmlstr);
                }
                
                $xmlparts=explode('Metadata>',$xmlstr);
                $xmlheader=$xmlparts[0].'Metadata>';
                $xmlbody=$xmlparts[1].'Metadata>'.$xmlparts[2];
                //enriching ABCD content with info from e.g biocase monitor service
                #print_r($this->providerinfo);exit;
                
                $xmlheader=preg_replace('/<([a-z]+:)?DataSets\s{1}/','$0xmlns:so="https://ws.gfbio.org/so/" ',$xmlheader);

               $xmlheader=preg_replace('/DataSet>/','DataSet>
                                    <so:ABCDFile>'.htmlspecialchars($this->getServerURL().'/'.basename($_SERVER[SCRIPT_NAME]).'/'.dirname($xmlfile)).'</so:ABCDFile>
                                    <so:ABCDFiletime>'.date('Y-m-d\TH:i:s\Z',filemtime(dirname($xmlfile))).'</so:ABCDFiletime>
                                    <so:ABCDHarvesttime>'.date('Y-m-d\TH:i:s\Z',strtotime($this->providerinfo->harvest_time)).'</so:ABCDHarvesttime>
                                    <so:BMS_ArchiveUrl>'.$this->providerinfo->archive_url.'</so:BMS_ArchiveUrl>
                                    <so:BMS_ArchiveFolder>'.$this->providerinfo->archive_folder.'</so:BMS_ArchiveFolder>
                                    <so:BMS_Datacenter>'.$this->providerinfo->provider_datacenter.'</so:BMS_Datacenter>
                                    <so:BMS_Datacenter_short>'.$this->providerinfo->provider_shortname.'</so:BMS_Datacenter_short>
                                    <so:BMS_Publisher>'.$this->providerinfo->provider_name.'</so:BMS_Publisher>
                                    <so:BMS_dsa>'.$this->providerinfo->dsa.'</so:BMS_dsa>
                                    <so:BMS_ProviderID>'.$this->providerinfo->providerid.'</so:BMS_ProviderID>
                                    <so:BMS_Pywrapper>'.$this->providerinfo->biocase_url.'</so:BMS_Pywrapper>
                                    <so:BMS_Querytool>'.$this->providerinfo->querytool.'</so:BMS_Querytool>',$xmlheader);
                
                
            echo $xmloutput;
                
            $xmlstr=$xmlheader.$xmlbody;
                $XML->loadXML($xmlstr);
                
                if($this->providerinfo->harvest_status=="delete"){
                    $xslt = new XSLTProcessor();
                    
                    $xslt->registerPhpFunctions();
                    $XSL = new DOMDocument();
                     
                    $XSL->load( $xsltfiledel, LIBXML_NOCDATA); 
                    $xslt->importStylesheet( $XSL );
                    echo str_replace('<?xml version="1.0" encoding="UTF-8"?>','',$xslt->transformToXML( $XML ));
                }
                else{
                    if($firstfile==1){
                        $xslt2 = new XSLTProcessor();
                        $xslt2->registerPhpFunctions();
                        $XSL2 = new DOMDocument(); 
                        $XSL2->load( $xsltfile2, LIBXML_NOCDATA); 
                        $xslt2->importStylesheet( $XSL2 );
                       
                        echo str_replace('<?xml version="1.0" encoding="UTF-8"?>','',$xslt2->transformToXML( $XML ));
                    }
                    
                    $xslt = new XSLTProcessor();
                    
                    $xslt->registerPhpFunctions();
                    $XSL = new DOMDocument();
                     
                    $XSL->load( $xsltfile, LIBXML_NOCDATA); 
                    $xslt->importStylesheet( $XSL );
                    $xsltresult=$xslt->transformToXML( $XML );
                    echo str_replace('<?xml version="1.0" encoding="UTF-8"?>','',$xsltresult);
                }
              } 

              if(isset($resumptiontoken)) {
                  echo '<resumptionToken>'.$resumptiontoken.'</resumptionToken>';
              }
              
              echo '</ListRecords></OAI-PMH>';

              }
        }
    }
    
    
    static function convertdate($datestr){
        
        $timestamp=strtotime($datestr);
        if($timestamp!==false){
            return date('Y-m-d\TH:i:s\Z',$timestamp);
        }
        else return '';
    }
    
    static function CamelcaseToWords($string){
       return strtolower(implode(' ', preg_split('/(?=[A-Z])/',$string)));
    }
    
    static function GetLandingPage($providerid,$dsa,$cat){
        //calling biocase monitor
        //http://bms.gfbio.org/services/landingpages/?output=json&provider=2&dsa=BSMeryscoll&filter=&inst=&col=&cat=M-0014086+%2F+14380+%2F+9132
        $bmsinfo=json_decode(file_get_contents('http://bms.gfbio.org/services/landingpages/?output=json&provider='.$providerid.'&dsa='.$dsa.'&cat='.$cat));

       return 'http://bms.gfbio.org/services/landingpages/?output=json&provider='.$providerid.'&dsa='.$dsa.'&cat='.$cat.'  '.$bmsinfo->dataUnit;
    }
    

    
    
    function fopendir($dir, &$fileinfo = array()) {
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if (!is_dir($dir.'/'.$file)) {
                    $fileinfo[] = $dir.'/'.$file;
                } elseif (is_dir($dir.'/'.$file) && $file != '.' && $file != '..') {
                    $this->fopendir($dir.'/'.$file, $fileinfo);
                }
            }
            closedir($handle);
        }
        return $fileinfo;
    }
}//class
?>