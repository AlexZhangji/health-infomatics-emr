 
<?php
	require_once('../globals.php');
require_once("$srcdir/formdata.inc.php");
include_once("$srcdir/sql.inc");
  



                   
  

					$y = $_SESSION['authId'];  
					//echo $y;
        			 $taskarea = $_POST["taskarea"];	
                     $date = $_POST["datenewtask"];
                     $keyval = $_POST["keyval"];
                     //echo $taskarea;
                    // echo $date;
                     $query = "insert into Task (userID,Date,Task) values ('$y','$date','$taskarea')";
                     
                    
                    // echo '$task';
                     sqlStatement("insert into Task (userID,Date,Task) values ('$y','$date','$taskarea')");
                    	
                  //header("Location: Tasklist.php");
          
echo $keyval;
                     
           ?>
            