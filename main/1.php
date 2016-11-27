<?php

$cars = array("Volvo", "BMW", "Toyota");

?>
  <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
  <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<form id="frm" method="post">
<input id="cartag" type="text" name="car">
</form>
<script>

$(function() {
var availableTags =  <?php echo json_encode($cars); ?>;
    $( "#cartag" ).autocomplete({
    source: availableTags
    });
});

</script>