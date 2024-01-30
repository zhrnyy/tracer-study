<?php 

/*
Hook: admin_menu
*/
add_action('admin_menu', 'menuCrud');
function menuCrud(){
    add_menu_page( 
		'Form Alumni',              //page title
		'Form Alumni',              //menu title
		'manage_options',           //capability
        'menu-crud',                //slug URL
		'callbackCrud',             //callback
		'dashicons-admin-generic',  // menu icon
		6                           //posisi menu
	); 
}

function callbackCrud(){
    include TEMP_DIR . 'tampil-table.php';
}
?>