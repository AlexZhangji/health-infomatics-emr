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
  $pwd_alert_date = date('Y-m-d', strtotime($pwd_expires_date.'-7 days'));

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
  $frame1url = 'pwd_expires_alert.php';
} elseif (!empty($_POST['patientID'])) {
    $patientID = 0 + $_POST['patientID'];
    $frame1url = '../patient_file/summary/demographics.php?set_pid='.attr($patientID);
} elseif ($GLOBALS['athletic_team']) {
    $frame1url = '../reports/players_report.php?embed=1';
} elseif (isset($_GET['mode']) && $_GET['mode'] == 'loadcalendar') {
    $frame1url = 'calendar/index.php?pid='.attr($_GET['pid']);
    if (isset($_GET['date'])) {
        $frame1url .= '&date='.attr($_GET['date']);
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
  $frame1url = 'main.php?mode='.attr($_GET['mode']);
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

    <script language='JavaScript'>
      <?php require $GLOBALS['srcdir'].'/restoreSession.php'; ?>

      // This counts the number of frames that have reported themselves as loaded.
      // Currently only left_nav and Title do this, so the maximum will be 2.
      // This is used to determine when those frames are all loaded.
      var loadedFrameCount = 0;

      function allFramesLoaded() {
       // Change this number if more frames participate in reporting.
       return loadedFrameCount >= 2;

      }
    </script>
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
    <script src="js/vendor/jquery-2.1.4.min.js"></script>


    <link rel=stylesheet href="../themes/registration.css" type="text/css">
  </head>

   <body>
   <div  class="main-page" >

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

    <body bgcolor="#f2f2f2">

 <center>

 <div class="md-plain-card" style="margin-top:20px; ">

 <form class = "" method = "POST">
 <div class="form-group form-group-lg is-empty"  id="search_module">
     <label class="control-label md-heading-text" for="patient-search" style='color:#2196F3; font-size:26px;'>Search patient:</label>
     <input class="form-control" type="text"  placeholder="Name" name='namefield' id='namefield'>
     <button class="btn btn-raised active" style='margin-left:0vw; width:10vw;'
     id='comm-search-btn' name="search_button">Search</button>
 </div>
 </form>


<div class="md-heading-text">Patient List</div>

<table class='table table-striped table-hover m-card'>
    <tr>
        <th>Name</th>
        <th>Date of Birth</th>
        <th>Village</th>
    </tr>

<?php


//$name = $_POST['namefield'];
if (isset($_POST['namefield'])) {
    $name = $_POST['namefield'];
} else {
    $name = null;
}

if (empty($name)) {
    $query = 'SELECT * FROM patient_data_gb';
} else {
    $query = 'SELECT * FROM `patient_data_gb` '.
  " WHERE `name` LIKE '%$name%' ;";
}

$comments = mysql_query($query);

// Please remember that  mysql_fetch_array has been deprecated in earlier
// versions of PHP.  As of PHP 7.0, it has been replaced with mysqli_fetch_array.

while ($row = mysql_fetch_array($comments, MYSQL_ASSOC)) {
    $name = $row['name'];
    $dob = $row['DOB'];
    $village = $row['city_village'];

    echo '<tr>';
    echo '<td><a href=md.php?patientId='.$row['id']." >".$row['name'].'</a></td>';
    echo "<td>{$row['DOB']}</td>";
    echo "<td>{$row['city_village']}</td>";
    echo '</tr>';
}

?>
</table>



    <form action="registration.php" method="get">
                      <input type="submit" value="New Patient" class="btn btn-raised active"
                      style='width:20vw;height:auto; background-color: #2196F3; color: white;'
                               name="create_new_patient" id="messages_link" onclick="top.restoreSession()" />
</form>
</div>
</div>

</center>


<script>
    $.material.init();
</script>


    </body>





</html>
