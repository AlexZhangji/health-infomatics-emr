<?php
require_once('../globals.php');
require_once("$srcdir/formdata.inc.php");
include_once("$srcdir/sql.inc");

	
            
           
              
            
        
				$name = $_POST['namefield'];  
				//echo $name;
                    //echo (json_encode($name));

        		$gender = $_POST['gender'];
        			// echo $gender;
        			 $address1 = $_POST["address1"];
        			// echo $address1;

					 $address2 = $_POST["address2"];
					// echo $address2;
                     $date = $_POST["date"];
                    // echo $date;

                     $cityvillage = $_POST["cityVillage"];
                    // echo $cityvillage;
                     $stateProvince = $_POST["stateProvince"];
                    // echo $stateProvince;
                     $selectCountry = $_POST["selectCountry"];
                    // echo $selectCountry;
                     $postalCode = $_POST["postalCode"];
                    // echo $postalCode;

                     $phoneNumber = $_POST["phoneNumber"];
                    $keyval = $_POST["keyval"];
                     //echo $phoneNumber;

                    $query = "insert into patient_data_gb (name,gender,DOB,address_1,address_2,city_village,state_province,country,postal_num,phone_num) values
                    ('$name','$gender','$date','$address1', '$address2', '$cityvillage', '$stateProvince','$selectCountry','$postalCode','$phoneNumber')";

                    
                     
                    

                    // echo '$task';
                     sqlStatement("insert into patient_data_gb (name,gender,DOB,address_1,address_2,city_village,state_province,country,postal_num,phone_num) values
                    ('$name','$gender','$date','$address1','$address2','$cityvillage','$stateProvince','$selectCountry','$postalCode','$phoneNumber')");


echo $keyval;

                 //header("Location: searchpatient.php");










?>
