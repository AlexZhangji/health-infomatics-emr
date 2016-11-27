<?php
require_once('../globals.php');
require_once("$srcdir/formdata.inc.php");
include_once("$srcdir/sql.inc");

if (isset($_POST['namefield'])){
  $name = $_POST['namefield'];
}
else{
  $name=null;
}

if (empty($name)){
  $query = "SELECT * FROM patient_data_gb";
}else{

  $query = "SELECT * FROM `patient_data_gb` " .
  " WHERE `name` LIKE '%$name%' ;";
}

$comments = mysql_query($query);

// Please remember that  mysql_fetch_array has been deprecated in earlier
// versions of PHP.  As of PHP 7.0, it has been replaced with mysqli_fetch_array.
$index = 0;
$yourArray = array(); 
while($row = mysql_fetch_array($comments, MYSQL_ASSOC))
{
  $yourArray[$index][0] = $row['id'];
  $yourArray[$index][1] = $row['name'];
  $yourArray[$index][2] = $row['DOB'];
  $yourArray[$index][3] = $row['city_village'];
  $index ++;


  
}
   echo (json_encode($yourArray));

?>