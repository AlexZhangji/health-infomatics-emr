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
<html manifest="app.appcache">
  <head>
    <title>
    <?php echo text($openemr_name) ?>
    </title>
    <script type="text/javascript" src="../../library/topdialog.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
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
                                        
                                        db.transaction(function (tx){

                                    
                                          tx.executeSql('SELECT * FROM patient_data_gb', [], function (tx, results) {
                                            var len = results;
                                           
                                      
                                          });


                                
                                      }); 
                              
                                    });
                                   
                                  
                          

                               

                        },
                        error: function() {
                          alert("Not able to sync data");
                            

                        }

                 
             
           
            });
        });

          var names = $("#namefield").val();

         
        $.ajax({
                type: "POST",
                url: "patientsearchdb.php",
                dataType: "JSON",
                data: {'namefield': names
                                         },
                success:function(json){

                  var arrayLength = json.length;
               
                                      for (var i = 0; i < arrayLength; i++) {
                                        $("#patienttable").append("<tr><td><a href=md.php?patientId=" + json[i][0] +  "style='color: #0B0080 '>'" + json[i][1] + "</a></td><td>" + json[i][2] + "</td><td>"+ json[i][3]+ "</td></tr>");
                                          

 
                                          //Do something
                                      }
                                      
                },
                error: function() {
                  // Save
                  alert("failed");
                   db.transaction(function (tx){

                                    
                                          tx.executeSql('SELECT * FROM patient_data_gb', [], function (tx, results) {
                                           $.each(results.rows, function(rowIndex){
                                              var row = results.rows.item(rowIndex);
                                              $("#patienttable").append("<tr><td><a href=md.php?patientId=" + row.id +  "style='color: #0B0080 '>'" + row.name + "</a></td><td>" + row.DOB + "</td><td>"+ row.city_village+ "</td></tr>");
                                            });
                                           
                                      
                                          });


                                
                                      }); 
                  
                  //alert(key);
                  //alert(JSON.parse(localStorage.getItem(key)));

                }

        });

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



 <div class="panel panel-default" id="search_module">

 <p> <input type="text" placeholder="Name" name='namefield' id='namefield'>
<button name="search_button" id = "search_button">Search</button>
</p>
</div>

 <script>
    $(document).ready(function(){
      $("#search_button").click(function(e){
          
         var names = $("#namefield").val();

         
        $.ajax({
                type: "POST",
                url: "patientsearchdb.php",
                dataType: "JSON",
                data: {'namefield': names
                                         },
                success:function(json){

                  var arrayLength = json.length;
                 $("#patienttable").empty();
                                      for (var i = 0; i < arrayLength; i++) {
                                        $("#patienttable").append("<tr><td><a href=md.php?patientId=" + json[i][0] +  "style='color: #0B0080 '>'" + json[i][1] + "</a></td><td>" + json[i][2] + "</td><td>"+ json[i][3]+ "</td></tr>");
                                          

 
                                          //Do something
                                      }
                                      
                },
                error: function() {
                  // Save
                  alert("failed");
                  
                  //alert(key);
                  //alert(JSON.parse(localStorage.getItem(key)));

                }

        });

      });
    });
    </script>




<div class="md-heading-text">Patient List</div>


<table class='table table-striped table-hover m-card'>

    <tr>
        <th>Name</th>
        <th>Date of Birth</th>
        <th>Village</th>
    </tr>
    <tbody id = "patienttable">
    </tbody>


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
    echo '<td><a href=md.php?patientId='.$row['id']." style='color: #0B0080 '>".$row['name'].'</a></td>';
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

<script>
    $.material.init();
</script>


    </body>





</html>
