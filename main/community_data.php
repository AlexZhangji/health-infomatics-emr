<?php
/**
 * The outside frame that holds all of the OpenEMR User Interface.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @author  Brady Miller <brady@sparmy.com>
 *
 * @link    http://www.open-emr.org
 */
$fake_register_globals = false;
$sanitize_all_escapes = true;

/* Include our required headers */
require_once '../globals.php';
require_once 'community_data_helper.php';
require_once "$srcdir/formdata.inc.php";

// Creates a new session id when load this outer frame
// (allows creations of separate OpenEMR frames to view patients concurrently
//  on different browser frame/windows)
// This session id is used below in the restoreSession.php include to create a
// session cookie for this specific OpenEMR instance that is then maintained
// within the OpenEMR instance by calling top.restoreSession() whenever
// refreshing or starting a new script.
if (isset($_POST['new_login_session_management'])) {
    // This is a new login, so create a new session id and remove the old session
    session_regenerate_id(true);
} else {
    // This is not a new login, so create a new session id and do NOT remove the old session
    session_regenerate_id(false);
}

$_SESSION['encounter'] = '';

// Fetch the password expiration date
$is_expired = false;
if ($GLOBALS['password_expiration_days'] != 0) {
    $is_expired = false;
    $q = (isset($_POST['authUser'])) ? $_POST['authUser'] : '';
    $result = sqlStatement('select pwd_expiration_date from users where username = ?', array($q));
    $current_date = date('Y-m-d');
    $pwd_expires_date = $current_date;
    if ($row = sqlFetchArray($result)) {
        $pwd_expires_date = $row['pwd_expiration_date'];
    }

    // Display the password expiration message (starting from 7 days before the password gets expired)
    $pwd_alert_date = date('Y-m-d', strtotime($pwd_expires_date . '-7 days'));

    if (strtotime($pwd_alert_date) != '' &&
        strtotime($current_date) >= strtotime($pwd_alert_date) &&
        (!isset($_SESSION['expiration_msg'])
            or $_SESSION['expiration_msg'] == 0)
    ) {
        $is_expired = true;
        $_SESSION['expiration_msg'] = 1; // only show the expired message once
    }
}

if ($is_expired) {
    //display the php file containing the password expiration message.
    $frame1url = 'pwd_expires_alert.php';
} elseif (!empty($_POST['patientID'])) {
    $patientID = 0 + $_POST['patientID'];
    $frame1url = '../patient_file/summary/demographics.php?set_pid=' . attr($patientID);
} elseif ($GLOBALS['athletic_team']) {
    $frame1url = '../reports/players_report.php?embed=1';
} elseif (isset($_GET['mode']) && $_GET['mode'] == 'loadcalendar') {
    $frame1url = 'calendar/index.php?pid=' . attr($_GET['pid']);
    if (isset($_GET['date'])) {
        $frame1url .= '&date=' . attr($_GET['date']);
    }
} elseif ($GLOBALS['concurrent_layout']) {
    // new layout
    if ($GLOBALS['default_top_pane']) {
        $frame1url = attr($GLOBALS['default_top_pane']);
    } else {
        $frame1url = 'main_info.php';
    }
} else {
    // old layout
    $frame1url = 'main.php?mode=' . attr($_GET['mode']);
}

$nav_area_width = $GLOBALS['athletic_team'] ? '230' : '130';
if (!empty($GLOBALS['gbl_nav_area_width'])) {
    $nav_area_width = $GLOBALS['gbl_nav_area_width'];
}

$rawVillageInfo = mysql_query(
    'SELECT city_village , COUNT(city_village) AS num_patient ' .
    'FROM patient_data_gb ' .
    'GROUP BY city_village ' .
    'ORDER BY COUNT(city_village) DESC ;');

//  handle search results
if (!empty($_GET['location'])) {
    $searchLoc = trim($_GET['location']);
    // print_r($searchLoc . ' is set!');
} else {
    $searchLoc = null;
}

if (empty($searchLoc)) {
    $resQuery = $rawVillageInfo;

} else {

    $resQuery = mysql_query('SELECT city_village , COUNT(city_village) AS num_patient ' .
        'FROM `patient_data_gb` ' .
        "WHERE `city_village` LIKE '%$searchLoc%' " .
        'GROUP BY city_village ' .
        'ORDER BY COUNT(city_village) DESC ;');

}

// init visibility as hidden
$chartVisibility = 'hidden';

?>

