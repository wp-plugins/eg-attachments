<?php
	if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
		exit ();

	$options = get_option('EG-Attachments-Options');
	if ( isset($options) && $options['uninstall_del_options']) {

		/*
		 * Remove options
		 */
		delete_option('EG-Attachments-Options');

		/*
		 * Remove templates
		 */
		global $wpdb;
		$wpdb->query("DELETE FROM $wpdb->posts WHERE post_type = 'egatmpl'");

		/*
		 * Remove all entries related to EG-Attachments, including transient
		 */
		$wpdb->query("DELETE FROM $wpdb->options WHERE option_name like '%eg-attachments%'");

		/*
		 * Remove stats table
		 */
		$wpdb->query("DROP TABLE IF EXISTS ${wpdb->prefix}eg_attachments_clicks");
	}
?>