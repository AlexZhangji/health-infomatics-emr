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


$patientId = trim($_GET['patientId']);
if ($patientId){
  echo $patientId;
}

$diseasesql = "SELECT name FROM disease_data_gb";
    $diseaseresult = mysql_query($diseasesql);

    $disease_list = array();
    while($row = mysql_fetch_array($diseaseresult))
    {
        $disease_list[] = $row['name'];
    }


?>




<html>
  <head>

  <!-- load jquery ui css-->
<link href="path/to/jquery-ui.min.css" rel="stylesheet" type="text/css" />
<!-- load jquery library -->
<script src="path/to/jquery-1.10.2.js"></script>
<!-- load jquery ui js file -->
<script src="path/to/jquery-ui.min.js"></script>


    <title>
    <?php echo text($openemr_name) ?>
    </title>
    <script type="text/javascript" src="../../library/topdialog.js"></script>

    <script language='JavaScript'>
      <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

      // This counts the number of frames that have reported themselves as loaded.
      // Currently only left_nav and Title do this, so the maximum will be 2.
      // This is used to determine when those frames are all loaded.
      var loadedFrameCount = 0;

    $("#diagnosis_field").autocomplete({
        source: $disease_list,
        autoFocus:true
    });


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


  

<tr>




<br>
<br>




<center>


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


</style>


   

        <div class="container">
        <form action="md.php" method="get">
                      <input type="submit" value="Back" 
                               name="create_new_patient" id="backbutton" onclick="top.restoreSession()"  />
        </form>
        </br>
        </br>
        <form class = "new_visit" id = "new_visit" action = "createVisit.php" method = "POST">
        <h2>Create Visit &nbsp;&nbsp;</h2>
        <p><label for="visitDate">Date</label><input type="date" name="visit_date"/>&nbsp;</p>
        <p>&nbsp;</p>
        <p class="left"><label> Weight:&nbsp;</label> <input id="weight_field" class="" name="weight_field" size="10" type="int" value="" /><label>kg</label></p>
        <p>&nbsp;</p>
         <p class="left"><label> Height:&nbsp;</label> <input id="height_field" class="" name="height_field" size="10" type="int" value="" /><label>cm</label></p>
        <p>&nbsp;</p>
        <p class="left"><label>Temperature &nbsp;</label> <input id="temp_field" class="" name="temp_field" size="10" type="int" value="" /><label>°C</label></p>
        <p>&nbsp;</p>
        <p class="left"><label>Blood Pressure </label> 
        <input id="b" class="" name="bph_field" size="10" type="int" value="" />
        <label>/</label>
        <input id="b" class="" name="bpl_field" size="10" type="int" value="" />
        <p>&nbsp;</p>
        <p class="left">&nbsp;<label>Pulse </label>
        <input id="pulse_field" class="" name="pulse_field" size="10" type="int" value="" /><label>/min</label>
        <p>&nbsp;</p>
        <label>Respiratory Rate</label> 
        <input id="respiratory_rate_field" class="" name="respiratory_rate_field" size="10" type="int" value="" /><label>/min</label></p>
        <p>&nbsp;</p>
        <p class="left"><label>Blood Oxygen Saturation </label> <input id="bos_field" class="" name="bos_field" size="10" type="int" value="" /><label>%</label></p>
        <p class="left">&nbsp;</p>


        <p class="left"><label>Cheif Complaint </label><input id="cc_field" class="" name="cc_field" size="40" type="text" value="" /> </p>
        <p>&nbsp;</p>

        <p class="left"><label>Signs and Symptoms </label><input id="symptoms_field" class="" name="symptoms_field" size="40" type="text" value="" /> </p>
        <p>&nbsp;</p>

        <p class="left"> <label>Diagnosis</label> <input id="diagnosis_field" class="" name="diagnosis_field" size="40" type="text" value="" /> </p>
        <p>&nbsp;</p>

        <p class="left"><label>Prescription </label><input id="rx_field" class="" name="rx_field" size="40" type="text" value="" /> </p>
        <p>&nbsp;</p>

        <label>Notes</label>
        &nbsp;
        <p class="left"> <textarea name="note_area" class="" rows="4" cols="40" type="text" value="" ></textarea> </p>

        <input name='patient_id' type="hidden" value="<?php echo text($patientId);?>" >
        <input type="submit" value="Submit" name = "submit_visit_button">
        </form>
        </div>
   

  
    </body>

  

  

</html>