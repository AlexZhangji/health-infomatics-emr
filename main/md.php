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

function debug_to_console_2( $data ) {

        $output = "<script>console.log( '" . array_values($data)[1] . "' );</script>";

    echo $output;
}


function debug_to_console( $data ) {

    if ( is_array( $data ) )
        $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
    else
        $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";

    echo $output;
}
// get patinet id
// $patientId = trim($_GET['patientId']);
$patientId = trim($_GET['patientId']);
if($patientId){
  $patientData = sqlQuery("SELECT * " .
    "FROM `patient_data_gb` " .
    "WHERE `id`=?", array(intval($patientId)) );

    debug_to_console_2($patientData);
    debug_to_console($patientData["name"]);
    $patientName = $patientData["name"];
}
// echo "console.log( patientId : " + $patientId + ")";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Customized css -->
    <link rel="stylesheet" type="text/css" href="css/style.css">

    <!-- Highcharts -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-more.js"></script>


    <!-- md js  -->
    <!-- <script src="js/main.js"></script> -->

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
    <link rel=stylesheet href="../themes/main_screen.css" type="text/css">
    <!--    <link rel=stylesheet href="../themes/material-style.css" type="text/css">-->
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
        <li class="left"><a href="#news">News</a></li>
        <li class="left"><a href="#contact">Contact</a></li>
        <li class="left"><a href="#tutorial">Tutorial</a></li>

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
        <div class="patient-basic-info m-card" id='patient-info'>
            <div class="title" style="float:left;">
                <i class="fa fa-user" aria-hidden="true"></i><?php echo text($patientData["name"]); ?>
                <span style="margin-left:10px;color:black;font-family: 'Open Sans', sans-serif;font-size:17px;">Male   10 year(s)</span>

            </div>


            <div class="patient-info-right" style="margin-top: 7px;">
                <span style="margin-right: 20px;">PatientID <span class="md-patient-id">000<?php echo text($patientData["id"]);?>V</span></span>
                <i class="fa fa-arrows-alt" aria-hidden="true" id='expand-patient-info'
                style="" ></i>
            </div>


            <br style="clear:both;" />
            <!-- END OF DEFAULT INFO -->

            <div id="more-patient-info" class='hidden' >
              <div style="min-height:6px;;border-bottom: 5px solid #2196F3; margin-bottom: 5px;">
              </div>

              <div class="basic-stats" >
                  <ul class="list-group" >
                      <li class="list-group-item">
                          <span class="badge"><?php echo text($patientData["gender"]); ?></span> Gender
                      </li>
                      <li class="list-group-item">
                        <span class="badge"><?php echo text($patientData["DOB"]); ?></span> Date of Birth
                      </li>
                      <li class="list-group-item">
                        <span class="badge"><?php echo text($patientData["city_village"]); ?></span> City/Village
                      </li>
                      <li class="list-group-item">
                        <span class="badge"><?php echo text($patientData["phone_num"]); ?></span> Phone Number
                      </li>

                  </ul>
              </div>
            </div>
        </div>

        <div style="width:40%;float:left;">

            <div class="patient-vitals m-card">
                <div class="title" style="border-bottom: 5px solid #2196F3;margin-bottom:5px;">
                    <i class="fa fa-heartbeat" aria-hidden="true"></i> Vitals
                </div>

                <div style="margin-left:3%; font-weight:bold;margin-bottom:4px;">
                    Last Vitals: 20.Mar.2015 12:38 PM
                </div>

                <div class="vitals-stats">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <span class="badge">162cm</span> Height (cm)
                        </li>
                        <li class="list-group-item">
                            <span class="badge">132kg</span> Weight (kg)
                        </li>
                        <li class="list-group-item">
                            <span class="badge">46.5</span> (Calculated) BMI
                        </li>
                        <li class="list-group-item">
                            <span class="badge">30°C</span> Temperature (C)
                        </li>
                        <li class="list-group-item">
                            <span class="badge">218/min</span> Pulse
                        </li>
                        <li class="list-group-item">
                            <span class="badge">80/min</span> Respiratory rate
                        </li>
                        <li class="list-group-item">
                            <span class="badge">108  /  42</span> Blood Pressure
                        </li>
                        <li class="list-group-item">
                            <span class="badge">100%</span> Blood oxygen saturation
                        </li>

                    </ul>
                </div>
            </div>
            <!-- vital card end -->
            <div class="patient-appoinment m-card" style="clear:both;width:100%;">
                <div class="title" style="border-bottom: 5px solid #2196F3;margin-bottom:5px;">
                    <i class="fa fa-calendar" aria-hidden="true"></i> Appointments
                </div>
                <div style="margin:7px; margin-bottom:4px;">
                    None
                </div>
            </div>
        </div>
        <!-- END OF LEFT PANE -->

        <!-- right pane -->
        <div style="width:58.5%; margin-left:1.5%; float:left;">

            <!-- visulization -->
            <div class="patient-visulization m-card" style="float:left;width:100%; ">
                <div class="title">
                    <i class="fa fa-instagram" aria-hidden="true"></i> Visualizations
                </div>

                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist" style="background-color:#2196F3;">
                    <li role="presentation" class="active"><a href="#vital-hex" aria-controls="vital-hex" role="tab" data-toggle="tab">Vitals Plot</a></li>
                    <li role="presentation"><a href="#bmi-scatter" aria-controls="bmi-scatter" role="tab" data-toggle="tab">BMI Chart</a></li>
                    <li role="presentation"><a href="#pressure-hist" aria-controls="pressure-hist" role="tab" data-toggle="tab">Pressure History</a></li>
                    <li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">Option IV</a></li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade in active" id="vital-hex">
                        <div id="spider-web"></div>
                    </div>

                    <div role="tabpanel" class="tab-pane fade" id="bmi-scatter">
                        <div id="scatter-plot"></div>
                    </div>

                    <div role="tabpanel" class="tab-pane fade" id="pressure-hist">
                      <div id="pressure-hist-range"></div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="settings">..2.</div>
                </div>
            </div>
            <!-- end of visulization -->

        </div>
        <!-- right pane -->



    </div>
    <!-- end of container -->
    <img src="./img/cat.jpg" id="cat_img">
</div>
<?php
//
//$x = $_SESSION['authUser'];
//$y = $_SESSION['authId'];
//echo "I love $x and $y";
//?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>

<!-- Material Design for Bootstrap -->
<script src="js/material.js"></script>
<script src="js/ripples.min.js"></script>

<!-- highcharts data-->
<script src="js/chart.js"></script>

<!-- voice control -->
<script src="js/voice.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/annyang/2.6.0/annyang.min.js"></script>
<script>
    if (annyang) {
        // Let's define a command.
        var commands = {
            'hello': function() {
                alert('Hello world!');
            },
            'show me *tag': showPlots,
            'test': function() {
                alert('Working!');
            },
        };

        // Add our commands to annyang
        annyang.addCommands(commands);

        // Start listening.
        annyang.start();
    }
</script>

<script>
  // for toogle patient info
  $(function(){
    $('#expand-patient-info').click(function(){
      $('#more-patient-info').toggleClass('hidden');
    });
  });
</script>

<script>
    $.material.init();
</script>

</body>


</html>
