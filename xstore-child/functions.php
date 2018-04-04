<?php
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css', array('bootstrap'));
    

    if ( is_rtl() ) {
    	wp_enqueue_style( 'rtl-style', get_template_directory_uri() . '/rtl.css');
    }

	$timestamp = strtotime("now");
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('parent-style', 'bootstrap'),'0.1.'.$timestamp
    );
}