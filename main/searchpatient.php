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
 * @package OpenEMR
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

$fake_register_globals=false;
$sanitize_all_escapes=true;

/* Include our required headers */
require_once('../globals.php');
require_once("$srcdir/formdata.inc.php");

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
}
else {
  // This is not a new login, so create a new session id and do NOT remove the old session
  session_regenerate_id(false);
}

$_SESSION["encounter"] = '';

// Fetch the password expiration date
$is_expired=false;
if($GLOBALS['password_expiration_days'] != 0){
  $is_expired=false;
  $q= (isset($_POST['authUser'])) ? $_POST['authUser'] : '';
  $result = sqlStatement("select pwd_expiration_date from users where username = ?", array($q));
  $current_date = date('Y-m-d');
  $pwd_expires_date = $current_date;
  if($row = sqlFetchArray($result)) {
    $pwd_expires_date = $row['pwd_expiration_date'];
  }

  // Display the password expiration message (starting from 7 days before the password gets expired)
  $pwd_alert_date = date('Y-m-d', strtotime($pwd_expires_date . '-7 days'));

  if (strtotime($pwd_alert_date) != '' &&
      strtotime($current_date) >= strtotime($pwd_alert_date) &&
      (!isset($_SESSION['expiration_msg'])
      or $_SESSION['expiration_msg'] == 0)) {
    $is_expired = true;
    $_SESSION['expiration_msg'] = 1; // only show the expired message once
  }
}

if ($is_expired) {
  //display the php file containing the password expiration message.
  $frame1url = "pwd_expires_alert.php";
}
else if (!empty($_POST['patientID'])) {
  $patientID = 0 + $_POST['patientID'];
  $frame1url = "../patient_file/summary/demographics.php?set_pid=".attr($patientID);
}
else if ($GLOBALS['athletic_team']) {
  $frame1url = "../reports/players_report.php?embed=1";
}
else if (isset($_GET['mode']) && $_GET['mode'] == "loadcalendar") {
  $frame1url = "calendar/index.php?pid=" . attr($_GET['pid']);
  if (isset($_GET['date'])) $frame1url .= "&date=" . attr($_GET['date']);
}
else if ($GLOBALS['concurrent_layout']) {
  // new layout
  if ($GLOBALS['default_top_pane']) {
    $frame1url=attr($GLOBALS['default_top_pane']);
  } else {
    $frame1url = "main_info.php";
  }
}
else {
  // old layout
  $frame1url = "main.php?mode=" . attr($_GET['mode']);
}

$nav_area_width = $GLOBALS['athletic_team'] ? '230' : '130';
if (!empty($GLOBALS['gbl_nav_area_width'])) $nav_area_width = $GLOBALS['gbl_nav_area_width'];
?>
<html>
  <head>
    <title>
    <?php echo text($openemr_name) ?>
    </title>
    <script type="text/javascript" src="../../library/topdialog.js"></script>
    <script src="js/vendor/jquery-2.1.4.min.js"></script>

    <script language='JavaScript'>
      <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

      // This counts the number of frames that have reported themselves as loaded.
      // Currently only left_nav and Title do this, so the maximum will be 2.
      // This is used to determine when those frames are all loaded.
      var loadedFrameCount = 0;

      function allFramesLoaded() {
       // Change this number if more frames participate in reporting.
       return loadedFrameCount >= 2;

      }
    </script>
    <link rel=stylesheet href="../themes/registration.css" type="text/css">
  </head>

   <body  >
      <h id = "heading">

        <p id = "heading">
          <a href="main_screen.php">
            <img class="LoginLogo" src="/openemr-4.2.0/sites/default/images/Global_Brigades_Logo.png" alt="Login Image" style="width:200px;height:100px;">
          </a>
        </p>

        </h>
        <ul>
      <li class = "left"><a href="main_screen.php">Home</a></li>
      <li class = "left"><a href="#news">News</a></li>
      <li class = "left"><a href="#contact">Contact</a></li>
      <li class = "left"><a href="#tutorial">Tutorial</a></li>

      <li class = "right">
       <a href="../logout.php" target="_top" class="css_button_small"  id="logout_link" onclick="top.restoreSession()" >
      <span><?php echo htmlspecialchars( xl('Logout'), ENT_QUOTES) ?></span></a></td>
      </li>
      <li class = "right">User</li>
      <li class = "right"><a href="#settings">Settings</a></li>


    </ul>
    </br>
        <style>
        input[type=text], select {
    border: 1px solid #ccc;
                padding: 7px 0px;
                border-radius: 3px;
                padding-left:5px;
}
input[type=submit] {
    width: 20%;
    background-color: #4CAF50;
    color: white;
    padding: 14px 20px;
    margin: 8px 0;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

div.container {
    width: 60%;
    border: 1px solid gray;
    background-color:#FFFFFF;
}

div.header {
    width: 100%;
    border: 1px solid gray;
    background-color:#FFFFFF;
    background-color: #20B2AA;
}

</style>


    </head>
    <body bgcolor="#f2f2f2">







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
ul.tab li {float: left;}

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
table, th, td {
    border: 1px solid black;
}
</style>


 <center>

 <div class="container">

 <div class="panel panel-default" id="search_module">
 <p> <input type="text" placeholder="Firstname">
<input type="text" placeholder="Lastname">
<button name="search_button">Search</button>



</p>
</div>





<div class="panel panel-default" id="patient_list" >
    <div class="panel-heading">Patient List</div>
    <div class="panel-body" style="overflow:scroll; font-size: large; padding: 1px">
        <table class="patient_table" id="patient_table" style="padding: 3px; font-size: large">
            <tr>
                <th>Firstname</th>
                <th>Lastname</th>
                <th>Date of Birth</th>
                <th>Village</th>
            </tr>


        </table>
    </div>
    <form action="registration.php" method="get">
                      <input type="submit" value="NewPatient"
                               name="create_new_patient" id="messages_link" onclick="top.restoreSession()" />
</form>
</div>

    <button onclick="postDataToMD();"> Medical record page </button>
<p></p>
<p></p>
</center>





<script>
// test ajax functions
function postDataToMD(){
  console.log('post to md excuted');
  // $.ajax({
  //   type: 'POST',
  //   url: 'md.php',
  //   data: { patientId: '777' },
  //   success: function(response) {
  //       content.html(response);
  //   }
  // });
  window.location.href = 'md.php?patientId=' + '1';
}
</script>



    </body>





</html>
