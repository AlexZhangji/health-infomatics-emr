<?php /* Smarty version 2.3.1, created on 2016-10-12 01:40:30
         compiled from default/views/header.html */ ?>
<html>
<head>
<!-- Get the style sheet for the theme defined in globals.php -->
<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'] ?>" type="text/css">


<?php if ($this->_tpl_vars['cal_ui'] == 3): ?>
<!-- this style sheet is used for the ajax_* style calendars -->
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'].'/interface/themes/ajax_calendar.css'; ?>" type="text/css">
<!--[if IE]>
        <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'].'/interface/themes/ajax_calendar_ie.css'; ?>" type="text/css"/>
<![endif]-->

<!-- the javascript used for the ajax_* style calendars -->
<script type="text/javascript" src="<?php  echo $GLOBALS['webroot']  ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php  echo $GLOBALS['webroot']  ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php  echo $GLOBALS['webroot']  ?>/library/js/jquery-1.6.4.min.js"></script>
<script type="text/javascript" src="<?php  echo $GLOBALS['webroot']  ?>/library/js/calendarDirectSelect.js"></script>
<script>function event_time_click(elem){EditEvent($(elem).parents("div.event_appointment").get(0))} </script>
<?php 
/**
 * @param string $displayString This is the text to be displayed(most likely representing the time of an event).  It is the responsibility of the caller to escape any entities as needed. This allows html tags to be used in the $displayString if desired.
 * @return string html anchor element with javascript onclick event that edits an appointment
 */
function create_event_time_anchor($displayString)
{
    $title=htmlspecialchars(xl('Click to edit'));
    return "<a class='event_time' onclick='event_time_click(this)' title='" .$title."'>".$displayString."</a>";
}
 ?>
<?php endif; ?>

</head>
<?php 
/* in an attempt to not 'rock the boat' too much the concurrent_layout
 * color scheme remains unchanged
 */
if ($GLOBALS['concurrent_layout']) {
    echo "<body style='background-color:".$GLOBALS['style']['BGCOLOR2']."'>";
}
else {
    echo "<body class='body_top'>";
}
 ?>