<?php
require_once('../globals.php');
require_once("$srcdir/formdata.inc.php");
include_once("$srcdir/sql.inc");
	$sql = "select name from disease_data_gb";
    $result = mysql_query($sql);

    $disease_list = array();
    while($row = mysqli_fetch_array($result))
    {
        $disease_list[] = $row['name'];
    }
    echo json_encode($disease_list);

}