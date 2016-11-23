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
        $_SESSION['expiration_msg'] = 1;
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

$patientId = trim($_GET['patientId']);

$diseasesql = 'SELECT name FROM disease_data_gb';
$diseaseresult = mysql_query($diseasesql);

$disease_list = array();
while ($row = mysql_fetch_array($diseaseresult)) {
    $disease_list[] = $row['name'];
}

?>

<html>
<head>
    <title>
        <?php echo text($openemr_name) ?>
    </title>
    <script type="text/javascript" src="../../library/topdialog.js"></script>

    <script language='JavaScript'>
        <?php require $GLOBALS['srcdir'] . '/restoreSession.php'; ?>

        // This counts the number of frames that have reported themselves as loaded.
        // Currently only left_nav and Title do this, so the maximum will be 2.
        // This is used to determine when those frames are all loaded.
        var loadedFrameCount = 0;

        function allFramesLoaded() {
            // Change this number if more frames participate in reporting.
            return loadedFrameCount >= 2;
            function allFramesLoaded() {
                // Change this number if more frames participate in reporting.
                return loadedFrameCount >= 2;
            }
    </script>
    <link rel=stylesheet href="../themes/registration.css" type="text/css">

    <!-- Customized css -->
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/md.css">

    <!-- Bootstrap -->
    <link rel="stylesheet" type="text/css" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

    <!-- Bootstrap Material Design -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap-material-design.css">
    <link rel="stylesheet" type="text/css" href="css/ripples.min.css">

    <!--  fonts import  -->
    <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:100,300,400,500" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
    <!-- end of fonts import -->
    <script src="js/vendor/jquery-2.1.4.min.js"></script>

    <!-- Material Design fonts -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <link rel=stylesheet href="../themes/main_screen.css" type="text/css">
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    //
    <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
    <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

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

    </br>
    <tr>


        <style>
            ul.tab {
                list-style-type: none;
                margin: 0;
                padding: 0;
                overflow: hidden;
                border: 1px solid #ccc;
                background-color: #A9A9A9;
            }

            /* Float the list items side by side */
            ul.tab li {
                float: left;
            }

            /* Style the links inside the list items */
            ul.tab li a {
                display: inline-block;
                color: black;
                text-align: center;
                padding: 14px 16px;
                text-decoration: none;
                transition: 0.3s;
                font-size: 17px;
            }


        </style>


        <div class="visit-container m-card">

            <form class="new_visit" id="new_visit" action="createVisit.php" method="POST">
                <h2 style="float:left; margin-left:10px;">Create Visit &nbsp;&nbsp;</h2>
                <form action="md.php" method="get">
                    <input type="submit" value="Back" class='btn btn-raised active'
                           style="width:8vw;height:auto;float:right; margin-right:10px;" id='comm-search-btn'
                           name="create_new_patient" id="backbutton" onclick="top.restoreSession()"/>
                </form>
                <br style="clear:both;"/>
                <br/>

                <p><label for="visitDate">Date</label><input type="date" name="visit_date"/>&nbsp;</p>
                <p>&nbsp;</p>
                <p class="left"><label> Weight:&nbsp;</label> <input id="weight_field" class="" name="weight_field"
                                                                     size="10" type="int"
                                                                     value=""/><label>kg</label></p>
                <p>&nbsp;</p>
                <p class="left"><label> Height:&nbsp;</label> <input id="height_field" class="" name="height_field"
                                                                     size="10" type="int"
                                                                     value=""/><label>cm</label></p>
                <p>&nbsp;</p>
                <p class="left"><label>Temperature &nbsp;</label> <input id="temp_field" class="" name="temp_field"
                                                                         size="10" type="int"
                                                                         value=""/><label>°C</label></p>
                <p>&nbsp;</p>
                <p class="left"><label>Blood Pressure </label>
                    <input id="b" class="" name="bph_field" size="10" type="int" value=""/>
                    <label>/</label>
                    <input id="b" class="" name="bpl_field" size="10" type="int" value=""/>
                <p>&nbsp;</p>
                <p class="left">&nbsp;<label>Pulse </label>
                    <input id="pulse_field" class="" name="pulse_field" size="10" type="int"
                           value=""/><label>/min</label>
                <p>&nbsp;</p>
                <label>Respiratory Rate</label>
                <input id="respiratory_rate_field" class="" name="respiratory_rate_field" size="10" type="int"
                       value=""/><label>/min</label></p>
                <p>&nbsp;</p>
                <p class="left"><label>Blood Oxygen Saturation </label> <input id="bos_field" class=""
                                                                               name="bos_field" size="10" type="int"
                                                                               value=""/><label>%</label></p>
                <p class="left">&nbsp;</p>


                <p class="left"><label>Cheif Complaint </label><input id="cc_field" class="" name="cc_field"
                                                                      size="40" type="text" value=""/></p>
                <p>&nbsp;</p>

                <p class="left"><label>Signs and Symptoms </label><input id="symptoms_field" class=""
                                                                         name="symptoms_field" size="40" type="text"
                                                                         value=""/></p>
                <p>&nbsp;</p>

                <p class="left"><label>Diagnosis</label> <input id="diagnosis_field" class="" name="diagnosis_field"
                                                                size="40" type="text" value=""/></p>
                <p>&nbsp;</p>

                <p class="left"><label>Prescription </label><input id="rx_field" class="" name="rx_field" size="40"
                                                                   type="text" value=""/></p>
                <p>&nbsp;</p>

                <label>Notes</label>
                &nbsp;
                <p class="left"><textarea name="note_area" class="" rows="4" cols="40" type="text"
                                          value=""></textarea></p>

                <input name='patient_id' type="hidden" value="<?php echo text($patientId); ?>">
                <input type="submit" value="Submit" name="submit_visit_button" class='btn btn-raised active'
                       style="width:10vw;height:auto; margin-left:20vw;" id='comm-search-btn'>
            </form>
        </div>
</div>


<script>

    $(function () {
        var availableTags =  <?php echo json_encode($disease_list); ?>;
        $("#diagnosis_field").autocomplete({
            source: availableTags

        });
    });

</script>

</body>

<style>
    .visit-container {
        margin-left: 15%;
        width: 70%;
        padding: 10px;

    }

    .ui-menu-item {
        background: white;
        color: #2196F3;
        display: block;
    }

    .ui-autocomplete {
        color: blue;
        width: 200px;
    }

</style>


</body>


</html>
