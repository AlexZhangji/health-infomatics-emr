<?php
$ignoreAuth = true;
include_once ("../globals.php");
?>
<HTML>
<head>
<?php html_header_show(); ?>
<TITLE><?php xl ('Login','e'); ?></TITLE>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<link rel=stylesheet href="../themes/login.css" type="text/css">

</HEAD>

<frameset >
  
  <frame src="<?php echo $rootdir;?>/login/login.php" name="Login" scrolling="auto" frameborder="NO">
  <!--<frame src="<?php echo $rootdir;?>/login/filler.php" name="Filler Bottom" scrolling="no" noresize frameborder="NO">-->
</frameset>

<noframes><body bgcolor="#FFFFFF">

</body></noframes>

</HTML>
