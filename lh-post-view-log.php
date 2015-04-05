<?php
/*
Plugin Name: Post Views Log
Plugin URI: http://www.lhoracek.cz
Description: This plugins will track each post view/hit by registered users.
Version: 1.0
Author: lhoracek
Author URI: http://www.lhoracek.cz
License: GPL2
*/

$pluginURI = get_option('siteurl').'/wp-content/plugins/'.dirname(plugin_basename(__FILE__)); 
add_action('wp_head', 'log_post_view');

function lh_pvl_db_install () {
	global $wpdb;
	$table_name = $wpdb->prefix . "lh_pvl";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
	
	$sql2 = "CREATE TABLE `$table_name` (
	`id` bigint(20) NOT NULL auto_increment,
	`post_id` int(11) NOT NULL,
	`created_at` varchar(20) NOT NULL,
	`created_date` varchar(20) default NULL,
	`user_id` int(11) NOT NULL,
	PRIMARY KEY  (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql2);
	}
}

register_activation_hook(__FILE__,'lh_pvl_db_install');

function wpgt_add_pages() {
	global $pluginURI;
	add_menu_page('Post Views Log', 'Post Views Log', 'manage_options', 'lh_pvl_view_post', 'lh_pvl_view_post_fn',$pluginURI.'/images/stat.png', 5.888888888888 );
}
add_action('admin_menu', 'wpgt_add_pages');

function lh_pvl_view_post_fn() { 
	ob_start();
	include_once('view.php');
	$out1 = ob_get_contents();
	ob_end_clean();	
	echo $out1;
}

function log_post_view() {
 
	global $post,$wpdb;
	if(is_single())
	{
		$current_user = wp_get_current_user();
		if ( $current_user ) {
			$table_name = $wpdb->prefix . "lh_pvl";
			$select = "SELECT 1 FROM " . $table_name . " WHERE post_id = ". $post->ID ." AND user_id=". $current_user->ID;
			$exists = $wpdb->query( $select );
			if(!$exists){
				$insert = "INSERT INTO " . $table_name . "( post_id, created_at, created_date, user_id ) VALUES (" . $post->ID . ",'" . time() . "','" . date('Y-m-d')."', ". $current_user->ID .")";
				$results = $wpdb->query( $insert );
				if($results) $msg = "Updated";
			}
		}
	}	

}

function lh_pvl_jQuery_files() {

echo '
<script>
jQuery(function() {
	jQuery( "#from" ).datepicker({
		//defaultDate: "+1w",
		dateFormat:"yy-mm-dd",
		changeMonth: true,
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			jQuery( "#to" ).datepicker( "option", "minDate", selectedDate );
		}
	});
	jQuery( "#to" ).datepicker({
		//defaultDate: "+1w",
		dateFormat:"yy-mm-dd",
		changeMonth: true,
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			jQuery( "#from" ).datepicker( "option", "maxDate", selectedDate );
		}
	});
});// JavaScript Document    
</script>
';
}


function lh_pvl_my_script() {
	global $pluginURI;
	wp_enqueue_script('jquery-ui-datepicker');
	wp_register_style('jquery-ui-css', $pluginURI . '/css/jquery-ui.css', array(), '1.9.0' );
	wp_enqueue_style( 'jquery-ui-css' );	
}

add_action('admin_init', 'lh_pvl_my_script');
add_action('admin_head', 'lh_pvl_jQuery_files');
?>
