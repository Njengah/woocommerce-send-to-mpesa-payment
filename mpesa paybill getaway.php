<?php
/*
*Plugin Name: Mpesa PayBill Payment 
*Description: Display Mpesa Paybill on Woocommerce 
*Author: Joe Njenga
*Author URI: http://njengah.com
*Version:		1.0.1

*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Mpesa Paybill Payment Gateway.
 *
 */
add_action('plugins_loaded', 'init_send_to_mpesa_gateway_class');  
function init_send_to_mpesa_gateway_class()
{

    class WC_Gateway_Send_To_Mpesa extends WC_Payment_Gateway
    {

        public $domain;

        /**
         * Constructor for the gateway.
         */
        public function __construct()
        {

            $this->domain = 'send_to_mpesa_payment';

            $this->id                 = 'send_to_mpesa_';
            $this->icon               = apply_filters('woocommerce_send_to_mpesa_gateway_icon', '');
            $this->has_fields         = false;
            $this->method_title       = __('Send to Mpesa', $this->domain);
            $this->method_description = __('Allows customers to send payments to Mpesa phone number Example 0722 XXX XXX.', $this->domain);

            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables
            $this->title        = $this->get_option('title');
            $this->description  = $this->get_option('description');
            $this->mpesa_name   = $this->get_option('mpesa_name');
            $this->instructions = $this->get_option('instructions', $this->description);
            $this->order_status = $this->get_option('order_status', 'completed');

            // Actions
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_thankyou_send_to_mpesa_', array($this, 'thankyou_page'));

            // Customer Emails
            add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
        }

        /**
         * Initialise Gateway Settings Form Fields.
         */
        public function init_form_fields()
        {

            $this->form_fields = array(
                'enabled' => array(
                    'title'   => __('Enable or Disable', $this->domain),
                    'type'    => 'checkbox',
                    'label'   => __('Enable Send Mpesa Payment', $this->domain),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title'       => __('Payment Title', $this->domain),
                    'type'        => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', $this->domain),
                    'default'     => __('Send to Mpesa', $this->domain),
                    'desc_tip'    => true,
                ),
                'order_status' => array(
                    'title'       => __('Order Status', $this->domain),
                    'type'        => 'select',
                    'class'       => 'wc-enhanced-select',
                    'description' => __('Choose whether status you wish after checkout.', $this->domain),
                    'default'     => 'wc-completed',
                    'desc_tip'    => true,
                    'options'     => wc_get_order_statuses()
                ),
                'description' => array(
                    'title'       => __('Description', $this->domain),
                    'type'        => 'textarea',
                    'description' => __('Payment method description that the customer will see on your checkout.', $this->domain),
                    'default'     => __('Pay with Lipa na Mpesa', $this->domain),
                    'desc_tip'    => true,
                ),
                'mpesa_name' => array(
                    'title'       => __('Mpesa Name', $this->domain),
                    'type'        => 'text',
                    'description' => __('Payment name that the customer will see on the mobile message to confirm.', $this->domain),
                    //'default'     => __('Example: Joe Njenga', $this->domain),
                    'desc_tip'    => true,
                ),

                'instructions' => array(
                    'title'       => __('Instructions', $this->domain),
                    'type'        => 'textarea',
                    'description' => __('Instructions that will be added to the thank you page and emails.', $this->domain),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
            );
        }

        /**
         * Output for the order received page.
         */
        public function thankyou_page()
        {
            if ($this->instructions)
                echo wpautop(wptexturize($this->instructions));
        }

        /**
         * Add content to the WC emails.
         *
         * @access public
         * @param WC_Order $order
         * @param bool $sent_to_admin
         * @param bool $plain_text
         */
        public function email_instructions($order, $sent_to_admin, $plain_text = false)
        {
            if ($this->instructions && !$sent_to_admin && 'send_to_mpesa_' === $order->payment_method && $order->has_status('on-hold')) {
                echo wpautop(wptexturize($this->instructions)) . PHP_EOL;
            }
        }

        public function payment_fields()
        {

            if ($description = $this->get_description()) {
                echo wpautop(wptexturize($description));
            }
           if (isset($this->mpesa_name)) {
                echo  wpautop(wptexturize("Mpesa Name : " . $this->mpesa_name));
           }

?>
<!--- This will go to frontend views --->
            <div id="send_to_mpesa_confirmation_details">

                <p class="form-row form-row-wide">

                    <p class="send-mpesa-confirmation-title">
                         Confirm Payment Details 
                    </p>

                    <p class="form-row form-row-wide">
                        <label for="customer" class=""><?php _e('Mpesa Payment Name', $this->domain); ?></label>
                        <input type="text" class="mpesa-confirm-input" name="customer" id="customer" placeholder="Enter your Mpesa Name" value="">
                    </p>
                   
                    <p class="form-row form-row-wide">
                        <label for="mobile" class=""><?php _e('Mobile Phone Number', $this->domain); ?></label>
                        <input type="text" class="mpesa-confirm-input" name="mobile" id="mobile" placeholder="Enter your mobile number" value="">
                    </p>

                    <p class="form-row form-row-wide">
                        <label for="transaction" class=""><?php _e('Mpesa Transaction Code', $this->domain); ?></label>
                        <input type="text" class="mpesa-confirm-input" name="transaction" id="transaction" placeholder="Enter the transaction code" value="">
                    </p>
                </p>
            </div>
<?php
        }

        /**
         * Process the payment and return the result.
         *
         * @param int $order_id
         * @return array
         */
        public function process_payment($order_id)
        {

            $order = wc_get_order($order_id);

            $status = 'wc-' === substr($this->order_status, 0, 3) ? substr($this->order_status, 3) : $this->order_status;

            // Set order status
            $order->update_status($status, __('Checkout with Mpesa payment. ', $this->domain));

            // Reduce stock levels
            $order->reduce_order_stock();

            // Remove cart
            WC()->cart->empty_cart();

            // Return thankyou redirect
            return array(
                'result'    => 'success',
                'redirect'  => $this->get_return_url($order)
            );
        }
    }
}

add_filter('woocommerce_payment_gateways', 'add_send_to_mpesa_gateway_class');
function add_send_to_mpesa_gateway_class($methods)
{
    $methods[] = 'WC_Gateway_Send_To_Mpesa';
    return $methods;
}

add_action('woocommerce_checkout_process', 'process_send_to_mpesa_payment');
function process_send_to_mpesa_payment()
{

    if ($_POST['payment_method'] != 'send_to_mpesa_')
        return;

    if (!isset($_POST['mobile']) || empty($_POST['mobile']))
        wc_add_notice(__('Please add your mobile number', $this->domain), 'error');


    if (!isset($_POST['transaction']) || empty($_POST['transaction']))
        wc_add_notice(__('Please add your Mpesa transaction code', $this->domain), 'error');
}

/**
 * Update the order meta with field value
 */
add_action('woocommerce_checkout_update_order_meta', 'send_to_mpesa_payment_update_order_meta');
function send_to_mpesa_payment_update_order_meta($order_id)
{

    if ($_POST['payment_method'] != 'send_to_mpesa_')
        return;

     echo "<pre>";
     print_r($_POST);
     echo "</pre>";
    // exit();

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

    $mobile = get_post_meta($order->id, 'mobile', true);
    $transaction = get_post_meta($order->id, 'transaction', true);

    echo '<p><strong>' . __('Mobile Number') . ':</strong> ' . $mobile . '</p>';
    echo '<p><strong>' . __('Mpesa Transaction ID') . ':</strong> ' . $transaction . '</p>';
}





/** Add Custom Icon For Cash On Delivery
**/ 
function send_to_mpesa_gateway_icon( $gateways ) {
    if ( isset( $gateways['send_to_mpesa_'] ) ) {
        $gateways['send_to_mpesa_']->icon = plugins_url( 'img/mpesa.png', __FILE__ );
    }

    return $gateways;
}

add_filter( 'woocommerce_available_payment_gateways', 'send_to_mpesa_gateway_icon' );