<?php

/**
* Plugin name: Home Page
* Description: Display the Home Page
* 
**/


/** displays the view file **/
add_action('view',function(){

   require plugin_path('views/view.php');
});