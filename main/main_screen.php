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
?>
<html>
<head>
    <title>
        <?php echo text($openemr_name) ?>
    </title>
    <script type="text/javascript" src="../../library/topdialog.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>

    <!--  fonts import  -->
    <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:100,300,400,500" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
    <!-- end of fonts import -->

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



         var db = openDatabase('openemr', '1.0', 'Open EMR Client DB', 2 * 1024 * 1024);
         var msg;

         db.transaction(function (tx) {
            var numrows;
            tx.executeSql('CREATE TABLE IF NOT EXISTS patient_data_gb (id BIGINT, name VARCHAR(255), gender VARCHAR(255), DOB date, city_village VARCHAR(255), state_province VARCHAR(255), address_1 VARCHAR(255), address_2 VARCHAR(255), country VARCHAR(255), postal_num BIGINT, phone_num BIGINT);');
             $.ajax({
                        type: "POST",
                        url: "patientdatatablerow.php",
                        dataType: "JSON",

                        success:function(json){
                                    db.transaction(function (tx){
                                        var arrayLength = json.length;
                                        for (var i = 0; i < arrayLength; i++) {
                                            tx.executeSql('INSERT INTO patient_data_gb (id, name, gender, DOB, city_village, state_province, address_1, address_2, country, postal_num, phone_num) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [json[i][0], json[i][1],json[i][2],json[i][3],json[i][4],json[i][5],json[i][7],json[i][8],json[i][6],json[i][9],json[i][10]]);
                                              //Do something
                                             // alert("passed insert");
                                        }
                                        // alert(json[1][1]);
                                        db.transaction(function (tx){


                                        tx.executeSql('SELECT * FROM patient_data_gb', [], function (tx, results) {
                                          var len = results;
                                          // alert(len);

                                        });



                                    });

                                    });






                        },
                        error: function() {
                          // alert("Not able to sync data");


                        }




            });
        });












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
    </br>

    <div class="container">

        <div class="card-shadow main-option">

            <i class="fa fa-check-square-o" aria-hidden="true"></i><br>
            <form action="Tasklist.php" method="get">

                <input type="submit" value="To Do List"
                       name="Messages" id="messages_link" onclick="top.restoreSession()"/>
            </form>
        </div>

        <div class="card-shadow main-option">
            <i class="fa fa-heartbeat" aria-hidden="true"></i><br>
            <form action="searchpatient.php" method="get">
                <input type="submit" value="Patient Records" id="Patients"/>
            </form>
        </div>
        <div class="card-shadow main-option">
            <i class="fa fa-comments-o" aria-hidden="true"></i><br>
            <form action="#" method="get">
                <input type="submit" value="Messages"
                       name="Messages" id="messages_link" onclick="top.restoreSession()"/>
            </form>
        </div>
        <div class="card-shadow main-option">
            <i class="fa fa-book" aria-hidden="true"></i><br>

            <form action="#" method="get">
                <input type="submit" value="Dictionary" id="dictionary">

            </form>
        </div>
        <div class="card-shadow main-option">
            <i class="fa fa-bar-chart" aria-hidden="true"></i><br>

            <form action="community_data.php" method="get">
                <input type="submit" value="Community Data" id="Community Data">
            </form>
        </div>

        <div class="card-shadow main-option">
            <i class="fa fa-cogs" aria-hidden="true"></i><br>

            <form action="#" method="get">
                <input type="submit" value="System Setting" id="system">
            </form>
        </div>


        <br style="clear:both;">
    </div>
</div>
<?php
//
//$x = $_SESSION['authUser'];
//$y = $_SESSION['authId'];
//echo "I love $x and $y";
//?>
</body>


</html>
