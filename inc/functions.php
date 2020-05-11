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
 *  Validation functions (Mpesa full name, mpesa number and mpesa transaction code )
 */

 // Mpesa phone number  
function is_mpesa_phone_number($mpesa_phone_number)
{
    if (empty($mpesa_phone_number)) {
        return false;
    }
    if (strlen(trim($mpesa_phone_number)) > 10) {
        return false;
    }
    if (!preg_match('/^[0-9-+]$/', $mpesa_phone_number)) {
        return false;
    }
    return true;
}

// Mpesa full name 
function is_mpesa_full_name($mpesa_full_name)
{
    if (empty($mpesa_full_name)) {
        return false;
    }
    if (!preg_match('/^[a-zA-Z ]*$/', $mpesa_full_name)) {
        return false;
    }
    return true;
}

//Mpesa Transaction Code  

function is_mpesa_transaction_code($mpesa_transaction_code)
{
    if (empty($mpesa_transaction_code)) {
        return false;
    }
    if (!preg_match('/[^a-zA-Z0-9]/', $mpesa_transaction_code)) {
        return false;
    }
    return true;
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

    // Validate Mpesa full name  
    if (!isset($_POST['mpesa_name']) || empty($_POST['mpesa_name'])){

       wc_add_notice('Please add your Mpesa name', 'error');

    }elseif (!is_mpesa_full_name($_POST['mpesa_name'])){

        wc_add_notice('Please add the full name as it appears in your Mpesa transaction', 'error');
    }

    // Validate Mpesa number 
    
    if (!isset($_POST['mobile']) || empty($_POST['mobile'])){
    
        wc_add_notice('Please add your mobile number', 'error');

    }elseif (!is_mpesa_phone_number($_POST['mobile'])){

        wc_add_notice('Please add the mpesa number you paid with example 0722 XXX XXX without spaces', 'error');
    }

   // Validate Mpesa transaction code 

    if (!isset($_POST['transaction']) || empty($_POST['transaction'])){
        wc_add_notice('Please add your Mpesa transaction code', 'error');
	
    }elseif (!is_mpesa_transaction_code($_POST['transaction'])){

        wc_add_notice('Please add the mpesa transaction code that you recieved from this payment without spaces', 'error');
    }

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
 * Update the order meta with field value
 */
add_action('woocommerce_checkout_update_order_meta', 'send_to_mpesa_payment_update_order_meta');

function send_to_mpesa_payment_update_order_meta($order_id)
{

    if ($_POST['payment_method'] != 'send_to_mpesa_')
        return;

    $mpesa_full_name = isset( $_POST['mpesa_name'] ) ? $_POST['mpesa_name'] : '';
    $mobile_number   = isset( $_POST['mobile'] ) ? $_POST['mobile'] : '';
    $transaction_code= isset( $_POST['mobile'] ) ? $_POST['mobile'] : '';

    update_post_meta($order_id, 'mpesa_name', sanitize_text_field($mpesa_full_name) );
    update_post_meta($order_id, 'mobile', sanitize_text_field($mobile_number ));
    update_post_meta($order_id, 'transaction', sanitize_text_field($transaction_code));
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

    $mpesa_name   =  get_post_meta($order->id, 'mpesa_name', true);
    $mobile       =  get_post_meta($order->id, 'mobile', true);
    $transaction  =  get_post_meta($order->id, 'transaction', true);

    echo '<p><strong>' . __('Mpesa Name') . ':</strong> ' . $mpesa_name . '</p>';
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

