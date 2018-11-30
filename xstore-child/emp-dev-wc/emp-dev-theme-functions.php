<?php

/**
 * Exclude products from a particular category on the shop page
 */
function empdev_exclude_cat_on_shop_page_query( $q ) {

	$tax_query = (array) $q->get( 'tax_query' );

	$tax_query[] = array(
		'taxonomy' => 'product_cat',
		'field' => 'slug',
		'terms' => array( 'uncategorised', 'black-friday-sale' ), // Don't display products in the clothing category on the shop page.
		'operator' => 'NOT IN'
	);


	$q->set( 'tax_query', $tax_query );

}
add_action( 'woocommerce_product_query', 'empdev_exclude_cat_on_shop_page_query' );

add_filter( 'woocommerce_add_to_cart_validation', 'emddev_conditional_product_in_cart_dynamic', 10, 2 );

function emddev_conditional_product_in_cart_dynamic( $passed, $product_id ) {

	// HERE define your 4 specific product Ids
	//$products_ids = array( 7131, 9026 );
	$products_ids = get_option( 'empdev_purchase_one_at_time', false );

	$addon_product_ids = get_option( 'empdev_enable_addon_checkout', false );

	// Searching in cart for IDs
	if ( ! WC()->cart->is_empty() && $products_ids != false  ) {
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$item_pid = $cart_item['product_id'];
			$product_message_title_cart = trim( get_post_meta( $item_pid, '_empdev_purchase_product_title_message', true ) );

			$product_message_title_cart = ($product_message_title_cart != '') ? $product_message_title_cart : get_the_title( $item_pid );

			//	// If current product is from the targeted IDs and a another targeted product id in cart
			if ( in_array( $item_pid, $products_ids ) && in_array( $product_id, $products_ids ) && $product_id != $item_pid ) {
				$passed = false; // Avoid add to cart
				$message_title = "Sorry, this product can't be purchased at the same time with other special offers!";
				break; // Stop the loop
			}
		}
	}

	if ( WC()->cart->is_empty() ) {

		if ( in_array( $product_id, $addon_product_ids ) ) {
			$passed        = false; // Avoid add to cart
			$message_title = "Sorry, you can only purchase this product as an add on, please add item to your cart.";

		}
	}

//	$product_message_title = trim( get_post_meta( $product_id, '_empdev_purchase_product_title_message', true ) );
//	$product_message_title = ($product_message_title != '') ? $product_message_title : get_the_title( $product_id );

	if ( ! $passed ) {
		// Displaying a custom message
		$message = __( $message_title, "woocommerce" );
		wc_add_notice( $message, 'error' );
	}

	if( $passed ){
		return $passed;
	}

}
function emddev_conditional_product_in_cart( $passed, $product_id, $quantity) {

	// HERE define your 4 specific product Ids
	$products_ids = array( 10952, 9811 );

	// Searching in cart for IDs
	if ( ! WC()->cart->is_empty() ) {
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$item_pid = $cart_item['product_id'];
			// If current product is from the targeted IDs and a another targeted product id in cart
			if ( in_array( $item_pid, $products_ids ) && in_array( $product_id, $products_ids ) && $product_id != $item_pid ) {
				$passed = false; // Avoid add to cart
				break; // Stop the loop
			}
		}
	}


	if ( ! $passed ) {
		// Displaying a custom message
		$message = __( "Sorry, Amazing Intro Offer and Crazy Pack Offer can't be purchased at the same time!", "woocommerce" );
		wc_add_notice( $message, 'error' );
	}

	if( $passed ){
		return $passed;
	}

}

if ( class_exists( 'WJECF_Wrap' ) ) {

	add_filter( 'woocommerce_coupon_is_valid', 'empdev_exclude_sale_free_products', 20, 2 );

	function empdev_exclude_sale_free_products( $valid, $coupon ) {

		$wrap_coupon          = WJECF_Wrap( $coupon );
		$exclude_sales_items  = $wrap_coupon->get_meta( 'exclude_sale_items' );
		$get_free_product_ids = WJECF_API()->get_coupon_free_product_ids( $coupon );

		if ( ! empty( $get_free_product_ids ) && $exclude_sales_items === true ) {

			$get_coupon_minimum_amount = $wrap_coupon->get_meta( 'minimum_amount' );

			$cart = WC()->cart->get_cart();

			//var_dump(WC()->cart->get_totals());
			//reference meta abstract-wc-product.php

			$calculate_regular_price = 0;
			foreach ( $cart as $cart_item_key => $cart_item ) {

				$cart_item_id = $cart_item['product_id'];

				if ( ! in_array( $cart_item_id, $get_free_product_ids ) ) {
					$sale_price         = $cart_item['data']->get_sale_price();
					$cart_item_quantity = $cart_item['quantity'];

					if ( empty( $sale_price ) ) {

						$regular_price = $cart_item['data']->get_regular_price();

						$calculate_regular_price += (float) $regular_price * (int) $cart_item_quantity;
					}
				}

			}

			if ( $calculate_regular_price < (float) $get_coupon_minimum_amount ) {
				return false;
			}

		}

		return $valid;
	}
}

