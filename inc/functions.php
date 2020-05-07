<?php 

/**
 * Enqueue both scripts and styles.
 */

add_action( 'wp_enqueue_scripts', 'woo_send_mpesa_payment_styles_scripts' );

function woo_send_mpesa_payment_styles_scripts() {

    wp_enqueue_style( "woo-send-mpesa-payment_styles", plugin_dir_url( __DIR__ ) . '/assets/css/styles.css' );
    wp_enqueue_script("woo-send-mpesa-payment_scripts", plugin_dir_url( __DIR__ ). '/assets/js/scripts.js', array('jquery'), false, false );

}

/**
 *  Intialize the Send to Mpesa Payment Gateway 
 */

add_filter('woocommerce_payment_gateways', 'add_send_to_mpesa_gateway_class');

function add_send_to_mpesa_gateway_class($methods)
{
    $methods[] = 'WC_Gateway_Send_To_Mpesa';
    return $methods;
}

/**
 *  Validate the Send to Mpesa Payment Gateway Fields 
 */

add_action('woocommerce_after_checkout_validation', 'process_send_to_mpesa_payment');

function process_send_to_mpesa_payment()
{
    if ($_POST['payment_method'] != 'send_to_mpesa_'){
        return;
    }
    if (!isset($_POST['customer']) || empty($_POST['customer'])){
       wc_add_notice('Please add your Mpesa name', 'error');
    }
    if (!isset($_POST['mobile']) || empty($_POST['mobile'])){
        wc_add_notice('Please add your mobile number', 'error');
    }
    if (!isset($_POST['transaction']) || empty($_POST['transaction'])){
        wc_add_notice('Please add your Mpesa transaction code', 'error');
	
    }
}

/**
 * Update the order meta with field value
 */
add_action('woocommerce_checkout_update_order_meta', 'send_to_mpesa_payment_update_order_meta');

function send_to_mpesa_payment_update_order_meta($order_id)
{

    if ($_POST['payment_method'] != 'send_to_mpesa_')
        return;

    update_post_meta($order_id, 'customer ', $_POST['customer']);
    update_post_meta($order_id, 'mobile', $_POST['mobile']);
    update_post_meta($order_id, 'transaction', $_POST['transaction']);
}

/**
 * Display field value on the order edit page
 */
add_action('woocommerce_admin_order_data_after_billing_address', 'send_to_mpesa_checkout_field_display_admin_order_meta', 10, 1);

function send_to_mpesa_checkout_field_display_admin_order_meta($order)
{
    $method = get_post_meta($order->id, '_payment_method', true);
    if ($method != 'send_to_mpesa_')
        return;

    $customer = get_post_meta($order->id, 'customer', true);
    $mobile = get_post_meta($order->id, 'mobile', true);
    $transaction = get_post_meta($order->id, 'transaction', true);

    echo '<p><strong>' . __('Mpesa Customer Name') . ':</strong> ' . $customer . '</p>';
    echo '<p><strong>' . __('Mobile Number') . ':</strong> ' . $mobile . '</p>';
    echo '<p><strong>' . __('Mpesa Transaction Code') . ':</strong> ' . $transaction . '</p>';
}

/** 
 * Add custom icon to the Send to Mpesa Payment Gateway 
 */ 
add_filter( 'woocommerce_available_payment_gateways', 'send_to_mpesa_gateway_icon' );

function send_to_mpesa_gateway_icon( $gateways ) {
    if ( isset( $gateways['send_to_mpesa_'] ) ) {
        $gateways['send_to_mpesa_']->icon = plugins_url( '/assets/img/mpesa.png', __DIR__ );
    }

    return $gateways;
}

