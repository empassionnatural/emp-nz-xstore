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

//embed admin scripts
add_action( 'admin_enqueue_scripts', 'empdev_custom_admin_scripts' );

function empdev_custom_admin_scripts(){	

	wp_register_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2.full.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'select2' );

	wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'selectWoo' );

	wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select.min.js', array( 'jquery', 'selectWoo' ) );
	wp_enqueue_script( 'wc-enhanced-select' );
}

//enqueue custom scripts
add_action( 'wp_enqueue_scripts', 'empdev_custom_scripts_frontend', 99 );

function empdev_custom_scripts_frontend(){
	wp_enqueue_style( 'custom-style', get_stylesheet_directory_uri() . '/css/custom-style.css', array(), '3.1.1' );

	wp_enqueue_style( 'ultimate-animate' );
	wp_enqueue_script( 'ultimate-appear' );
	wp_enqueue_script( 'ultimate-custom' );
	wp_enqueue_script( 'ultimate-vc-params' );

}

// check for empty-cart get param to clear the cart
add_action( 'woocommerce_init', 'woocommerce_clear_cart_url' );
function woocommerce_clear_cart_url() {
	global $woocommerce;
	if ( isset( $_GET['empty-cart'] ) ) {
		$woocommerce->cart->empty_cart();
	}
}
add_action( 'woocommerce_cart_actions', 'empdev_add_clear_cart_button', 20 );
function empdev_add_clear_cart_button() {

	echo '<button class="btn gray" onclick="if(confirm(\'Are you sure to remove all items?\'))window.location=\'//empassion.co.nz/cart/?empty-cart=true\';else event.stopPropagation();event.preventDefault();">' . __( "Empty Cart", "woocommerce" ) . '</button>';

}

//wholesale notice filter
if( class_exists( 'WWP_Wholesale_Prices' ) ) {
	require_once( get_stylesheet_directory() . '/woocommerce-wholesale-prices-premium/class-wwpp-wholesale-price-requirement.php' );
}

add_action( 'widgets_init', 'empdev_product_banner_widget' );
function empdev_product_banner_widget() {
	register_sidebar( array(
		'name' => __( 'Before Product Top Banner', 'empassion' ),
		'id' => 'before-product-top-banner',
		'description' => __( 'Display banner after breadcrumb on product pages.', 'empassion' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="widgettitle">',
		'after_title'   => '</h2>',
	) );
}

