<?php
include_once("../../globals.php");
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Patient Summary','e'); ?></title>
</head>

<frameset rows="50%,50%">

 <frame src="demographics.php" name="Demographics" scrolling="auto">

<?php if ($GLOBALS['athletic_team']) { ?>
 <frameset cols="25%,50%,*">
  <frame src="stats.php" name="Stats" scrolling="auto">
  <frame src="pnotes.php" name="Notes" scrolling="auto">
  <frame src="fitness_status.php" name="Fitness" scrolling="auto">
 </frameset>
<?php } else { ?>
 <frameset cols="20%,80%">
  <frame src="stats.php" name="Stats" scrolling="auto">
  <frame src="pnotes.php" name="Notes" scrolling="auto">
 </frameset>
<?php } ?>

</frameset>

</html>
