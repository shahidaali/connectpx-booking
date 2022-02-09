<?php 
use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Utils;
use ConnectpxBooking\Backend\Wp\Tables;
?>
<?php 
$table = new Tables\Customers();
$table->prepare_items(); 
$table->display(); 
?>