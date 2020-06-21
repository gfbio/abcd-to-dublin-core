<html>
    <head>
        <meta content="text/html; charset=UTF-8" http-equiv="content-type" />
        <style>
            body{font-family: Arial, Helvetica; font-size: 12pt}
            table{border-collapse: collapse;}
            th {border: 1px solid black; font-style: bold}
            td {font-size: 10pt; border: 1px solid grey}
        </style>
    </head>
<body>
<h1>GFBio ABCD check</h1>
<table><tr><th>Archive</th><th>ABCD Version</th><th>Dataset title</th><th>File creation</th><th>Metadata modified (abcd:DateModified)</th></th><th>Data Center (abcd:TechnicalContact)</th><th>Publisher (abcd:Owner)</th></tr>
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
$abcddir='ABCD';
$dirlisting = scandir($abcddir);
#print_r($dirlisting);
foreach ($dirlisting as $dir){
    $abcd=null;
    if(is_dir($abcddir.'/'.$dir)&&$dir!='..'&&$dir!='.'){
        $abcdversion='2.06';
        echo '<tr>';
        $abcdfile=$abcddir.'/'.$dir.'/'.'response.00001.xml';
        if(file_exists($abcdfile)){
            $xml=file_get_contents($abcdfile);
            $biocasexml=simplexml_load_string($xml);
            
            $abcd=$biocasexml->children('http://www.tdwg.org/schemas/abcd/2.06');
            if(sizeof($abcd)==0) {

                $abcd=$biocasexml->children('http://www.tdwg.org/schemas/abcd/2.1');
                $abcdversion='2.1';
            }
             echo '<td>'.$dir.'</td>';
             echo '<td>'.$abcdversion.'</td>';
            echo '<td>'.$abcd->DataSet->Metadata->Description->Representation->Title.'</td>';
             echo '<td>'.date ("d.m.Y", filemtime($abcdfile)).'</td>';
             echo '<td>'.$abcd->DataSet->Metadata->RevisionData->DateModified.'</td>';
            echo '<td>'.$abcd->DataSet->TechnicalContacts->TechnicalContact->Name.'</td>';
            #DataSets/DataSet/Metadata/Owners/Owner/Organisation/Name/Representation/Text
            echo '<td>'.$abcd->DataSet->Metadata->Owners->Owner->Organisation->Name->Representation->Text.'</td>';
        }
        echo '<tr>';
    }
}
?>
</body>
</table>
</html>