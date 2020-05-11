<?php 
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://njengah.com
 * @since      1.0.0
 *
 * @package    WooCommerce_Send_To_Mpesa_Payment
 * @subpackage WooCommerce_Send_To_Mpesa_Payment/public
 * @author     Joe Njenga <plugins@njengah.com>
 */


if ($description = $this->get_description()) {
    echo wpautop(wptexturize($description));
}
if (isset($this->mpesa_name)) {
    _e(' Mpesa Recipient Name :' . $this->mpesa_name, $this->domain);
}

?>

<!-- Checkout page payment details confirmation form -->

<div id="send_to_mpesa_confirmation_details">

    <p class="form-row form-row-wide">

        <p class="send-mpesa-confirmation-title">
                <?php _e('Confirm Mpesa Payment Details ', $this->domain); ?> 
        </p>

        <p class="form-row form-row-wide">
            <label for="mpesa_name" class=""><?php _e('Mpesa Payment Name', $this->domain); ?></label>
            <input type="text" class="mpesa-confirm-input" name="mpesa_name" id="mpesa_name" placeholder="Enter your Mpesa Name" value="" required>
        </p>
        
        <p class="form-row form-row-wide">
            <label for="mobile" class=""><?php _e('Mobile Phone Number', $this->domain); ?></label>
            <input type="text" class="mpesa-confirm-input" name="mobile" id="mobile" placeholder="Enter your mobile number" value="" required>
        </p>

        <p class="form-row form-row-wide">
            <label for="transaction" class=""><?php _e('Mpesa Transaction Code', $this->domain); ?></label>
            <input type="text" class="mpesa-confirm-input" name="transaction" id="transaction" placeholder="Enter the transaction code" value="" required>
        </p>
    </p>

</div>