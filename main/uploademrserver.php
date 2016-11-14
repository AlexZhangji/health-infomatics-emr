<?php
require_once('../globals.php');
require_once("$srcdir/formdata.inc.php");
include_once("$srcdir/sql.inc");
//require_once('wordparcer.php');
echo "hell0";
if(isset($_POST['submit']))
{
    
    //CHECK: File by POST

    if(isset($_POST['file']))
    {
         //echo $_POST['file'] . '<br />';
        
    }

    //CHECK: File if 'isset' $_FILES        
    if(isset($_FILES['file']))
    {
        $name = $_FILES['file']['tmp_name'];
        //echo $name;
         if (file_exists($name)){
         	//echo "exists";
         }else{
         	//echo "does not exist";
         }

         //echo "hellp";
         $archiveFile = $name;
         $dataFile = "word/document.xml";
         $zip = new ZipArchive;

// Open received archive file
if (true == $zip->open($archiveFile)) {

    // If done, search for the data file in the archive
    if (($index = $zip->locateName($dataFile)) !== false) {
    	//echo "hello1";
        // If found, read it to the string
        $data = $zip->getFromIndex($index);
        // Close archive file
        
        $zip->close();
        // Load XML from a string
        // Skip errors and warnings
        $xml = new DOMDocument();
    $xml->loadXML($data);
        // Return data without XML formatting tags
    
        echo strip_tags($xml->saveXML());

        $pieces = explode(" ", $xml->saveXML());
        foreach($pieces as $v){
		    echo $v;
		    echo "-----";
		}
    //echo $xml;
    }

    $zip->close();
}

        
    



	}
    else
    {
       // echo '$_FILES not set!';
    }
 }
//cache data and then 
 //google allows to run as a native web application

                 
    /* if (empty($_REQUEST['filevalue'])){
                    echo "value is empty";
                  }
                  else{


                        $file = $_SESSION['filevalue'];
                       
                    	echo "hello";
                        //$resultset = sqlStatement("select Task from Task where userID = '$userID' and Date = '$date'");
                        //$row = mysql_fetch_row($result);
                       /* $index = 0;

                        while($rows = mysql_fetch_row($resultset)){ // loop to store the data in an associative array.
                             $yourArray[$index] = $rows[0];
                             
                             $index++;
                             
                        }

                        
                


                  }*/

?>