<html>
<head>
    <title>
        <?php echo text($openemr_name) ?>
    </title>
    <script type="text/javascript" src="../../library/topdialog.js"></script>

    <!--  fonts import  -->
    <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:100,300,400,500" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
    <!-- end of fonts import -->
    <script src="js/vendor/jquery-2.1.4.min.js"></script>

    <!-- Material Design fonts -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <!-- Bootstrap -->
    <link rel="stylesheet" type="text/css" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

    <!-- Bootstrap Material Design -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap-material-design.css">
    <link rel="stylesheet" type="text/css" href="css/ripples.min.css">
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <!-- Customized css -->
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/md.css">

    <!-- Highcharts -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-more.js"></script>


    <!-- md js  -->
    <!-- <script src="js/main.js"></script> -->
    <link rel=stylesheet href="../themes/main_screen.css" type="text/css">
    <!--    <link rel=stylesheet href="../themes/material-style.css" type="text/css">-->

    <script language='JavaScript'>

        <?php require $GLOBALS['srcdir'] . '/restoreSession.php'; ?>
        // This counts the number of frames that have reported themselves as loaded.
        // Currently only left_nav and Title do this, so the maximum will be 2.
        // This is used to determine when those frames are all loaded.
        var loadedFrameCount = 0;

        function allFramesLoaded() {
            // Change this number if more frames participate in reporting.
            return loadedFrameCount >= 2;

        }
    </script>

</head>

<body>

<div class="main-page">
    <h id="heading">

        <p id="heading">
            <a href="main_screen.php">
                <img class="LoginLogo" src="../pic/sites/images/Global_Brigades_Logo_H.png" alt="Login Image"
                     style="height:100px;">
            </a>
        </p>

    </h>
    <ul class="header card-shadow">
        <li class="left"><a href="main_screen.php">Home</a></li>
        <li class="left"><a href="Tasklist.php">To Dos</a></li>
        <li class="left"><a href="#Dictionary">Dictionary</a></li>
        <li class="left"><a href="community_data.php">Communities</a></li>

        <li class="right">
            <a href="../logout.php" target="_top" class="css_button_small" id="logout_link"
               onclick="top.restoreSession()">
                <span><?php echo htmlspecialchars(xl('Logout'), ENT_QUOTES) ?></span></a></td>
        </li>
        <li class="right">User</li>
        <li class="right"><a href="#settings">Settings</a></li>
    </ul>
    <!-- </br> -->
    <!-- END OF HEADER -->

    <!-- container  -->
    <div class="md-plain-card" style="margin-top:20px; ">

        <div class="form-group form-group-lg is-empty ">
            <label class="control-label" for="village-search" style='color:#2196F3; font-size:20px;'>Input the
                Community:</label>
            <input class="form-control" type="text" id="village-search">
            <a class="btn btn-raised active " style='margin-left:30vw; width:20vw;' id='comm-search-btn'
               onclick='searchComm();'>
                Search
            </a>
        </div>

        <br/>

        <table class='table table-striped table-hover m-card'>
            <tr>
                <th>Village Name</th>
                <th>Number of Patient</th>
            </tr>

            <?php

            $resCount = 0;
            while ($villageInfo = mysql_fetch_array($resQuery)) {
                $village = $villageInfo['city_village'];
                $numPatient = $villageInfo['num_patient'];
                $resCount++;

                echo "<tr>";
                echo "<td><a href=community_data.php?location=" . urlencode($village) . " style='color: #0B0080 '>" . $village . "</a></td>";
                echo "<td>$numPatient</td>";
                echo "</tr>";
            }

            // find exactly one location
            if ($resCount == 1) {
                $chartVisibility = 'block';
                $patientRawInfo = getPatientRawInfo($searchLoc);
            } else {
                $chartVisibility = 'none';
                $patientRawInfo = '';
            }

            ?>
        </table>
        <!--  end of table  -->

        <!--   show chart if selected certain location  -->
        <div class="" id="chart-panel" style="display: <?php echo $chartVisibility ?> ;
            width:100%; margin-top: 20px;" >

            <div class="m-card" id="column-plot" style="float:left; width:49%;"></div>
            <div class="m-card" id="pie-plot" style="float: left; width:49%;margin-left: 2%; " ></div>

            <br style="clear:both;">

            <div class="m-card" id="scatter-plot" style="width: 100%;"></div>

            <div class="m-card" id="pressure-hist-range" style="width: 100%;"></div>


        </div>

    </div>
    <!-- end of container -->


</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>

<!-- Material Design for Bootstrap -->
<script src="js/material.js"></script>
<script src="js/ripples.min.js"></script>

<!-- highcharts data-->
<script src="chart.js"></script>



<script>
    $(function(){
      var patientDOBList =<?php echo json_encode($patientRawInfo); ?>;
      if(patientDOBList != ''){
        var ageGroupList = parseDOB(patientDOBList);
        initPieChart(ageGroupList);
      }

      initScatterPlot();
      initPressureHist();
      initColChart();
    });


    function searchComm() {
        var _village = document.querySelector('#village-search').value;
        window.location.href = "community_data.php?location=" + encodeURIComponent(_village);
    }


    // init bootstrap-material
    $.material.init();
</script>

</body>


</html>
