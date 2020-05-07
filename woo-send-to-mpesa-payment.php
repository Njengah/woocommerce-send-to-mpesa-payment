<?php
/*
 * Plugin Name:       WooCommerce Send to Mpesa Payment
 * Plugin URI:        https://njengah.com/plugins/
 * Description:       This is a simple Mpesa WooCommerce payment gateway that allows customers to send the shop owner the payment on mobile phone number. Its useful for those vendors without the Safaricom Paybill or Till Number. 
 * Version:           1.0.0
 * Author:            Joe Njenga
 * Author URI:        https://njengah.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       send-to-mpesa-payment
 * Domain Path:       /languages
 * 
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Send to Mpesa Payment Gateway.
 */
add_action('plugins_loaded', 'init_send_to_mpesa_gateway_class');  

function init_send_to_mpesa_gateway_class()
{

    if (class_exists('WC_Payment_Gateway')) {

        /**
         * WooCommerce Send to Mpesa Payment Gateway Main Class 
         */
        class WC_Gateway_Send_To_Mpesa extends WC_Payment_Gateway
        {

            public $domain;

            /**
             * Constructor for the gateway.
             */
            public function __construct()
            {

                $this->domain = 'send-to-mpesa-payment';

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
                        'default'     => __('Pay by sending to Mpesa mobile number', $this->domain),
                        'desc_tip'    => true,
                    ),
                    'mpesa_name' => array(
                        'title'       => __('Mpesa Recipient Name', $this->domain),
                        'type'        => 'text',
                        'description' => __('Payment name that the customer will see on the mobile message to confirm.', $this->domain),
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
                 include __DIR__ . '/views/frontend.php';       
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
                $order->update_status($status, __('Checkout with Send to Mpesa Payment. ', $this->domain));

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

    }else{

        // If woocommerce is not active show the admin notice

        add_action( 'admin_notices', 'woo_send_mpesa_payment_install_admin_notice' );
   
    }

}

/**
 * Admin notice for WooCommerce activation requirement 
 */ 
 
function woo_send_mpesa_payment_install_admin_notice(){
	 
    echo '<div class="notice notice-error">' ."<br>";
    echo  '<p>'.  _e( ' <strong> WooCommerce Send to Mpesa Payment Gateway</strong> plugin requires active WooCommerce Installation!', 'send-to-mpesa-payment' ).'</p>';
    echo '</div>';
   
}

/**
 *  Include the helper functions 
 */

require plugin_dir_path(__FILE__) . 'inc/functions.php';



