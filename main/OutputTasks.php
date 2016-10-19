<?php
require_once('../globals.php');
require_once("$srcdir/formdata.inc.php");
include_once("$srcdir/sql.inc");
include("Tasklist.html");
				
 
                  if (empty($_REQUEST['datevalue'])){
                    //echo "value is empty";
                  }
                  else{
                    

                        $userID = $_SESSION['authId'];
                        $date = $_REQUEST['datevalue'];
                      	
                        
                        $yourArray = array(); // make a new array to hold all your data

                        $resultset = sqlStatement("select Task from Task where userID = '$userID' and Date = '$date'");
                        $row = mysql_fetch_row($result);
                        $index = 0;

                        while($rows = mysql_fetch_row($resultset)){ // loop to store the data in an associative array.
                             $yourArray[$index] = $rows[0];
                             
                             $index++;
                             
                        }

                        
                        echo (json_encode($yourArray));


                  }



?>