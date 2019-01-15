<?php

add_action('woocommerce_add_to_cart', 'empdev_new_customers_redirect_purchase', 100);
function empdev_new_customers_redirect_purchase() {

	if( ! is_user_logged_in() ){
		if ( ! WC()->cart->is_empty()  ) {

			$cart = WC()->cart->get_cart();
			$empdev_limit_new_customers_ids = get_option( 'empdev_limit_new_customers_ids', false );
			$blog_link = get_bloginfo('url');

			foreach ( $cart as $cart_item_key => $cart_item ) {

				$cart_item_id = $cart_item['product_id'];

				if ( in_array( $cart_item_id, $empdev_limit_new_customers_ids ) ) {

					wp_redirect( $blog_link . '/my-account/?redirect_permalink='.$blog_link.'/cart/');
					die;

				}

			}
		}
	}

}


add_action('woocommerce_after_cart', 'empdev_new_customers_cart_restriction');

add_action('woocommerce_after_checkout_form', 'empdev_new_customers_cart_restriction');

function empdev_new_customers_cart_restriction(){

	if ( ! WC()->cart->is_empty()  ) {

		$empdev_limit_new_customers_ids = get_option( 'empdev_limit_new_customers_ids', false );
		$customer_orders = EMPDEV_WC_Static_Helper::get_recent_order();
		$blog_link = get_bloginfo('url');

        if ( ( is_cart() || is_checkout () ) && ! empty ( $empdev_limit_new_customers_ids ) && count( $customer_orders ) > 0 ){

            $cart = WC()->cart->get_cart();
            //var_dump($cart);
            $cart_item_id = null;
            $send_error_notice = false;
            foreach ( $cart as $cart_item_key => $cart_item ) {

                $cart_item_id = $cart_item['product_id'];

                if ( in_array( $cart_item_id, $empdev_limit_new_customers_ids ) ) {

                    $send_error_notice = true;
                    break;
                }

            }

            if($send_error_notice){
                wc_clear_notices();
                $product_title = get_the_title($cart_item_id);

                if( is_user_logged_in() ){
	                $message_title = "Sorry, ".$product_title." is only valid for new customers! ";
                } else {
	                $message_title = "Login is required to purchase ".$product_title . ". <span><a href='".$blog_link."/my-account/?redirect_permalink=".$blog_link."/cart'>Click here to login.</a></span>";
                }

                $message = __( $message_title, "woocommerce" );
                wc_add_notice( $message, 'error' );
            }

        }

	}

}

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

	add_filter( 'woocommerce_coupon_is_valid', 'empdev_exclude_sale_free_products', 100, 2 );

	function empdev_exclude_sale_free_products( $valid, $coupon ) {

		$wrap_coupon          = WJECF_Wrap( $coupon );
		$exclude_sales_items  = $wrap_coupon->get_meta( 'exclude_sale_items' );
		$get_free_product_ids = WJECF_API()->get_coupon_free_product_ids( $coupon );

		$get_coupon_minimum_amount = $wrap_coupon->get_meta( 'minimum_amount' );

		/*Recalculate cart to exclude sale items in minimum spend amount restriction*/
		if ( $exclude_sales_items === true && ! empty( $get_coupon_minimum_amount ) ) {

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

function etheme_ajax_search_action() {
	global $woocommerce, $wpdb, $wp_query, $product;
	$result = array(
		'status' => 'error',
		'html' => ''
	);
	if( isset( $_REQUEST['s'] ) && $_REQUEST['s'] != '') {

		$s = sanitize_text_field( $_REQUEST['s'] );
		$i = 0;
		$to = 8;

		// ! Get sku results
		if ( etheme_get_option('search_by_sku') ) {
			$sku = $_REQUEST['s'];

			// ! Should the query do some extra joins for WPML Enabled sites...
			$wmplEnabled = false;

			if(defined('WPML_TM_VERSION') && defined('WPML_ST_VERSION') && class_exists("woocommerce_wpml")){
				$wmplEnabled = true;
				// ! What language should we search for...
				$languageCode = ICL_LANGUAGE_CODE;
			}

			// ! Search for the sku of a variation and return the parent.
			$variationsSql = "
              SELECT p.post_parent as post_id FROM $wpdb->posts as p
              join $wpdb->postmeta pm
              on p.ID = pm.post_id
              and pm.meta_key='_sku'
              and pm.meta_value LIKE '%$sku%'
              ";

			// ! IF WPML Plugin is enabled join and get correct language product.
			if( $wmplEnabled ) {
				$variationsSql .=
					"join ".$wpdb->prefix."icl_translations t on
                     t.element_id = p.post_parent
                     and t.element_type = 'post_product'
                     and t.language_code = '$languageCode'";
				;
			}

			$variationsSql .= "
                  where 1
                  AND p.post_parent <> 0
                  and p.post_status = 'publish'
                  group by p.post_parent
              ";
			$variations = $wpdb->get_results($variationsSql);


			$regularProductsSql =
				"SELECT p.ID as post_id FROM $wpdb->posts as p
                    join $wpdb->postmeta pm
                    on p.ID = pm.post_id
                    and  pm.meta_key='_sku' 
                    AND pm.meta_value LIKE '%$sku%'
                    AND post_title NOT LIKE '%$sku%'
                ";
			// ! IF WPML Plugin is enabled join and get correct language product.
			if($wmplEnabled) {
				$regularProductsSql .=
					"join ".$wpdb->prefix."icl_translations t on
                     t.element_id = p.ID
                     and t.element_type = 'post_product'
                     and t.language_code = '$languageCode'";
			}
			$regularProductsSql .=
				"where 1
                and (p.post_parent = 0 or p.post_parent is null)
                and p.post_status = 'publish'
                group by p.ID";
			$regular_products = $wpdb->get_results($regularProductsSql);
		}

		// ! Get title/excerpt results
		// $title_q = "SELECT ID FROM $wpdb->posts WHERE post_title LIKE '%$s%' AND post_type = 'product'";
		$excerpt_q = "SELECT ID FROM $wpdb->posts WHERE post_excerpt LIKE '%$s%' AND post_title NOT LIKE '%$s%' AND post_type = 'product'";

		if ( ! $wmplEnabled ) {
			$title_q = "SELECT ID FROM $wpdb->posts WHERE post_title LIKE '%$s%' AND post_type = 'product'";
		} else {
			$title_q = "
                SELECT ID FROM $wpdb->posts
                JOIN {$wpdb->prefix}icl_translations ON 
                ($wpdb->posts.ID = {$wpdb->prefix}icl_translations.element_id)
                AND {$wpdb->prefix}icl_translations.language_code = '$languageCode'
                WHERE post_title LIKE '%$s%' AND post_type = 'product'
            ";
		}

		$title_q = $wpdb->get_results( $title_q );
		$excerpt_q = $wpdb->get_results( $excerpt_q );

		$title_q = array_reverse( $title_q );
		$excerpt_q = array_reverse( $excerpt_q );

		$products = array_merge( $title_q, $excerpt_q );

		$result['html'] .= '<div class="product-ajax-list"></ul>';

		if ( ! empty( $products ) || ! empty( $regular_products ) || ! empty( $variations ) ) {
			$result['status'] = 'success';
			$result['html'] .= '<h3 class="search-results-title">' . esc_html__('Products found', 'xstore') . '<a href="' . esc_url( home_url() ) . '/?s='. $s .'&post_type=product&product_cat=' . $_REQUEST['cat'] . '">' . esc_html__('View all', 'xstore' ) . '</a></h3>';
		}

		if ( ! empty( $products ) && count( $products ) > 0 ) {
			foreach ( $products as $post ) {
				if ( $i >= $to )  break;

				setup_postdata( $post );
				$product = wc_get_product( $post->ID );

				if ( ! $product->is_visible() ) continue;

				if ( $_REQUEST['cat'] ) {
					$terms = wp_get_post_terms( $post->ID, 'product_cat' );
					$categories = array();
					foreach ( $terms as $term ){
						$categories[] = $term->slug;
					}

					if ( ! in_array( $_REQUEST['cat'], $categories ) ) continue;
				}
				
				//skip uncategorised product category
				if ( has_term( 'uncategorised', 'product_cat', $post->ID ) ) continue;

				$result['html'] .= '<li>';
				$result['html'] .= '<a href="'.get_the_permalink($post->ID).'" title="'.get_the_title($post->ID).'" class="product-list-image">';
				$result['html'] .= ( get_the_post_thumbnail( $post->ID ) ) ? get_the_post_thumbnail( $post->ID ) : wc_placeholder_img( $size = 'shop_thumbnail' );
				$result['html'] .='</a>';
				$result['html'] .= '<p class="product-title"><a href="'.get_the_permalink($post->ID).'" title="'.get_the_title($product->post_id).'">'.get_the_title($post->ID).'</a></p>';
				$result['html'] .= '<div class="price">'.$product->get_price_html().'</div>';
				$result['html'] .= '</li>';

				$i++;
			}
		}


		if ( ( ! empty( $regular_products ) || ! empty( $variations ) ) && etheme_get_option('search_by_sku') ) {

			$products = array_merge( $variations, $regular_products );

			$arrayID = array();
			foreach ( $products as $object ) {
				array_push( $arrayID, $object->post_id );
			}
			$arrayID = array_unique( $arrayID );

			$newObjects = array();
			foreach ( $arrayID as $id ) {
				foreach ( $products as $object ) {
					if ( $object->post_id == $id ) {
						array_push($newObjects, $object);
						break;
					}
				}
			}

			foreach ( $newObjects as $product ) {
				if ( $i >= $to )  break;

				setup_postdata( $product );
				$_product = wc_get_product( $product->post_id );

				$result['html'] .= '<li>';
				$result['html'] .= '<a href="'.get_the_permalink($product->post_id).'" title="'.get_the_title($product->post_id).'" class="product-list-image">';
				$result['html'] .= ( get_the_post_thumbnail( $product->post_id ) ) ? get_the_post_thumbnail( $product->post_id ) : wc_placeholder_img( $size = 'shop_thumbnail' );
				$result['html'] .='</a>';
				$result['html'] .= '<p class="product-title"><a href="'.get_the_permalink($product->post_id).'" title="'.get_the_title($product->post_id).'">'.get_the_title($product->post_id).'</a></p>';
				$result['html'] .= '<div class="price">'.$_product->get_price_html().'</div>';
				$result['html'] .= '</li>';

				$i++;
			}
		}

		wp_reset_postdata();
		$result['html'] .= '</ul></div>';

		// ! Get posts results
		$args = array(
			's'                   => $s,
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => $to,
		);

		if ( etheme_get_option( 'search_ajax_page' ) ) {
			$args['post_type'] = array( 'post', 'page' );
		}

		if ( $_REQUEST['cat'] && ! etheme_get_option( 'search_ajax_product' ) ) $args['category_name'] = $_REQUEST['cat'];

		$posts = ( etheme_get_option( 'search_ajax_post' ) ) ? get_posts( $args ) : '' ;

		if ( !empty( $posts ) ) {
			ob_start();
			foreach ( $posts as $post ) {
				?>
				<li>
					<a href="<?php echo get_the_permalink( $post->ID ); ?>" class="post-list-image"><?php echo get_the_post_thumbnail( $post->ID );?></a>
					<p class="post-title"><a href="<?php echo get_the_permalink( $post->ID ); ?>"><?php echo get_the_title( $post->ID ) ?></a></p>
					<span class="post-date"><?php echo get_the_date( '',$post->ID ); ?></span>
				</li>

				<?php
			}

			$result['status'] = 'success';
			$result['html'] .= '<div class="posts-ajax-list">';
			$result['html'] .= '<h3 class="search-results-title">' . esc_html__('Posts found', 'xstore') . '<a href="' . esc_url( home_url() ) . '/?s='. $s .'&post_type=post">' . esc_html__('View all', 'xstore' ) . '</a></h3>';
			$result['html'] .= '<ul>' . ob_get_clean() . '</ul>';
			$result['html'] .= '</div>';
		}
		wp_reset_postdata();

		if ( empty( $products ) && empty( $posts ) && empty( $regular_products ) && empty( $variations ) ) {
			$result['status'] = 'error';
			$result['html'] = '<div class="empty-category-block">';
			$result['html'] .= '<h3>' . esc_html__( 'No results were found', 'xstore' ) . '</h3>';
			$result['html'] .= '<p class="not-found-info">' . esc_html__( 'We invite you to get acquainted with an assortment of our site. Surely you can find something for yourself!', 'xstore' ). '</p>';
			$result['html'] .= '</div>';
		}

		wp_reset_postdata();

	}

	echo json_encode($result);
	die();
}
