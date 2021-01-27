<?php

global $wpdb, $current_user, $wpscfunction;

$WP_PATH = implode("/", (explode("/", $_SERVER["PHP_SELF"], -6)));
require_once($_SERVER['DOCUMENT_ROOT'].$WP_PATH.'/wp/wp-load.php');

if(
!empty($_POST['postvarsmessageid'])
){
$table_pm_users = 'wpqa_pm_users';
$pm_users_data_update = array('viewed' => 1);
$pm_users_data_where = array('pm_id' => $_POST['postvarsmessageid']);
$wpdb->update($table_pm_users , $pm_users_data_update, $pm_users_data_where);

}

?>
