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

<html manifest="app.appcache">
  <head>
    <title>
    <?php echo text($openemr_name) ?>
    </title>

    <script type="text/javascript" src="../../library/topdialog.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script src="JQueryUI-v1.12.1.js"></script>
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


      if (localStorage.getItem("Sizeofnewpatientcache") === null){
        localStorage.setItem("Sizeofnewpatientcache", 0);



      }
      else{
        if (localStorage.getItem("Sizeofnewpatientcache") == 0){
          alert("there are no pending registrations");
        }
        else{
          alert("there are pending registrations");
          alert(localStorage.getItem("Sizeofnewpatientcache"));

          //for (int i = 0; i < parseInt(localStorage.getItem("Sizeofnewpatientcache")); i++){




          var names;
          var gender;
          var address1;
          var address2;
          var date;
          var cityVillage;
          var stateProvince;
          var selectCountry;
          var postalCode;
          var phoneNumber;
          var test = false;

           for( var i = parseInt(localStorage.getItem("Sizeofnewpatientcache")) - 1; i >= 0; i--) {
                    var keyval = "NewPatient" + i;
                    var getdata = JSON.parse(localStorage.getItem(keyval));
                    //getdata = getdata.split(",");


                    names = getdata[0];
                    gender = getdata[1];
                    address1= getdata[2];
                    address2= getdata[3];
                    date= getdata[4];
                    cityVillage= getdata[5];
                    stateProvince= getdata[6];
                    selectCountry= getdata[7];
                    postalCode= getdata[8];
                    phoneNumber= getdata[9];
                    //alert(names+gender+address1);

                  $.ajax({
                        type: "POST",
                        url: "createpatient.php",
                        //dataType: "JSON",
                        data: {'namefield': names,
                               'gender': gender,
                               'address1': address1,
                               'address2': address2,
                               'date': date,
                               'cityVillage': cityVillage,
                               'stateProvince': stateProvince,
                               'selectCountry': selectCountry,
                               'postalCode': postalCode,
                               'phoneNumber': phoneNumber,
                               'keyval':keyval
                                                 },
                        success:function(result){
                         var keyvals = result;
                              localStorage.removeItem(keyvals);
                               localStorage["Sizeofnewpatientcache"] = parseInt(localStorage.getItem("Sizeofnewpatientcache")) - 1;


                        },
                        error: function() {
                          // Save


                        }

                  });
                 //



            }
            //alert(test);


             /* if (test){
                            for (var y = 0; y < parseInt(localStorage.getItem("Sizeofnewpatientcache")); y++){
                              var keyvals = "NewPatient" + y;
                              localStorage.removeItem(keyvals);

                            }
                            localStorage["Sizeofnewpatientcache"] = 0;
                          }


        //  }*/



        }

      }


    </script>
    <link rel=stylesheet href="../themes/registration.css" type="text/css">
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

    <link rel=stylesheet href="../themes/main_screen.css" type="text/css">


  </head>


   <body  >

    </br>



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
    </br>
     <div class="container">
        <form action="searchpatient.php" method="get">
                      <input type="submit" value="Back"
                               name="create_new_patient" id="backbutton" onclick="top.restoreSession()"  />
        </form>



        </br>
        </br>

            <script>
    $(document).ready(function(){
      $("#submitbutton").click(function(e){

         var names = $("#namefield").val();

         var genderarray = document.getElementsByName("gender");
         var gender;
         if (genderarray[0].checked){
            gender = genderarray[0].value;

         }
         else if(genderarray[1].checked){
            gender = genderarray[1].value;
         }
         else{
          gender = genderarray[2].value;
         }
         var address1 = $("#address1").val();
          var address2 = $("#address2").val();
          var date = $("#date").val();
          var cityVillage = $("#cityVillage").val();
          var stateProvince = $("#stateProvince").val();
          var selectCountry = $("#selectCountry").val();
          var postalCode = $("#postalCode").val();
          var phoneNumber = $("#phoneNumber").val();
          alert(postalCode);
          alert(phoneNumber);
          var numberofcachedfiles = localStorage.getItem("Sizeofnewpatientcache");
          alert(names);
        $.ajax({
                type: "POST",
                url: "createpatient.php",
                //dataType: "JSON",
                data: {'namefield': names,
                       'gender': gender,
                       'address1': address1,
                       'address2': address2,
                       'date': date,
                       'cityVillage': cityVillage,
                       'stateProvince': stateProvince,
                       'selectCountry': selectCountry,
                       'postalCode': postalCode,
                       'phoneNumber': phoneNumber
                                         },
                success:function(result){
                  alert(result);
                },
                error: function() {
                  // Save
                  alert("failed");
                  var cacheditems = parseInt(localStorage.getItem("Sizeofnewpatientcache"));
                  var cacheditemarr = [names,gender,address1,address2,date,cityVillage,stateProvince,selectCountry,postalCode,phoneNumber];

                  var key = "NewPatient" + cacheditems;
                 // alert(key);
                  localStorage.setItem(key, JSON.stringify(cacheditemarr));
                  localStorage["Sizeofnewpatientcache"] = cacheditems + 1;
                  //alert(key);
                  //alert(JSON.parse(localStorage.getItem(key)));

                }

        });

      });
    });
    </script>


        <h2>Register a patient &nbsp;&nbsp;</h2>
        <p class="left"><label> Name:&nbsp;</label> <input id="namefield" class="" name="namefield" size="40" type="text" value="" /></p>
        <p>&nbsp;</p>
        <p>Gender:&nbsp;<input name="gender" type="radio" value="male" /> Male &nbsp; <input name="gender" type="radio" value="female" /> Female &nbsp;<input name="gender" type="radio" value="other" /> Other</p>
        <p>&nbsp;</p>
        <p><label for="inputdob">Date of Birth</label><input type="date" id = "date"  name="date"/>&nbsp;</p>
        <p>&nbsp;</p>
        <p>Address <span id="fr9101" class="field-error" style="display: none;"> </span></p>
        <p class="left"><label> Address 1&nbsp;</label> <input id="address1" class="" name="address1" size="40" type="text" value="" /></p>
        <p class="left"><label> Address 2 </label> <input id="address2" class="" name="address2" size="40" type="text" value="" /> <span id="fr197" class="field-error" style="display: none;"> </span></p>
        <p class="clear">&nbsp;<label>City/Village </label>
        <input id="cityVillage" class="" name="cityVillage" size="10" type="text" value="" />&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;
        <label>State/Province </label>
        <input id="stateProvince" class="" name="stateProvince" size="10" type="text" value="" /></p>
        <p class="left"><label> Country </label><select name = "selectCountry" id = "selectCountry">
            <option value="AF">Afghanistan</option>
            <option value="AX">&Aring;land Islands</option>
            <option value="AL">Albania</option>
            <option value="DZ">Algeria</option>
            <option value="AS">American Samoa</option>
            <option value="AD">Andorra</option>
            <option value="AO">Angola</option>
            <option value="AI">Anguilla</option>
            <option value="AQ">Antarctica</option>
            <option value="AG">Antigua and Barbuda</option>
            <option value="AR">Argentina</option>
            <option value="AM">Armenia</option>
            <option value="AW">Aruba</option>
            <option value="AU">Australia</option>
            <option value="AT">Austria</option>
            <option value="AZ">Azerbaijan</option>
            <option value="BS">Bahamas</option>
            <option value="BH">Bahrain</option>
            <option value="BD">Bangladesh</option>
            <option value="BB">Barbados</option>
            <option value="BY">Belarus</option>
            <option value="BE">Belgium</option>
            <option value="BZ">Belize</option>
            <option value="BJ">Benin</option>
            <option value="BM">Bermuda</option>
            <option value="BT">Bhutan</option>
            <option value="BO">Bolivia, Plurinational State of</option>
            <option value="BQ">Bonaire, Sint Eustatius and Saba</option>
            <option value="BA">Bosnia and Herzegovina</option>
            <option value="BW">Botswana</option>
            <option value="BV">Bouvet Island</option>
            <option value="BR">Brazil</option>
            <option value="IO">British Indian Ocean Territory</option>
            <option value="BN">Brunei Darussalam</option>
            <option value="BG">Bulgaria</option>
            <option value="BF">Burkina Faso</option>
            <option value="BI">Burundi</option>
            <option value="KH">Cambodia</option>
            <option value="CM">Cameroon</option>
            <option value="CA">Canada</option>
            <option value="CV">Cape Verde</option>
            <option value="KY">Cayman Islands</option>
            <option value="CF">Central African Republic</option>
            <option value="TD">Chad</option>
            <option value="CL">Chile</option>
            <option value="CN">China</option>
            <option value="CX">Christmas Island</option>
            <option value="CC">Cocos (Keeling) Islands</option>
            <option value="CO">Colombia</option>
            <option value="KM">Comoros</option>
            <option value="CG">Congo</option>
            <option value="CD">Congo, the Democratic Republic of the</option>
            <option value="CK">Cook Islands</option>
            <option value="CR">Costa Rica</option>
            <option value="CI">C&ocirc;te d'Ivoire</option>
            <option value="HR">Croatia</option>
            <option value="CU">Cuba</option>
            <option value="CW">Cura&ccedil;ao</option>
            <option value="CY">Cyprus</option>
            <option value="CZ">Czech Republic</option>
            <option value="DK">Denmark</option>
            <option value="DJ">Djibouti</option>
            <option value="DM">Dominica</option>
            <option value="DO">Dominican Republic</option>
            <option value="EC">Ecuador</option>
            <option value="EG">Egypt</option>
            <option value="SV">El Salvador</option>
            <option value="GQ">Equatorial Guinea</option>
            <option value="ER">Eritrea</option>
            <option value="EE">Estonia</option>
            <option value="ET">Ethiopia</option>
            <option value="FK">Falkland Islands (Malvinas)</option>
            <option value="FO">Faroe Islands</option>
            <option value="FJ">Fiji</option>
            <option value="FI">Finland</option>
            <option value="FR">France</option>
            <option value="GF">French Guiana</option>
            <option value="PF">French Polynesia</option>
            <option value="TF">French Southern Territories</option>
            <option value="GA">Gabon</option>
            <option value="GM">Gambia</option>
            <option value="GE">Georgia</option>
            <option value="DE">Germany</option>
            <option value="GH">Ghana</option>
            <option value="GI">Gibraltar</option>
            <option value="GR">Greece</option>
            <option value="GL">Greenland</option>
            <option value="GD">Grenada</option>
            <option value="GP">Guadeloupe</option>
            <option value="GU">Guam</option>
            <option value="GT">Guatemala</option>
            <option value="GG">Guernsey</option>
            <option value="GN">Guinea</option>
            <option value="GW">Guinea-Bissau</option>
            <option value="GY">Guyana</option>
            <option value="HT">Haiti</option>
            <option value="HM">Heard Island and McDonald Islands</option>
            <option value="VA">Holy See (Vatican City State)</option>
            <option value="HN">Honduras</option>
            <option value="HK">Hong Kong</option>
            <option value="HU">Hungary</option>
            <option value="IS">Iceland</option>
            <option value="IN">India</option>
            <option value="ID">Indonesia</option>
            <option value="IR">Iran, Islamic Republic of</option>
            <option value="IQ">Iraq</option>
            <option value="IE">Ireland</option>
            <option value="IM">Isle of Man</option>
            <option value="IL">Israel</option>
            <option value="IT">Italy</option>
            <option value="JM">Jamaica</option>
            <option value="JP">Japan</option>
            <option value="JE">Jersey</option>
            <option value="JO">Jordan</option>
            <option value="KZ">Kazakhstan</option>
            <option value="KE">Kenya</option>
            <option value="KI">Kiribati</option>
            <option value="KP">Korea, Democratic People's Republic of</option>
            <option value="KR">Korea, Republic of</option>
            <option value="KW">Kuwait</option>
            <option value="KG">Kyrgyzstan</option>
            <option value="LA">Lao People's Democratic Republic</option>
            <option value="LV">Latvia</option>
            <option value="LB">Lebanon</option>
            <option value="LS">Lesotho</option>
            <option value="LR">Liberia</option>
            <option value="LY">Libya</option>
            <option value="LI">Liechtenstein</option>
            <option value="LT">Lithuania</option>
            <option value="LU">Luxembourg</option>
            <option value="MO">Macao</option>
            <option value="MK">Macedonia, the former Yugoslav Republic of</option>
            <option value="MG">Madagascar</option>
            <option value="MW">Malawi</option>
            <option value="MY">Malaysia</option>
            <option value="MV">Maldives</option>
            <option value="ML">Mali</option>
            <option value="MT">Malta</option>
            <option value="MH">Marshall Islands</option>
            <option value="MQ">Martinique</option>
            <option value="MR">Mauritania</option>
            <option value="MU">Mauritius</option>
            <option value="YT">Mayotte</option>
            <option value="MX">Mexico</option>
            <option value="FM">Micronesia, Federated States of</option>
            <option value="MD">Moldova, Republic of</option>
            <option value="MC">Monaco</option>
            <option value="MN">Mongolia</option>
            <option value="ME">Montenegro</option>
            <option value="MS">Montserrat</option>
            <option value="MA">Morocco</option>
            <option value="MZ">Mozambique</option>
            <option value="MM">Myanmar</option>
            <option value="NA">Namibia</option>
            <option value="NR">Nauru</option>
            <option value="NP">Nepal</option>
            <option value="NL">Netherlands</option>
            <option value="NC">New Caledonia</option>
            <option value="NZ">New Zealand</option>
            <option value="NI">Nicaragua</option>
            <option value="NE">Niger</option>
            <option value="NG">Nigeria</option>
            <option value="NU">Niue</option>
            <option value="NF">Norfolk Island</option>
            <option value="MP">Northern Mariana Islands</option>
            <option value="NO">Norway</option>
            <option value="OM">Oman</option>
            <option value="PK">Pakistan</option>
            <option value="PW">Palau</option>
            <option value="PS">Palestinian Territory, Occupied</option>
            <option value="PA">Panama</option>
            <option value="PG">Papua New Guinea</option>
            <option value="PY">Paraguay</option>
            <option value="PE">Peru</option>
            <option value="PH">Philippines</option>
            <option value="PN">Pitcairn</option>
            <option value="PL">Poland</option>
            <option value="PT">Portugal</option>
            <option value="PR">Puerto Rico</option>
            <option value="QA">Qatar</option>
            <option value="RE">R&eacute;union</option>
            <option value="RO">Romania</option>
            <option value="RU">Russian Federation</option>
            <option value="RW">Rwanda</option>
            <option value="BL">Saint Barth&eacute;lemy</option>
            <option value="SH">Saint Helena, Ascension and Tristan da Cunha</option>
            <option value="KN">Saint Kitts and Nevis</option>
            <option value="LC">Saint Lucia</option>
            <option value="MF">Saint Martin (French part)</option>
            <option value="PM">Saint Pierre and Miquelon</option>
            <option value="VC">Saint Vincent and the Grenadines</option>
            <option value="WS">Samoa</option>
            <option value="SM">San Marino</option>
            <option value="ST">Sao Tome and Principe</option>
            <option value="SA">Saudi Arabia</option>
            <option value="SN">Senegal</option>
            <option value="RS">Serbia</option>
            <option value="SC">Seychelles</option>
            <option value="SL">Sierra Leone</option>
            <option value="SG">Singapore</option>
            <option value="SX">Sint Maarten (Dutch part)</option>
            <option value="SK">Slovakia</option>
            <option value="SI">Slovenia</option>
            <option value="SB">Solomon Islands</option>
            <option value="SO">Somalia</option>
            <option value="ZA">South Africa</option>
            <option value="GS">South Georgia and the South Sandwich Islands</option>
            <option value="SS">South Sudan</option>
            <option value="ES">Spain</option>
            <option value="LK">Sri Lanka</option>
            <option value="SD">Sudan</option>
            <option value="SR">Suriname</option>
            <option value="SJ">Svalbard and Jan Mayen</option>
            <option value="SZ">Swaziland</option>
            <option value="SE">Sweden</option>
            <option value="CH">Switzerland</option>
            <option value="SY">Syrian Arab Republic</option>
            <option value="TW">Taiwan, Province of China</option>
            <option value="TJ">Tajikistan</option>
            <option value="TZ">Tanzania, United Republic of</option>
            <option value="TH">Thailand</option>
            <option value="TL">Timor-Leste</option>
            <option value="TG">Togo</option>
            <option value="TK">Tokelau</option>
            <option value="TO">Tonga</option>
            <option value="TT">Trinidad and Tobago</option>
            <option value="TN">Tunisia</option>
            <option value="TR">Turkey</option>
            <option value="TM">Turkmenistan</option>
            <option value="TC">Turks and Caicos Islands</option>
            <option value="TV">Tuvalu</option>
            <option value="UG">Uganda</option>
            <option value="UA">Ukraine</option>
            <option value="AE">United Arab Emirates</option>
            <option value="GB">United Kingdom</option>
            <option value="US">United States</option>
            <option value="UM">United States Minor Outlying Islands</option>
            <option value="UY">Uruguay</option>
            <option value="UZ">Uzbekistan</option>
            <option value="VU">Vanuatu</option>
            <option value="VE">Venezuela, Bolivarian Republic of</option>
            <option value="VN">Viet Nam</option>
            <option value="VG">Virgin Islands, British</option>
            <option value="VI">Virgin Islands, U.S.</option>
            <option value="WF">Wallis and Futuna</option>
            <option value="EH">Western Sahara</option>
            <option value="YE">Yemen</option>
            <option value="ZM">Zambia</option>
            <option value="ZW">Zimbabwe</option>
        </select>&nbsp;&nbsp;</p>
        <p class="left"><label>Postal Code </label> <input id="postalCode" class="" name="postalCode" size="10" type="text" value="" /></p>
        <p class="left">&nbsp;</p>
        <p class="left"><label> Phone Number &nbsp;</label> <input id="phoneNumber" class="" name="phoneNumber" size="40" type="text" value="" /></p>
        <p class="left">&nbsp;</p>
        <input type="submit" value="Submit" name = "submitbutton" id = "submitbutton">

        </div>



</div>








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






    </body>





</html>
