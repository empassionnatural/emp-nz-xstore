<?php

/**
 * Created by PhpStorm.
 * User: web
 * Date: 8/27/2018
 * Time: 11:38 AM
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	class EMPDEV_WC_Meta_Option {

		public function __construct() {

			add_action( 'woocommerce_product_options_advanced', array( $this, 'empdev_woocommerce_product_options_advanced' ) );

			add_action( 'woocommerce_process_product_meta', array( $this, 'empdev_woocommerce_advance_option_save_product' ) );
		}

		public function empdev_woocommerce_product_options_advanced() {

			echo '<div class="options_group">';

			woocommerce_wp_checkbox(
				array(
					'id' => '_empdev_exclude_related_posts',
					'label' => __( 'Hide in related products', 'woocommerce' ),
					'placeholder' => '',
					'desc_tip' => 'true',
					'description' => __( 'Check this option to exclude in related products.', 'woocommerce' )
				)

			);

			woocommerce_wp_checkbox(
				array(
					'id' => '_empdev_purchase_one_at_time',
					'label' => __( 'Enable purchase one at a time', 'woocommerce' ),
					'placeholder' => '',
					'desc_tip' => 'true',
					'description' => __( 'This will add to the list of products that can\'t be added in the cart at the same time.', 'woocommerce' )
				)

			);

			woocommerce_wp_text_input(
				array(
					'id'          => '_empdev_purchase_product_title_message',
					'label'       => __( 'Product tile to display error message.', 'wmamc-cart-limit' ),
					'placeholder' => ''
				)
			);

			woocommerce_wp_checkbox(
				array(
					'id' => '_empdev_limit_new_customers',
					'label' => __( 'Enable new customers only', 'woocommerce' ),
					'placeholder' => '',
					'desc_tip' => 'true',
					'description' => __( 'Only new customers can purchase this product.', 'woocommerce' )
				)

			);

			woocommerce_wp_text_input(
				array(
					'id'          => '_empdev_limit_new_customers_start_date',
					'label'       => __( 'Enter start date to restrict new customers limit.', 'woocommerce' ),
					'placeholder' => ''
				)
			);

			echo '</div>';

		}

		public function empdev_woocommerce_advance_option_save_product( $post_id ) {

			$new_customers_val = trim( get_post_meta( $post_id, '_empdev_limit_new_customers', true ) );

			$new_customers_val_update = $_POST['_empdev_limit_new_customers'];

			if ( $new_customers_val != $new_customers_val_update ) {

				update_post_meta( $post_id, '_empdev_limit_new_customers', $new_customers_val_update );

			}

			$start_date_val = trim( get_post_meta( $post_id, '_empdev_limit_new_customers_start_date', true ) );
			$start_date_val_update = sanitize_text_field( $_POST['_empdev_limit_new_customers_start_date'] );


			if ( $start_date_val != $start_date_val_update ) {

				update_post_meta( $post_id, '_empdev_limit_new_customers_start_date', $start_date_val_update );

			}

			$meta_related = trim( get_post_meta( $post_id, '_empdev_exclude_related_posts', true ) );
			$meta_related_new = $_POST['_empdev_exclude_related_posts'];
			//delete_option( 'empdev_exclude_related_posts');
			if ( $meta_related != $meta_related_new ) {

				update_post_meta( $post_id, '_empdev_exclude_related_posts', $meta_related_new );

				$get_related_ids = get_option( 'empdev_exclude_related_posts', false );

				if ( ! $get_related_ids ) {

					update_option( 'empdev_exclude_related_posts', array($post_id) );

				} else {

					$check_related_ids = in_array( $post_id, $get_related_ids );
					$new_related_ids = array();

					if ( $check_related_ids ) {

						$i = 0;
						foreach ( $new_related_ids as $pid ) {
							if ( $pid == $post_id ) {
								unset( $new_related_ids[ $i ] );
							} else {
								$new_related_ids[] = $pid;
							}
							$i ++;

						}
						update_option( 'empdev_exclude_related_posts', $new_related_ids );

					} else {
						//array_push($get_product_ids, $post_id)
						array_push($get_related_ids, $post_id);

						update_option( 'empdev_exclude_related_posts', $get_related_ids );

					}
				}

			}

			$meta_purchase = trim( get_post_meta( $post_id, '_empdev_purchase_one_at_time', true ) );
			$meta_purchase_new = $_POST['_empdev_purchase_one_at_time'];

			$meta_purchase_title = trim( get_post_meta( $post_id, '_empdev_purchase_product_title_message', true ) );
			$meta_purchase_title_new = sanitize_text_field( $_POST['_empdev_purchase_product_title_message'] );

			//	delete_option('empdev_purchase_one_at_time');

			//	delete_post_meta($post_id, 'empdev_purchase_one_at_time');

			if ( $meta_purchase != $meta_purchase_new ) {

				update_post_meta( $post_id, '_empdev_purchase_one_at_time', $meta_purchase_new );

				$product_ids     = array();
				$get_product_ids = get_option( 'empdev_purchase_one_at_time', false );

				if ( ! $get_product_ids ) {

					update_option( 'empdev_purchase_one_at_time', array($post_id) );

				} else {

					$check_product_ids = in_array( $post_id, $get_product_ids );
					$new_product_ids = array();

					if ( $check_product_ids ) {

						$i = 0;
						foreach ( $get_product_ids as $pid ) {
							if ( $pid == $post_id ) {
								unset( $get_product_ids[ $i ] );
							} else {
								$new_product_ids[] = $pid;
							}
							$i ++;

						}
						update_option( 'empdev_purchase_one_at_time', $new_product_ids );

					} else {
						//array_push($get_product_ids, $post_id)
						array_push($get_product_ids, $post_id);

						update_option( 'empdev_purchase_one_at_time', $get_product_ids );

					}
				}

			}

			if ( $meta_purchase_title != $meta_purchase_title_new ) {

				update_post_meta( $post_id, '_empdev_purchase_product_title_message', $meta_purchase_title_new );

			}

		}
	}
	new EMPDEV_WC_Meta_Option();
}