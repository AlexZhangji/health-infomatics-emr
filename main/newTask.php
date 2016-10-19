 
<?php
	require_once('../globals.php');
require_once("$srcdir/formdata.inc.php");
include_once("$srcdir/sql.inc");
  



                   
  if (isset($_POST['submitbutton'])) {

					$y = $_SESSION['authId'];  
					echo $y;
        			 $taskarea = $_POST["taskarea"];	
                     $date = $_POST["datenewtask"];
                     echo $taskarea;
                     echo $date;
                     $query = "insert into Task (userID,Date,Task) values ('$y','$date','$taskarea')";
                     echo $query;
                     
                    
                    // echo '$task';
                     sqlStatement("insert into Task (userID,Date,Task) values ('$y','$date','$taskarea')");
                    	
                  header("Location: Tasklist.php");
          
}
           ?>
            