<?php
// +-----------------------------------------------------------------------------+
// Copyright (C) 2011 ZMG LLC <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Vinish K <vinish@zhservices.com>
//
// +------------------------------------------------------------------------------+
//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//
require('globals.php');
require('eRx_xml.php');
$message='';
if(!extension_loaded('soap')){
    $message.=htmlspecialchars( xl("PLEASE ENABLE SOAP EXTENSION"), ENT_QUOTES)."<br>";
}
if(!extension_loaded('curl')){
    $message.=htmlspecialchars( xl("PLEASE ENABLE CURL EXTENSION"), ENT_QUOTES)."<br>";
}
if(!extension_loaded('openssl')){
    $message.=htmlspecialchars( xl("PLEASE ENABLE OPENSSL EXTENSION"), ENT_QUOTES)."<br>";
}
if(!extension_loaded('xml')){
    $message.=htmlspecialchars( xl("PLEASE ENABLE XML EXTENSION"), ENT_QUOTES)."<br>";
}
if($message){
    echo $message;die;
}
$userRole=sqlQuery("select * from users where username=?",array($_SESSION['authUser']));
$userRole['newcrop_user_role'] = preg_replace('/erx/','',$userRole['newcrop_user_role']);
$msg='';
$warning_msg='';
$dem_check='';
$doc = new DOMDocument();
$doc->formatOutput = true;
$GLOBALS['total_count']=60;
$r = $doc->createElement( "NCScript" );
$r->setAttribute('xmlns','http://secure.newcropaccounts.com/interfaceV7');
$r->setAttribute('xmlns:NCStandard','http://secure.newcropaccounts.com/interfaceV7:NCStandard');
$r->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
$doc->appendChild( $r );

credentials($doc,$r);
user_role($doc,$r);
$page=$_REQUEST['page'];
destination($doc,$r,$page,$pid);
account($doc,$r);
if($userRole['newcrop_user_role']!='manager')
{
    location($doc,$r);
}
if($userRole['newcrop_user_role']=='doctor' || $page=='renewal')
{
    LicensedPrescriber($doc,$r);
}
if($userRole['newcrop_user_role']=='manager' || $userRole['newcrop_user_role']=='admin' || $userRole['newcrop_user_role']=='nurse')
{
    Staff($doc,$r);
}
if($userRole['newcrop_user_role']=='supervisingDoctor')
{
    SupervisingDoctor($doc,$r);
}
if($userRole['newcrop_user_role']=='midlevelPrescriber')
{
    MidlevelPrescriber($doc,$r);
}
$prescIds='';
if($pid)
{
    $allergy=Patient($doc,$r,$pid);
    $active = '';
    if($GLOBALS['erx_upload_active']==1)
        $active = 'and active=1';    
    $res_presc=sqlStatement("select id from prescriptions where patient_id=? and erx_source='0' and erx_uploaded='0' $active limit 0,".$GLOBALS['total_count'],array($pid));
    $presc_limit=sqlNumRows($res_presc);
    $med_limit=$GLOBALS['total_count']-$presc_limit;
    while($row_presc=sqlFetchArray($res_presc))
    {
        $prescIds.=$row_presc['id'].":";
    }
    $prescIds=preg_replace('/:$/','',$prescIds);
    if($_REQUEST['id'] || $prescIds)
    {
        if($_REQUEST['id'])
        $prescArr=explode(':',$_REQUEST['id']);
        elseif($prescIds)
        $prescArr=explode(':',$prescIds);
        foreach($prescArr as $prescid)
        {
            if($prescid)
            OutsidePrescription($doc,$r,$pid,$prescid);
        }
    }
    else
    {
        OutsidePrescription($doc,$r,$pid,0);
    }    
    if($res_presc<$GLOBALS['total_count'])
    $uploaded_med_arr =PatientMedication($doc,$r,$pid,$med_limit);
}
$xml = $doc->saveXML();
$xml = preg_replace('/"/',"'",$xml);
//echo $xml."<br><br>";
$xml = stripStrings($xml,array('&#xD;'=>'','\t'=>''));
//$xml = stripStrings($xml,array('&#xD;'=>'','\t'=>'','\r'=>'','\n'=>''));
if($dem_check){
    echo "<b>".htmlspecialchars( xl("Warning"), ENT_NOQUOTES).":</b><br><br>";
    echo $dem_check."<br>";
    echo htmlspecialchars( xl("The page will be redirected to Demographics. You can edit the country field and clickthrough to NewCrop again."), ENT_NOQUOTES);
    ob_end_flush();
    ?>
    <script type="text/javascript">
    window.setTimeout(function nav(){
        window.location="patient_file/summary/demographics_full.php";
    },5000);
    </script>
    <?php
    die;
}
if($msg)
{
    echo htmlspecialchars( xl('The following fields have to be filled to send request.'), ENT_NOQUOTES);
    echo "<br>";
    echo $msg;    
    die;
}
if($warning_msg)
{    
    echo "<font style='font-weight:bold;font-size:15px'>".htmlspecialchars( xl("Warning"), ENT_NOQUOTES)." : </font><br>".$warning_msg;
    echo "<br><b>".htmlspecialchars( xl('This will not prevent you from going to the e-Prescriptions site.'), ENT_NOQUOTES)."</b>";
    sleep(2);
}
//################################################
//XML GENERATED BY OPENEMR
//################################################
//$fh=fopen('click_xml.txt','a');
//fwrite($fh,$xml);
//echo $xml;
//die;
//################################################
if(!extension_loaded('curl'))
{
    echo htmlspecialchars( xl('PHP CURL module should be enabled in your server.'), ENT_NOQUOTES);die;
}
$error = checkError($xml);
if($error==0)
{
    if($page=='compose'){
        sqlQuery("update patient_data set soap_import_status=1 where pid=?",array($pid));
    }
    elseif($page=='medentry'){
        sqlQuery("update patient_data set soap_import_status=3 where pid=?",array($pid));
    }
    foreach($allergy as $allId)
    {
        sqlQuery("update lists set erx_uploaded='1'  where type='allergy' and pid=? and id=?",array($pid,$allId));
    }
    $prescArr=explode(':',$prescIds);
    foreach($prescArr as $prescid)
    {
        sqlQuery("update prescriptions set erx_uploaded='1' ,active='0' where patient_id=? and id=?",array($pid,$prescid));
    }
    foreach($uploaded_med_arr as $value)
    {
        sqlQuery("update lists set erx_uploaded='1' where id=?",array($value));
        //sqlQuery("update lists set enddate=".date('Y-m-d')." where 
        //(enddate is null or enddate = '' ) and id=?  ",array($value));     
    }	
?>
    <script language='JavaScript'>
    <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
    </script>
    <form name='info' method='post' action="<?php echo getErxPath()?>" onsubmit='return top.restoreSession()'>
        <input type='submit' style='display:none'>
        <input type='hidden' id='RxInput' name='RxInput' value="<?php echo $xml;?>">
    </form>
    <script type="text/javascript" src="../library/js/jquery.1.3.2.js"></script>
    <script type='text/javascript'>
    document.forms[0].submit();
    </script>
<?php
}
else
{
    echo htmlspecialchars( xl('NewCrop call failed', ENT_NOQUOTES));
}
?>
