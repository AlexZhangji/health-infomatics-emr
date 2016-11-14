<?php
require_once('../globals.php');
require_once("$srcdir/formdata.inc.php");
include_once("$srcdir/sql.inc");
<<<<<<< HEAD
	//if (isset($_POST['submitbutton'])) {
            
           
              
              if ($_POST['namefield']){
                echo $_POST['namefield'];
              }else{
                echo "not full";
              }
        
				$name = $_POST['namefield'];  
				echo $name;
                    //echo (json_encode($name));
=======
>>>>>>> 78271b2572649dbe4e53c1f9e098615827d3496c
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

                    $query = "insert into patient_data_gb (name,gender,DOB,address_1,address_2,city_village,state_province,country,postal_num,phone_num) values
                    ('$name','$gender','$date','$address1', '$address2', '$cityvillage', '$stateProvince','$selectCountry','$postalCode','$phoneNumber')";
<<<<<<< HEAD
                    // echo $query;
                     
                    
=======


>>>>>>> 78271b2572649dbe4e53c1f9e098615827d3496c
                    // echo '$task';
                     sqlStatement("insert into patient_data_gb (name,gender,DOB,address_1,address_2,city_village,state_province,country,postal_num,phone_num) values
                    ('$name','$gender','$date','$address1', '$address2', '$cityvillage', '$stateProvince','$selectCountry','$postalCode','$phoneNumber')");
<<<<<<< HEAD

=======

                 header("Location: searchpatient.php");

}
>>>>>>> 78271b2572649dbe4e53c1f9e098615827d3496c







?>
