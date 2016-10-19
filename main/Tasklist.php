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
include_once("$srcdir/sql.inc");

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
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
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
    <link rel=stylesheet href="../themes/tasklist.css" type="text/css">

     
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
      <span><?php echo htmlspecialchars( xl('Logout'), ENT_QUOTES) ?></span></a>
      </li>
      <li class = "right">User</li>
      <li class = "right"><a href="#settings">Settings</a></li>


    </ul>
    </br>
        <div id="container">
            
             
            <form class="date" method = "POST">
             
            Date:
            <input type="date" name="datepickerinput" id ="datepickerinput" value="<?php echo date('Y-m-d'); ?>"/> >
            


            

            
            <div id="tasklistdiv">
              <ul id="TaskList" name = "TaskList">
                <?php 


                   if (empty($_POST['TaskList'])){
                        

      
                        $userID = $_SESSION['authId'];
                        $date = date('Y-m-d');
                      
                        
                        $yourArray = array(); // make a new array to hold all your data

                        $res = sqlStatement("select Task from Task where userID = '$userID' and Date = '$date'");
                        $row = mysql_fetch_row($result);
                        $index = 0;

                        while($row = mysql_fetch_row($res)){ // loop to store the data in an associative array.
                              
                             echo '<li>' . $row[0] . '</li>';
                             $index++;
                             
                        }
                        

                       
                                              
                       
                    
                  }


                  
                  //echo $_POST['datevalue'];
 
               

                ?>




                <script>
               



                  // Datepicker
                  jQuery(function($) {

                    $("#datepickerinput").datepicker({
                      dateFormat: "yy-mm-dd",
                      onSelect: function(dateText) {
                        //alert("value changed");
                        
                        $(this).change();
                      }
                    }).on("change", function() {
                       alert("test point");
                       var newdate = $("#datepickerinput").val();

                       $.ajax({
                          type: "POST", //or GET. Whichever floats your boat.
                          url: "OutputTasks.php",
                          dataType: "JSON",
                          //ContentType: 'application/json',
                          data: { 'datevalue': newdate },
                          success: function(json) {

                                    $("#TaskList").empty();

                                    var arrayLength = json.length;
                                      for (var i = 0; i < arrayLength; i++) {
                                         $("#TaskList").append('<li>'+ json[i]+'</li>');
                                          //Do something
                                      }
                                      
                                        

                                           //Write code here for you successful update (if you want to)
                          },
                          error: function() {
                            alert("Error.");
                          }
                        });
                    });

                    

                  });
                                  
            </script>

              

              </ul>
             </div> 
             </form>

                <input class="button large" type="submit" value="New Task" id="NewTaskButton" >
              



              <div id="myModal" class="modal">
                    
                <!-- Modal content -->
                <div class="modal-content">
                
                  <span class="close">×</span>
                 
                        
                          
                         
                          
               
                     
                    

                    <p class="Modal_Title">New Task</p>

                  



                     <form class = "newtask" id = "newtask" action = "newTask.php" method = "POST">





                      <div>
                        <textarea rows="4" cols="50" maxlength="240" name="taskarea" id="taskarea">
                          
                        </textarea>
                           
                      </div>
             
                      Date:

                      <input type="date" name="datenewtask" id ="datenewtask" >
                      </br>
                      
                      <input class="button large" type="submit" value="New Task" id="NewTaskModalButton" name = "submitbutton" >
                     
                    </form>
                
                </div>
                <script>
                  // Get the modal
                  var modal = document.getElementById('myModal');

                  // Get the button that opens the modal
                  var btn = document.getElementById("NewTaskButton");

                  // Get the <span> element that closes the modal
                  var span = document.getElementsByClassName("close")[0];

                  // When the user clicks the button, open the modal
                  btn.onclick = function() {
                      modal.style.display = "block";
                  }

                  // When the user clicks on <span> (x), close the modal
                  span.onclick = function() {
                      modal.style.display = "none";
                  }

                  // When the user clicks anywhere outside of the modal, close it

                  window.onclick = function(event) {
                      if (event.target == modal) {
                          modal.style.display = "none";
                      }
                  }

               


                </script>
                 <script>
               


                  // Datepicker
                /*  jQuery(function($) {

                    $("#datenewtask").datepicker({
                      dateFormat: "yy-mm-dd",
                      onSelect: function(dateText) {
                        //alert("value changed");
                        
                        $(this).change();
                      }
                    }).on("change", function() {
                       

                    
                    });

                    

                  });*/
                                  
            </script>

                

              </div>

        </div>
  
    </body>

  

  

</html>