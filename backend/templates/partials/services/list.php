<?php 
use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Utils;
use ConnectpxBooking\Backend\Wp\Tables;
?>
<?php 
$table = new Tables\Services();
$table->prepare_items(); 
$table->display(); 
?>