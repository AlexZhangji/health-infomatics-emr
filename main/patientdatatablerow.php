<?php
require_once('../globals.php');
require_once("$srcdir/formdata.inc.php");
include_once("$srcdir/sql.inc");

$resultset = sqlStatement("select * FROM patient_data_gb");
 
 						$index = 0;
 						$yourArray = array(); 
                        while($rows = mysql_fetch_row($resultset)){ // loop to store the data in an associative array.
                             $yourArray[$index][0] = $rows[0];
                             $yourArray[$index][1] = $rows[1];
                             $yourArray[$index][2] = $rows[2];
                             $yourArray[$index][3] = $rows[3];
                             $yourArray[$index][4] = $rows[4];
                             $yourArray[$index][5] = $rows[5];
                             $yourArray[$index][6] = $rows[6];
                             $yourArray[$index][7] = $rows[7];
                             $yourArray[$index][8] = $rows[8];
                             $yourArray[$index][9] = $rows[9];
                             $yourArray[$index][10] = $rows[10];
                             
                             $index++;
                             
                        }

                        
                        echo (json_encode($yourArray));








?>