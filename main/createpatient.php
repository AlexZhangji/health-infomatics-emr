<?php
require_once('../globals.php');
require_once("$srcdir/formdata.inc.php");
include_once("$srcdir/sql.inc");
	if (isset($_POST['submitbutton'])) {

					$name = $_POST['namefield'];  
					echo $name;
        		$gender = $_POST['gender'];
        			 echo $gender;
        			 $address1 = $_POST["address1"];
        			 echo $address1;

					 $address2 = $_POST["address2"];	
					 echo $address2;
                     $date = $_POST["date"];
                     echo $date;

                     $cityvillage = $_POST["cityVillage"];
                     echo $cityvillage;
                     $stateProvince = $_POST["stateProvince"];
                     echo $stateProvince;
                     $selectCountry = $_POST["selectCountry"];
                     echo $selectCountry;
                     $postalCode = $_POST["postalCode"];
                     echo $postalCode;

                     $phoneNumber = $_POST["phoneNumber"];
                     echo $phoneNumber;
                     
                    $query = "insert into PatientData (name,gender,DateofBirth,Address1,Address2,cityVillage,stateProvince,Country,Postal,Phonenumber) values 
                    ('$name','$gender','$date','$address1', '$address2', '$cityvillage', '$stateProvince','$selectCountry','$postalCode','$phoneNumber')";
                     echo $query;
                     
                    
                    // echo '$task';
                     sqlStatement("insert into PatientData (name,gender,DateofBirth,Address1,Address2,cityVillage,stateProvince,Country,Postal,Phonenumber) values 
                    ('$name','$gender','$date','$address1', '$address2', '$cityvillage', '$stateProvince','$selectCountry','$postalCode','$phoneNumber')");
                    	
                 header("Location: searchpatient.php");
          
}







?>