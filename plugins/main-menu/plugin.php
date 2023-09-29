<?php

/**
 * Plugin name: Main Menu
 * Description: A way for admin to manage menu pages
 * 
 * 
 **/

 set_value([

	'admin_route'	=>'admin',
	'plugin_route'	=>'main-menu',
	'tables'		=>[
		'users_table' 		=> 'menus',
	],

]);

/** add to amin links **/
add_action('header-footer_main_menu',function($data){

	require plugin_path('views/frontend/menu.php');
});

