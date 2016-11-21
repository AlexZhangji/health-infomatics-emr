<?php
require_once('../globals.php');
require_once("$srcdir/formdata.inc.php");
include_once("$srcdir/sql.inc");

	if (isset($_POST['edit_visit_button'])) {
		$height = $_POST['height'];
		$weight = $_POST['weight'];
		$temp = $_POST['temperature'];
		$pulse = $_POST['pulse'];
		$respiratory_rate = $_POST['respiratory_rate'];
		$bph = $_POST['bph'];
		$bpi = $_POST['bpl'];
		$visit_id = $_POST['visit_id'];
		$patient_id = $_POST['patient_id'];

		$query = "UPDATE patient_visit_gb SET height='$height', weight='$weight', temperature='$temp', pulse='$pulse', respiratory_rate='$respiratory_rate', bph='$bph', bpi='$bpi' WHERE visit_id=$visit_id";
		
		sqlStatement($query);
		
		header("Location: md.php?patientId=".$patient_id."");
	}


	echo "here";

?>