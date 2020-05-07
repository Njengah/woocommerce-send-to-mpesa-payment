# Woocommerce Send to Mpesa Payment
A simple WooCommerce plugin that allows customers to directly send money to your Mpesa business numbers for those users without the Till number of paybill access. 


# Details

= Contributors: Njengah
= Donate link: https://njengah.com
= Tags: woocommerce, mpesa, woocommerce mpesa payment gateway, mpesa woocommmerce payment 
= Requires at least: 4.3
= Tested up to: 5.4
= Stable tag: 5.4
= License: GPLv2 or later
= License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple Mpesa WooCommerce payment gateway that allows customers to send the shop owner the payment on mobile phone number. Its useful for those vendors without the Safaricom Paybill or Till Number.

# Description 

Most WooCommerce users who want to recieve payment via Mpesa do not have the PayBill or Till number. This plugin is designed to allow such users to recieve  payment from customers who want to send the payment to the business or personal phone number. The checkout provides the three important fields (customer name, customer mobile number 
and the Mpesa transaction code ) for confirmation of the payment.

This plugin does not have the API verification capabalities since at this time the Mpesa API does not support the ability to read data from Customer to Customer (C2C)though this is a future possiblity. 

# Installation 

This are the instructions on how to install the plugin and get it working.

1. Upload `woocommerce-send-to-mpesa-payment.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the WooCommerce Settings > Payment and set the default values for your Mpesa number and the name that customers see on the transation. 

# Frequently Asked Questions ==

= Does this plugin work with WooCommerce only ?  =

 Yes, this is a custom WooCommerce payment gateway that will not work without WooCommerce. 

= Does it support Mpesa Till and Paybill APIs =

No this a plugin for direct payments by sending money from one customer number to the shop owner number. 


# Changelog ==

= 1.0.0 =
Intial release 

