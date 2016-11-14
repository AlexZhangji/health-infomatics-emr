<?php
require_once('../globals.php');
require_once("$srcdir/formdata.inc.php");
include_once("$srcdir/sql.inc");
	if (isset($_POST['submit_visit_button'])) {

					$visit_date = $_POST['visit_date'];  
        		    $weight = $_POST['weight_field'];
        			$height = $_POST['height_field'];
                    $temp = $_POST['temp_field'];
                    $bph = $_POST['bph_field'];
                    $bpl = $_POST['bpl_field'];
                    $pulse = $_POST['pulse_field'];
                    $respiratory_rate = $_POST['respiratory_rate_field'];
                    $bos = $_POST['bos_field'];
                    $diagnose = $_POST['diagnose_field'];
                    $note = $_POST['note_area'];
                     
                    $query = "insert into Visits (date,weight,height,temperature,bph,bpl,pulse,respiratory_rate,bos,diagnose,note) values 
                    ('$visit_date','$weight','$height','$temp', '$bph', '$bpl', '$pulse','$respiratory_rate','$bos','$diagnose','$note')";
                     echo $query;
                     
                    
                    // echo '$task';
                     sqlStatement("insert into Visits (date,weight,height,temperature,bph,bpl,pulse,respiratory_rate,bos,diagnose,note) values 
                    ('$visit_date','$weight','$height','$temp', '$bph', '$bpl', '$pulse','$respiratory_rate','$bos','$diagnose','$note')");
                    	
                 header("Location: md.php");
          
}







?>