function etheme_top_links($args = array()) {

	$links = etheme_get_links($args);
	if( ! empty($links)) :
		?>

			<?php foreach ($links as $link): ?>

				<?php

				$submenu = '';

				if( isset( $link['submenu'] ) ) {
					$submenu = $link['submenu'];
				}

				printf(
					$submenu
				);
				?>
			<?php endforeach ?>

	<?php endif;

}

function etheme_get_links($args) {
	extract(shortcode_atts(array(
		'short'  => false,
		'popups'  => true,
	), $args));
	$links = array();

	$reg_id = etheme_tpl2id('et-registration.php');

	$login_link = wp_login_url( get_permalink() );

	if( class_exists('WooCommerce')) {
		$login_link = get_permalink( get_option('woocommerce_myaccount_page_id') );
	}

	if(etheme_get_option('promo_popup')) {
		$links['popup'] = array(
			'class' => 'popup_link',
			'link_class' => 'etheme-popup',
			'href' => '#etheme-popup-holder',
			'title' => etheme_get_option('promo-link-text'),
		);
		if(!etheme_get_option('promo_link')) {
			$links['popup']['class'] .= ' hidden';
		}
		if(etheme_get_option('promo_auto_open')) {
			$links['popup']['link_class'] .= ' open-click';
		}
	}

	if( etheme_get_option('top_links') ) {
		$class = ( etheme_get_header_type() == 'hamburger-icon' ) ? ' type-icon' : '';
		if ( is_user_logged_in() ) {
			if( class_exists('WooCommerce')) {
				if ( has_nav_menu( 'my-account' ) ) {
					$submenu = wp_nav_menu(array(
						'theme_location' => 'my-account',
						'before' => '',
						'container_class' => 'menu-main-container',
						'after' => '',
						'link_before' => '',
						'link_after' => '',
						'depth' => 100,
						'fallback_cb' => false,
						'walker' => new ETheme_Navigation,
						'echo' => false
					));
				} else {
					$submenu = '<ul class="dropdown-menu">';
					$permalink = wc_get_page_permalink( 'myaccount' );

					foreach ( wc_get_account_menu_items() as $endpoint => $label ) {
						$url = ( $endpoint != 'dashboard' ) ? wc_get_endpoint_url( $endpoint, '', $permalink ) : $permalink ;
						$submenu .= '<li class="' . wc_get_account_menu_item_classes( $endpoint ) . '"><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
					}

					$submenu .= '</ul>';
				}

				$links['my-account'] = array(
					'class' => 'my-account-link' . $class,
					'link_class' => '',
					'href' => get_permalink( get_option('woocommerce_myaccount_page_id') ),
					'title' => esc_html__( 'Account', 'xstore' ),
					'submenu' => $submenu
				);

			}
			// $links['logout'] = array(
			//     'class' => 'logout-link' . $class,
			//     'link_class' => '',
			//     'href' => wp_logout_url(home_url()),
			//     'title' => esc_html__( 'Logout', 'xstore' )
			// );
		} else {

			$login_text = ($short) ? esc_html__( 'Sign In', 'xstore' ): esc_html__( 'Login | Register', 'xstore' );

//			$links['login'] = array(
//				'class' => 'login-link' . $class,
//				'link_class' => '',
//				'href' => $login_link,
//				'title' => $login_text
//			);

			if(!empty($reg_id)) {
				$links['register'] = array(
					'class' => 'register-link' . $class,
					'link_class' => '',
					'href' => get_permalink($reg_id),
					'title' => esc_html__( 'Register', 'xstore' )
				);
			}

		}
	}

	return apply_filters('etheme_get_links', $links);
}
//wence theme
add_action('woocommerce_after_shop_loop_item_title', 'empdev_add_category_loop_item' , 3 );
function empdev_add_category_loop_item()
{
    global $product;

    echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in">' . _n( count( $product->get_category_ids() ) ) . ' ', '</span>' );
}

add_action('woocommerce_after_shop_loop_item_title', 'empdev_add_star_rating' , 4 );
function empdev_add_star_rating()
{

    global $woocommerce, $product;
    $average = $product->get_average_rating();

    echo '<div class="star-rating"><span style="width:'.( ( $average / 5 ) * 100 ).'%"><strong itemprop="ratingValue" class="rating">'.$average.'</strong> '.__( 'out of 5', 'woocommerce' ).'</span></div>';

}

add_action( 'woocommerce_after_shop_loop_item_title', 'empdev_after_shop_loop_item' , 15 );
function empdev_after_shop_loop_item() {
    global $post , $product;
    $stock = get_post_meta( $post->ID, '_stock', true );
    if( $product->is_in_stock() ) {

        echo "<span class='stock'>In Stock</span>";

    } else {
        echo "<span class='stock' style='color:#f7931e;'>Out of Stock</span>";
    }
}
//add_action( 'woocommerce_after_shop_loop_item_title', 'quick_view_after_shop_loop_item' , 18 );
//function quick_view_after_shop_loop_item() {
//    global $post;
//    echo "<span class='show-quickly' data-prodid=".$post->ID."></span>";
//}

function empdev_quick_view_outofstock() {
    global $post;
    $stock = get_post_meta( $post->ID, '_stock', true );
    if( $stock <= 0 ) {
        echo "<div class='qv-out-of-stock'><span class='stock' style='color:#f7931e;'>Out of Stock</span></div>";
    }

}