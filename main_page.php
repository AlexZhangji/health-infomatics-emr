<?php
/**
 * Login screen.
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
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Brady Miller <brady@sparmy.com>
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @author  Scott Wakefield <scott.wakefield@gmail.com>
 * @author  ViCarePlus <visolve_emr@visolve.com>
 * @author  Julia Longtin <julialongtin@diasp.org>
 * @author  cfapress
 * @author  markleeds
 * @link    http://www.open-emr.org
 */

$fake_register_globals=false;
$sanitize_all_escapes=true;

$ignoreAuth=true;
include_once("../globals.php");
include_once("$srcdir/sql.inc");
?>
<html>
    <head>
        <title>Owa Basic App</title>
         <?php html_header_show();?>
        <link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
        <link rel=stylesheet href="../themes/main_page.css" type="text/css">

        <script language='JavaScript' src="../../library/js/jquery-1.4.3.min.js"></script>
        <script language='JavaScript'> 
        
       
  


    </head>
    <body  >
    	<h id = "heading">

    		<p id = "heading"><img class="LoginLogo" src="img/Login/Global_Brigades_Logo.png" alt="Login Image" style="width:200px;height:100px;"></p>

        </h>
        <ul>
		  <li class = "left"><a href="#home">Home</a></li>
		  <li class = "left"><a href="#news">News</a></li>
		  <li class = "left"><a href="#contact">Contact</a></li>
		  <li class = "left"><a href="#tutorial">Tutorial</a></li>
		  
		  <li class = "right"><a href="#logout">Logout</a></li>
		  <li class = "right">User</li>
		  <li class = "right"><a href="#settings">Settings</a></li>


		</ul>
		</br>
        <div>
            <table class="LoginTable">
                <tr>
                    
                    <td><input type="submit" value="To do List" id="To do list"/></td>
                    <td><input type="submit" value="Patient Records" id="Patients"/></td>
                    <td><input type="submit" value="Messenges" id="Messenges"/></td>

                </tr>
            
                <tr>
                    
                    
                </tr>
                <tr>
                    
                    <td><input type="submit" value="Dictionary"  id="dictionary"></td>
                    <td><input type="submit" value="Data Trends"  id="datatrends"></td>

                   

                </tr>
                <tr>
                    
                   
                </tr>

            </table>
        </div>
        
    </body>
</html>
