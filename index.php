<?php
//PHP script to create a static OAI repository
set_time_limit(0);
header('Content-type: application/xml');
function convertdate($datestr){
    $timestamp=strtotime($datestr);
    if($timestamp!==false)
        return date('Y-m-d',$timestamp);
    else return false;
}

function fopendir($dir, &$fileinfo = array()) {
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            if (!is_dir($dir.'/'.$file)) {
                $fileinfo[] = array($dir.'/'.$file, filesize($dir.'/'.$file));
            } elseif (is_dir($dir.'/'.$file) && $file != '.' && $file != '..') {
                fopendir($dir.'/'.$file, $fileinfo);
            }
        }
        closedir($handle);
    }
    return $fileinfo;
}    
// directory where abcd files are located

$xsltfile='abcd2pansimple.xslt';
if(isset($_REQUEST['directory']))
    $xmldirectory=$_REQUEST['directory'];
else
    $xmldirectory='ABCD';    
$earliestdate='';
$n=0;

$fileinfo=fopendir($xmldirectory);

if(is_array($fileinfo)){
    echo '<?xml version="1.0" encoding="UTF-8"?>
<Repository xmlns="http://www.openarchives.org/OAI/2.0/static-repository" 
            xmlns:oai="http://www.openarchives.org/OAI/2.0/" 
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
            xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/static-repository 
                                http://www.openarchives.org/OAI/2.0/static-repository.xsd">
  <Identify>
    <oai:repositoryName>'.$xmldirectory.'</oai:repositoryName>
    <oai:baseURL>http://</oai:baseURL>
    <oai:protocolVersion>2.0</oai:protocolVersion>
    <oai:adminEmail>rhuber@uni-bremen.de</oai:adminEmail>
    <oai:earliestDatestamp>'.$earliestdate.'</oai:earliestDatestamp>
    <oai:deletedRecord>no</oai:deletedRecord>
    <oai:granularity>YYYY-MM-DD</oai:granularity>
  </Identify>
  <ListMetadataFormats>
    <oai:metadataFormat>
      <oai:metadataPrefix>pan_dc</oai:metadataPrefix>
      <oai:schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</oai:schema>
      <oai:metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/</oai:metadataNamespace>
    </oai:metadataFormat>    
  </ListMetadataFormats>
  <ListRecords metadataPrefix="pan_dc">';
  
    foreach($fileinfo as $file){
        $xmlfile=$file[0];
        if($xmlfile!='..'&&$xmlfile!='.'&&strpos($xmlfile,'.xml')!==false){    
            $XML = new DOMDocument(); 
            $XML->load($xmlfile);
            if($n==0){
                $xpath = new DOMXpath($XML);
                $elements = $xpath->query("//abcd:DateModified");
                if (!is_null($elements)) {
                    foreach ($elements as $element)
                        $earliestdate=convertdate($element->nodeValue); 
                }
            }    
            $xslt = new XSLTProcessor();
            $xslt->registerPhpFunctions();
            $XSL = new DOMDocument(); 
            $XSL->load( $xsltfile, LIBXML_NOCDATA); 
            $xslt->importStylesheet( $XSL ); 
            print str_replace('<?xml version="1.0" encoding="UTF-8"?>','',$xslt->transformToXML( $XML ));
            $n++;
        } 
    }

echo'</ListRecords>
</Repository>';
}
?>
