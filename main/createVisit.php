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


                    $cc = $_POST['cc_field'];
                    $p_id=$_POST['patient_id'];
                    $symptons = $_POST['symptoms_field'];
                    $diagnosis = $_POST['diagnosis_field'];
                    $rx = $_POST['rx_field'];
                    $note = $_POST['note_area'];

                    $query = "insert into patient_visit_gb (p_id,date,weight,height,temperature,bph,bpi,pulse,respiratory_rate,bos,CC,symptoms,diagnosis,Rx,note) values
                    ('$p_id','$visit_date','$weight','$height','$temp','$bph','$bpl','$pulse','$respiratory_rate','$bos','$cc','$symptons','$diagnosis','$rx','$note')";
                     echo $query;


                    // echo '$task';

                     sqlStatement("insert into patient_visit_gb (p_id,date,weight,height,temperature,bph,bpi,pulse,respiratory_rate,bos,CC,symptoms,diagnosis,Rx,note) values
                    ('$p_id','$visit_date','$weight','$height','$temp','$bph','$bpl','$pulse','$respiratory_rate','$bos','$cc','$symptons','$diagnosis','$rx','$note')");

                 header("Location: md.php?patientId=".$p_id."");

}







?>
