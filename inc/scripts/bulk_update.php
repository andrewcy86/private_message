<?php

global $wpdb, $current_user, $wpscfunction;

$WP_PATH = implode("/", (explode("/", $_SERVER["PHP_SELF"], -6)));
require_once($_SERVER['DOCUMENT_ROOT'].$WP_PATH.'/wp/wp-load.php');

$bulk_action = $_POST['postvaraction'];
$selection = $_POST['postvarselection'];

$selection_arr = explode (",", $selection); 

if(isset($bulk_action) && isset($selection) && ($bulk_action != '')){

$table_pm_users = $wpdb->prefix . 'pm_users';


if($selection != ''){
if($bulk_action == 'read') {

foreach($selection_arr as $key) {
$pm_users_data_update = array('viewed' => 1);
$pm_users_data_where = array('pm_id' => $key);
$wpdb->update($table_pm_users , $pm_users_data_update, $pm_users_data_where);
}
echo "The selected messages have been marked as read.";
}

if($bulk_action == 'delete') {

foreach($selection_arr as $key) {
$pm_users_data_update = array('deleted' => 2);
$pm_users_data_where = array('pm_id' => $key);
$wpdb->update($table_pm_users , $pm_users_data_update, $pm_users_data_where);
}
echo "The selected messages have been deleted.";
}

} else {
echo "Nothing Selected.";
}

} else {
echo "Please make a bulk action selection.";
}

?>
