<?php

if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
    exit();

// --- Get options ---	
define('EG_ATTACH_OPTIONS_ENTRY', 'EG-Attachments-Options'); 
$eg_attach_options = get_option(EG_ATTACH_OPTIONS_ENTRY);

// --- Delete options (plugins and widgets ---
if ($eg_attach_options['uninstall_del_option']) {
	delete_option(EG_ATTACH_OPTIONS_ENTRY);	

}

?>