<?php
/**
 * Cart Page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

wc_print_notices();

global $product, $woocommerce_loop;

$addon_products_ids = get_option( 'empdev_enable_addon_checkout', false );

$exclude_product_addon_ids = get_option( 'empdev_enable_addon_checkout_hide', false );

$filter_addon_product_ids = $addon_products_ids;

if( $exclude_product_addon_ids ){
	$filter_addon_product_ids = array_diff( $addon_products_ids, $exclude_product_addon_ids );
}

if ( sizeof( $addon_products_ids ) == 0 || ! $addon_products_ids ) return;

echo '<div class="rp-header related_prod_container">';

echo '<h2 class="products-title"><span>' . esc_html__( 'Before you go, grab a Super Special!', 'xstore' ) . '</span></h2>';

$args = array(
    'post_type'           => 'product',
    'ignore_sticky_posts' => 1,
    'no_found_rows'       => 1,
    'posts_per_page'      => 4,
    'orderby'             => 'ID',
    'post__in'            => $filter_addon_product_ids,
);

$slider_args = array(
    'slider_autoplay' => false,
    'slider_speed' => 0,
    'large' => 4,
    'notebook' => 4,
    'tablet_land' => 3,
    'tablet_portrait' => 2,
);

etheme_create_slider( $args, $slider_args );

echo '</div>';

wp_reset_postdata();

do_action( 'woocommerce_before_cart' );

?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">

    <?php do_action( 'woocommerce_before_cart_table' ); ?>
    <div class="table-responsive">
        <table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
            <thead>
            <tr>
                <th class="product-name">&nbsp;</th>
                <th class="product-details"><?php esc_html_e( 'Product', 'xstore' ); ?></th>
                <th class="product-price"><?php esc_html_e( 'Price', 'xstore' ); ?></th>
                <th class="product-quantity"><?php esc_html_e( 'Quantity', 'xstore' ); ?></th>
                <th class="product-subtotal"><?php esc_html_e( 'Total', 'xstore' ); ?></th>
                <th class="product-remove">&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            <?php do_action( 'woocommerce_before_cart_contents' ); ?>

            <?php
            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                $product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                    $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                    ?>
                    <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">


                        <td class="product-name" data-title="<?php esc_attr_e( 'Product', 'xstore' ); ?>">
                            <div class="product-thumbnail">
                                <?php
                                $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image('medium'), $cart_item, $cart_item_key );

                                if ( ! $_product->is_visible() || ! $product_permalink)
                                    echo $thumbnail;
                                else
                                    printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );

                                ?>
                            </div>
                        </td>
                        <td class="product-details">
                            <div class="product-thumbnail-mobile">
                                <?php
                                $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image('medium'), $cart_item, $cart_item_key );

                                if ( ! $_product->is_visible() || ! $product_permalink)
                                    echo $thumbnail;
                                else
                                    printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );

                                ?>
                            </div>
                            <div class="cart-item-details">
                                <?php
                                if ( ! $_product->is_visible() || ! $product_permalink  )
                                    echo apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
                                else
                                    echo apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ) , $_product->get_name() ), $cart_item, $cart_item_key );

                                // Meta data
                                echo wc_get_formatted_cart_item_data( $cart_item );

                                // Backorder notification
                                if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) )
                                    echo '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'xstore' ) . '</p>';
                                ?>
                                <span class="mobile-price">
                            	<?php
                                echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                                ?>
                            </span>
                                <?php
                                //display in stock product quantity
                                $stock_available = $_product->get_availability();
                                $availability = $stock_available['availability'];
                                $availability = preg_replace( '/(\d+)/', '<span class="stock-count">($1)</span>', $availability );
                                echo '<span class="stock '. esc_attr( $stock_available['class'] ) .' "> '. wp_kses_post( $availability ).' </span>';
                                ?>
                            </div>
                        </td>

                        <td class="product-price" data-title="<?php esc_attr_e( 'Price', 'xstore' ); ?>">
                            <?php
                            echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                            ?>
                        </td>

                        <td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'xstore' ); ?>">
                            <?php
                            if ( $_product->is_sold_individually() ) {
                                $product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
                            } else {
                                $product_quantity = woocommerce_quantity_input( array(
                                    'input_name'  => "cart[{$cart_item_key}][qty]",
                                    'input_value' => $cart_item['quantity'],
                                    'max_value'   => $_product->get_max_purchase_quantity(),
                                    'min_value'   => '0',
                                    'product_name'  => $_product->get_name(),
                                ), $_product, false );
                            }

                            echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );

                            echo apply_filters( 'woocommerce_cart_item_remove_link_mobile', sprintf( '<a href="%s" class="remove-item-mobile" title="%s">Remove</a>', esc_url( wc_get_cart_remove_url( $cart_item_key ) ), __( 'Remove this item', 'xstore' ) ), $cart_item_key );
                            ?>



                        </td>

                        <td class="product-subtotal" data-title="<?php esc_attr_e( 'Total', 'xstore' ); ?>">
                            <?php
                            echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
                            ?>
                        </td>
                        <td class="product-remove">
                            <?php
                            echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf( '<a href="%s" class="btn remove-item" title="%s">X</a>', esc_url( wc_get_cart_remove_url( $cart_item_key ) ), __( 'Remove this item', 'xstore' ) ), $cart_item_key );
                            ?>
                        </td>
                    </tr>
                    <?php
                }
            }

            do_action( 'woocommerce_cart_contents' );
            ?>

            <?php do_action( 'woocommerce_after_cart_contents' ); ?>
            </tbody>
        </table>
    </div>

    <div class="actions">
        <button type="submit" class="btn gray" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'xstore' ); ?>"><?php esc_html_e( 'Update cart', 'xstore' ); ?></button>
        <input type="submit" class="checkout-button btn big alt wc-forward" name="proceed" value="<?php esc_html_e( 'Proceed to Checkout', 'xstore' ); ?>" />
        <?php wp_nonce_field( 'woocommerce-cart' ); ?>
        <?php do_action( 'woocommerce_cart_actions' ); ?>
    </div>

    <?php do_action( 'woocommerce_after_cart_table' ); ?>

</form>

<div class="row cart-bottom">
    <?php $cols = (is_active_sidebar( 'cart-area' )) ? 4 : 6; ?>

    <?php if (is_active_sidebar( 'cart-area' )): ?>
        <div class="col-md-4">
            <?php dynamic_sidebar( 'cart-area' ); ?>
        </div>
    <?php endif ?>

    <?php if ( wc_coupons_enabled() ) { ?>
        <div class="col-md-<?php echo esc_attr( $cols ); ?>">
            <h3 class="block-title"><?php esc_html_e( 'Have a coupon?', 'xstore' ); ?> <span class="label"><?php esc_html_e('Promotion', 'xstore'); ?></span></h3>
            <form class="checkout_coupon" method="post">
                <div class="coupon">

                    <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_html_e( 'Coupon code', 'xstore' ); ?>" />
                    <input type="submit" class="btn" name="apply_coupon" value="<?php esc_html_e( 'Apply Coupon', 'xstore' ); ?>" />

                    <?php do_action('woocommerce_cart_coupon'); ?>

                </div>
            </form>
        </div>
    <?php } ?>

    <div class="col-md-<?php echo esc_attr( $cols ); ?> pull-right">
        <div class="row">
            <div class="col-xs-12">
                <div class="bag-total-table">
                    <div class="cart-collaterals">
                        <?php do_action( 'woocommerce_cart_collaterals' ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php woocommerce_cross_sell_display(); ?>

<?php do_action( 'woocommerce_after_cart' ); ?>

<?php

/*echo '<div class="rp-footer related_prod_container">';

echo '<h2 class="products-title"><span>' . esc_html__( 'Before you go, grab a Super Special!', 'xstore' ) . '</span></h2>';

etheme_create_slider( $args, $slider_args );

echo '</div>';*/

wp_reset_postdata();
?>
