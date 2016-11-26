<?php
require_once '../globals.php';
require_once "$srcdir/formdata.inc.php";


function getLocalPatientIdList($loc){
  $rawPatientInfo = mysql_query('SELECT gender, DOB, id ' .
      'FROM `patient_data_gb` ' .
      "WHERE `city_village` LIKE '%$loc%' ;");

  $patientIdList = [];
  while($patientInfo = mysql_fetch_array($rawPatientInfo)) {
    array_push($patientIdList,$patientInfo['id']);
  }
  return $patientIdList;
}



function getPatientRawInfo($loc){
  $rawPatientInfo = mysql_query('SELECT gender, DOB, id ' .
      'FROM `patient_data_gb` ' .
      "WHERE `city_village` LIKE '%$loc%' ;");

  $patientIdList = [];
  $patientDOBList = [];
  $patientInfoList = [];

  while($patientInfo = mysql_fetch_array($rawPatientInfo)) {
    array_push($patientIdList,$patientInfo['id']);
    array_push($patientDOBList,$patientInfo['DOB']);

    array_push($patientInfoList, $patientInfo);
  }


  return $patientDOBList;
  // foreach($patientIdList as $_id){
  //
  // }
}

 ?>
