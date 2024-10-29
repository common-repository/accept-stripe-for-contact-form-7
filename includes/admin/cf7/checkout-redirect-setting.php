<?php

namespace CF7PA_Pay_Addons\Admin\CF7;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
use CF7PA_Pay_Addons\Stripe\Stripe_Settings;
use CF7PA_Pay_Addons\Shared\Countries;
use CF7PA_Pay_Addons\Shared\Utils;
use CF7PA_Pay_Addons\Shared\Logger;
class Checkout_Redirect_Setting {
    // instance container
    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        if ( is_admin() ) {
            add_filter(
                'wpcf7_editor_panels',
                array($this, 'wpcf7_editor_panels'),
                10,
                1
            );
            // Save settings
            add_action(
                'wpcf7_save_contact_form',
                array($this, 'save_cf7_checkout_redirect_setting'),
                10,
                3
            );
        }
    }

    function save_cf7_checkout_redirect_setting( $contact_form, $data, $context ) {
        $form_id = $contact_form->id();
        if ( !isset( $_POST['save_checkout_redirect_setting_nonce'] ) || !wp_verify_nonce( $_POST['save_checkout_redirect_setting_nonce'], 'save_checkout_redirect_setting' ) ) {
            return;
        }
        $payment_settings = $_POST['cf7pacr'];
        function get_text_field_value(  $setting, $key, $default_value  ) {
            return sanitize_text_field( ( isset( $setting[$key] ) ? $setting[$key] : $default_value ) );
        }

        $settings = array(
            'enable'                           => ( isset( $payment_settings['enable'] ) ? 1 : 0 ),
            'enable_link'                      => ( isset( $payment_settings['enable_link'] ) ? 1 : 0 ),
            'stripe_link'                      => ( isset( $payment_settings['stripe_link'] ) ? esc_url_raw( $payment_settings['stripe_link'] ) : '' ),
            'payment_method_types'             => array_map( 'sanitize_text_field', $payment_settings['payment_method_types'] ),
            'payment_type'                     => sanitize_text_field( $payment_settings['payment_type'] ),
            'success_url'                      => ( isset( $payment_settings['success_url'] ) ? esc_url_raw( $payment_settings['success_url'] ) : '' ),
            'cancel_url'                       => ( isset( $payment_settings['cancel_url'] ) ? esc_url_raw( $payment_settings['cancel_url'] ) : '' ),
            'submit_type'                      => sanitize_text_field( $payment_settings['submit_type'] ),
            'automatic_tax'                    => get_text_field_value( $payment_settings, 'automatic_tax', 'no' ),
            'tax_behavior'                     => sanitize_text_field( $payment_settings['tax_behavior'] ),
            'phone_number_collection'          => get_text_field_value( $payment_settings, 'phone_number_collection', 'no' ),
            'terms_of_service'                 => get_text_field_value( $payment_settings, 'terms_of_service', 'no' ),
            'allow_promotion_codes'            => get_text_field_value( $payment_settings, 'allow_promotion_codes', 'no' ),
            'billing_address_collection'       => get_text_field_value( $payment_settings, 'billing_address_collection', 'no' ),
            'shipping_address_collection'      => array_map( 'sanitize_text_field', $payment_settings['shipping_address_collection'] ),
            'email_field'                      => ( isset( $payment_settings['email_field'] ) ? sanitize_text_field( $payment_settings['email_field'] ) : '' ),
            'onetime_currency'                 => ( isset( $payment_settings['onetime_currency'] ) ? sanitize_text_field( $payment_settings['onetime_currency'] ) : '' ),
            'onetime_amount_field'             => ( isset( $payment_settings['onetime_amount_field'] ) ? sanitize_text_field( $payment_settings['onetime_amount_field'] ) : '' ),
            'onetime_quantity_field'           => ( isset( $payment_settings['onetime_quantity_field'] ) ? sanitize_text_field( $payment_settings['onetime_quantity_field'] ) : '' ),
            'onetime_product_name_field'       => ( isset( $payment_settings['onetime_product_name_field'] ) ? sanitize_text_field( $payment_settings['onetime_product_name_field'] ) : '' ),
            'onetime_product_desc_field'       => ( isset( $payment_settings['onetime_product_desc_field'] ) ? sanitize_text_field( $payment_settings['onetime_product_desc_field'] ) : '' ),
            'sub_currency'                     => ( isset( $payment_settings['sub_currency'] ) ? sanitize_text_field( $payment_settings['sub_currency'] ) : '' ),
            'sub_amount_field'                 => ( isset( $payment_settings['sub_amount_field'] ) ? sanitize_text_field( $payment_settings['sub_amount_field'] ) : '' ),
            'sub_quantity_field'               => ( isset( $payment_settings['sub_quantity_field'] ) ? sanitize_text_field( $payment_settings['sub_quantity_field'] ) : '' ),
            'sub_product_name_field'           => ( isset( $payment_settings['sub_product_name_field'] ) ? sanitize_text_field( $payment_settings['sub_product_name_field'] ) : '' ),
            'sub_product_desc_field'           => ( isset( $payment_settings['sub_product_desc_field'] ) ? sanitize_text_field( $payment_settings['sub_product_desc_field'] ) : '' ),
            'sub_interval_count_field'         => ( isset( $payment_settings['sub_interval_count_field'] ) ? sanitize_text_field( $payment_settings['sub_interval_count_field'] ) : '' ),
            'sub_interval_field'               => ( isset( $payment_settings['sub_interval_field'] ) ? sanitize_text_field( $payment_settings['sub_interval_field'] ) : '' ),
            'payment_type_condition_field'     => ( isset( $payment_settings['payment_type_condition_field'] ) ? sanitize_text_field( $payment_settings['payment_type_condition_field'] ) : '' ),
            'payment_type_condition_operation' => ( isset( $payment_settings['payment_type_condition_operation'] ) ? sanitize_text_field( $payment_settings['payment_type_condition_operation'] ) : '' ),
            'payment_type_condition_value'     => ( isset( $payment_settings['payment_type_condition_value'] ) ? sanitize_text_field( $payment_settings['payment_type_condition_value'] ) : '' ),
        );
        // For new forms, we might need to save immediately after creation
        if ( empty( $form_id ) ) {
            add_action( 'wpcf7_after_create', function ( $contact_form ) use($settings) {
                update_post_meta( $contact_form->id(), 'cf7pa_checkout_redirect_setting', $settings );
            } );
        } else {
            update_post_meta( $form_id, 'cf7pa_checkout_redirect_setting', $settings );
        }
    }

    function wpcf7_editor_panels( $panels ) {
        $panels['cf7pacr-panel'] = array(
            'title'    => __( 'Stripe Checkout Redirection', 'contact-form-7-stripe-addon' ),
            'callback' => array($this, 'cf7_payment_settings_panel_html'),
        );
        return $panels;
    }

    function cf7_payment_settings_panel_html( $post ) {
        $form_id = $post->id();
        $settings = get_post_meta( $form_id, 'cf7pa_checkout_redirect_setting', true );
        $settings = wp_parse_args( $settings, array(
            'enable'                           => 0,
            'enable_link'                      => 0,
            'stripe_link'                      => '',
            'payment_type'                     => 'payment',
            'payment_method_types'             => ['automatic'],
            'submit_type'                      => 'auto',
            'success_url'                      => '',
            'cancel_url'                       => '',
            'billing_address_collection'       => 'yes',
            'allow_promotion_codes'            => 'yes',
            'automatic_tax'                    => 'yes',
            'tax_behavior'                     => 'exclusive',
            'phone_number_collection'          => 'no',
            'terms_of_service'                 => 'no',
            'shipping_address_collection'      => ['US'],
            'email_field'                      => '',
            'onetime_currency'                 => '',
            'onetime_amount_field'             => '19.9',
            'onetime_quantity_field'           => '1',
            'onetime_product_name_field'       => 'your product name',
            'onetime_product_desc_field'       => 'your product description',
            'sub_currency'                     => '',
            'sub_quantity_field'               => '1',
            'sub_amount_field'                 => '19.9',
            'sub_interval_count_field'         => '1',
            'sub_interval_field'               => 'month',
            'sub_product_name_field'           => 'your product name',
            'sub_product_desc_field'           => 'your product description',
            'payment_type_condition_field'     => '',
            'payment_type_condition_operation' => '',
            'payment_type_condition_value'     => '',
        ) );
        $form_fields = $post->scan_form_tags();
        $is_premium = cf7pa_fs()->can_use_premium_code();
        $default_currency = Stripe_Settings::get_setting( 'default_currency' );
        $payment_method_types = Utils::get_supported_payment_methods( $default_currency );
        $currencies = Utils::get_currencies_options();
        $countries = Countries::get_countries();
        ?>
		<div id="cf7pacr" class="cf7pa-form-table bg-white p-6 rounded-lg shadow-md">
			<div class="mb-8">
				<div class="space-y-4">
					<div class="flex items-center justify-between">
						<label for="cf7pacr[enable]" class="mr-4">Enable</label>
						<input type="checkbox" id="cf7pacr[enable]" name="cf7pacr[enable]" value="1" <?php 
        checked( $settings['enable'], 1 );
        ?> class="form-checkbox h-5 w-5 text-blue-600">
					</div>

					<div class="flex items-center justify-between">
						<label for="cf7pacr[enable]" class="mr-4">Enable Stripe Link</label>
						<input type="checkbox" id="cf7pacr[enable_link]" name="cf7pacr[enable_link]" value="1" <?php 
        checked( $settings['enable_link'], 1 );
        ?> class="form-checkbox h-5 w-5 text-blue-600">
					</div>

					<div id="link-section" class="hidden">
						<label for="cf7pacr[stripe_link]" class="block mb-2">Stripe Link</label>
						<input type="text" id="cf7pacr[stripe_link]" name="cf7pacr[stripe_link]" class="form-input mt-1 block w-full" value="<?php 
        echo esc_attr( $settings['stripe_link'] );
        ?>">
					</div>
				</div>
			</div>
			<div id="basic-section" class="mb-8">
				<h3 class="text-xl font-semibold mb-4">Basic Settings</h3>
				<div class="space-y-4">
					<div>
						<label for="cf7pacr[payment_method_types]" class="block mb-2">Payment Methods</label>
						<select id="cf7pacr[payment_method_types]" name="cf7pacr[payment_method_types][]" class="form-multiselect mt-1 block w-full" multiple>
							<?php 
        foreach ( $payment_method_types as $key => $label ) {
            echo sprintf(
                '<option value="%s" %s>%s</option>',
                esc_attr( $key ),
                selected( in_array( $key, $settings['payment_method_types'] ), true, false ),
                esc_html( $label )
            );
        }
        ?>
						</select>
					</div>

					<div>
						<label for="cf7pacr[success_url]" class="block mb-2">Success URL</label>
						<select id="cf7pacr[success_url]" name="cf7pacr[success_url]" class="form-select mt-1 block w-full">
							<option value="">Current form page</option>
							<?php 
        $pages_options = Utils::get_pages_options();
        foreach ( $pages_options as $url => $title ) {
            echo sprintf(
                '<option value="%s" %s>%s</option>',
                esc_url( $url ),
                selected( $settings['success_url'], $url, false ),
                esc_html( $title )
            );
        }
        ?>
						</select>
					</div>

					<div>
						<label for="cf7pacr[cancel_url]" class="block mb-2">Failed URL</label>
						<select id="cf7pacr[cancel_url]" name="cf7pacr[cancel_url]" class="form-select mt-1 block w-full">
							<option value="">Current form page</option>
							<?php 
        $pages_options = Utils::get_pages_options();
        foreach ( $pages_options as $url => $title ) {
            echo sprintf(
                '<option value="%s" %s>%s</option>',
                esc_url( $url ),
                selected( $settings['cancel_url'], $url, false ),
                esc_html( $title )
            );
        }
        ?>
						</select>
					</div>

					<div>
						<label for="cf7pacr[submit_type]" class="block mb-2">Submit button type</label>
						<select id="cf7pacr[submit_type]" name="cf7pacr[submit_type]" class="form-select mt-1 block w-full">
							<option value="auto"><?php 
        echo esc_html__( 'auto', 'elementor-pay-addons' );
        ?></option>
							<option value="pay"><?php 
        echo esc_html__( 'pay', 'elementor-pay-addons' );
        ?></option>
							<option value="book"><?php 
        echo esc_html__( 'book', 'elementor-pay-addons' );
        ?></option>
							<option value="donate"><?php 
        echo esc_html__( 'donate', 'elementor-pay-addons' );
        ?></option>
						</select>
					</div>

					<div class="flex items-center justify-between">
						<label for="cf7pacr[billing_address_collection]" class="mr-4"><?php 
        echo esc_html__( 'Billing address required', 'elementor-pay-addons' );
        ?></label>
						<input type="checkbox" id="cf7pacr[billing_address_collection]" name="cf7pacr[billing_address_collection]" value="yes" <?php 
        checked( $settings['billing_address_collection'], 'yes' );
        ?> class="form-checkbox h-5 w-5 text-blue-600">
					</div>

					<div class="flex items-center justify-between">
						<label for="cf7pacr[allow_promotion_codes]" class="mr-4"><?php 
        echo esc_html__( 'Enable promotion', 'elementor-pay-addons' );
        ?></label>
						<input type="checkbox" id="cf7pacr[allow_promotion_codes]" name="cf7pacr[allow_promotion_codes]" value="yes" <?php 
        checked( $settings['allow_promotion_codes'], 'yes' );
        ?> class="form-checkbox h-5 w-5 text-blue-600">
					</div>

					<div class="flex items-center justify-between">
						<label for="cf7pacr[automatic_tax]" class="mr-4"><?php 
        echo esc_html__( 'Enable automatic taxes', 'elementor-pay-addons' );
        ?></label>
						<input type="checkbox" id="cf7pacr[automatic_tax]" name="cf7pacr[automatic_tax]" value="yes" <?php 
        checked( $settings['automatic_tax'], 'yes' );
        ?> class="form-checkbox h-5 w-5 text-blue-600">
					</div>

					<div>
						<label for="cf7pacr[tax_behavior]" class="block mb-2"><?php 
        echo esc_html__( 'Tax Behaviors', 'elementor-pay-addons' );
        ?></label>
						<select id="cf7pacr[tax_behavior]" name="cf7pacr[tax_behavior]" class="form-select mt-1 block w-full">
							<option value="inclusive" <?php 
        selected( $settings['tax_behavior'], 'inclusive' );
        ?>>Inclusive</option>
							<option value="exclusive" <?php 
        selected( $settings['tax_behavior'], 'exclusive' );
        ?>>Exclusive</option>
						</select>
					</div>

					<div class="flex items-center justify-between">
						<label for="cf7pacr[phone_number_collection]" class="mr-4"><?php 
        echo esc_html__( 'Phone number required', 'elementor-pay-addons' );
        ?></label>
						<input type="checkbox" id="cf7pacr[phone_number_collection]" name="cf7pacr[phone_number_collection]" value="yes" <?php 
        checked( $settings['phone_number_collection'], 'yes' );
        ?> class="form-checkbox h-5 w-5 text-blue-600">
					</div>

					<div class="flex items-center justify-between">
						<label for="cf7pacr[terms_of_service]" class="mr-4"><?php 
        echo esc_html__( 'Enable terms of service', 'elementor-pay-addons' );
        ?></label>
						<input type="checkbox" id="cf7pacr[terms_of_service]" name="cf7pacr[terms_of_service]" value="yes" <?php 
        checked( $settings['terms_of_service'], 'yes' );
        ?> class="form-checkbox h-5 w-5 text-blue-600">
					</div>

					<div>
						<label for="cf7pacr[shipping_address_collection]" class="block mb-2"><?php 
        echo esc_html__( 'Shipping address countries', 'elementor-pay-addons' );
        ?></label>
						<select id="cf7pacr[shipping_address_collection]" name="cf7pacr[shipping_address_collection][]" class="form-multiselect mt-1 block w-full" multiple>
							<?php 
        foreach ( $countries as $key => $label ) {
            echo sprintf(
                '<option value="%s" %s>%s</option>',
                esc_attr( $key ),
                selected( in_array( $key, $settings['shipping_address_collection'] ), true, false ),
                esc_html( $label )
            );
        }
        ?>
						</select>
					</div>
				</div>
			</div>
			<div id="pricing-section">
				<h3 class="text-xl font-semibold mb-4">Pricing Settings</h3>
				<div class="space-y-4">
					<div>
						<label for="cf7pacr[email_field]" class="block mb-2">Customer Email Field</label>
						<input type="text" id="cf7pacr[email_field]" name="cf7pacr[email_field]" class="form-input mt-1 block w-full" value="<?php 
        echo esc_attr( $settings['email_field'] );
        ?>">
					</div>

					<div>
						<label for="cf7pacr[payment_type]" class="block mb-2">Payment Type</label>
						<select id="cf7pacr[payment_type]" name="cf7pacr[payment_type]" class="form-select mt-1 block w-full">
							<option value="payment" <?php 
        selected( $settings['payment_type'], 'payment' );
        ?>>One-time</option>
							<option <?php 
        echo ( $is_premium ? '' : 'disabled' );
        ?> value="subscription" <?php 
        selected( $settings['payment_type'], 'subscription' );
        ?>>Subscription (pro)</option>
							<option <?php 
        echo ( $is_premium ? '' : 'disabled' );
        ?> value="flex" <?php 
        selected( $settings['payment_type'], 'flex' );
        ?>>Flex (pro)</option>
						</select>
					</div>
					<?php 
        ?>

					<div class="mt-8">
						<nav class="payment-type-tabs" aria-label="Tabs">
							<a href="#" class="payment-type-tab" data-tab="onetime">One-time</a>
							<a href="#" class="payment-type-tab" data-tab="subscription">Subscription (pro)</a>
						</nav>

						<div id="onetime-tab" class="payment-content mt-4">
							<div class="space-y-4">
								<div>
									<label for="cf7pacr[onetime_currency]" class="block mb-2">Currency</label>
									<select id="cf7pacr[onetime_currency]" name="cf7pacr[onetime_currency]" class="form-select mt-1 block w-full">
										<?php 
        foreach ( $currencies as $key => $label ) {
            echo sprintf(
                '<option value="%s" %s>%s</option>',
                esc_attr( $key ),
                selected( $key, $settings['onetime_currency'], false ),
                esc_html( $label )
            );
        }
        ?>
									</select>
								</div>

								<div>
									<label for="cf7pacr[onetime_amount_field]" class="block mb-2">Amount Field</label>
									<input type="text" id="cf7pacr[onetime_amount_field]" name="cf7pacr[onetime_amount_field]" class="form-input mt-1 block w-full" value="<?php 
        echo esc_attr( $settings['onetime_amount_field'] );
        ?>">
								</div>

								<div>
									<label for="cf7pacr[onetime_quantity_field]" class="block mb-2">Quantity Field</label>
									<input type="text" id="cf7pacr[onetime_quantity_field]" name="cf7pacr[onetime_quantity_field]" class="form-input mt-1 block w-full" value="<?php 
        echo esc_attr( $settings['onetime_quantity_field'] );
        ?>">
								</div>

								<div>
									<label for="cf7pacr[onetime_product_name_field]" class="block mb-2">Product name</label>
									<input type="text" id="cf7pacr[onetime_product_name_field]" name="cf7pacr[onetime_product_name_field]" class="form-input mt-1 block w-full" value="<?php 
        echo esc_attr( $settings['onetime_product_name_field'] );
        ?>">
								</div>

								<div>
									<label for="cf7pacr[onetime_product_desc_field]" class="block mb-2">Product description</label>
									<input type="text" id="cf7pacr[onetime_product_desc_field]" name="cf7pacr[onetime_product_desc_field]" class="form-input mt-1 block w-full" value="<?php 
        echo esc_attr( $settings['onetime_product_desc_field'] );
        ?>">
								</div>
							</div>
						</div>

						<div id="subscription-tab" class="payment-content mt-4 hidden">
							<?php 
        cf7pa_upgrade_link();
        ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('.form-multiselect').select2();

				// Function to toggle visibility of payment_type_condition_value field
				function togglePaymentTypeConditionValue() {
					var operation = $('[id="cf7pacr[payment_type_condition_operation]"]').val();
					if (operation === 'equalto' || operation === 'notequalto') {
						$('[id="cf7pacr[payment_type_condition_value]"]').closest('div').show();
					} else {
						$('[id="cf7pacr[payment_type_condition_value]"]').closest('div').hide();
					}
				}

				// Initial call to set correct visibility on page load
				togglePaymentTypeConditionValue();

				// Add event listener for changes to the operation select
				$('[id="cf7pacr[payment_type_condition_operation]"]').on('change', function() {
					togglePaymentTypeConditionValue();
				});

				$('[id="cf7pacr[payment_type]"]').on('change', function(e) {
					var payment_type = $(this).val();
					if (payment_type === 'subscription') {
						$('#subscription-tab').show();
						$('#onetime-tab').hide();
						$('.payment-type-tab[data-tab="subscription"]').addClass('active');
						$('.payment-type-tab[data-tab="onetime"]').removeClass('active');
						$('#flex-fields').hide();
					} else if (payment_type === 'payment') {
						$('#onetime-tab').show();
						$('#subscription-tab').hide();
						$('.payment-type-tab[data-tab="onetime"]').addClass('active');
						$('.payment-type-tab[data-tab="subscription"]').removeClass('active');
						$('#flex-fields').hide();
					} else if (payment_type === 'flex') {
						$('#onetime-tab').show();
						$('#subscription-tab').hide();
						$('.payment-type-tab[data-tab="onetime"]').addClass('active');
						$('.payment-type-tab[data-tab="subscription"]').removeClass('active');
						$('#flex-fields').show();
					}
				});

				function toggleStripeLinkOption() {
					if ($('[id="cf7pacr[enable_link]"]').prop('checked')) {
						$('#link-section').show();
						$('#basic-section').hide();
						$('#pricing-section').hide();
					} else {
						$('#link-section').hide();
						$('#basic-section').show();
						$('#pricing-section').show();
					}
				}

				toggleStripeLinkOption();

				$('[id="cf7pacr[enable_link]"]').on('change', function(e) {
					toggleStripeLinkOption();
				});

				$('.payment-type-tab').on('click', function(e) {
					e.preventDefault();
					var tab = $(this).data('tab');
					$('.payment-type-tab').removeClass('active');
					$(this).addClass('active');
					$('.payment-content').hide();
					$('#' + tab + '-tab').show();
					if ($('[id="cf7pacr[payment_type]"]').val() !== 'flex') {
						$('[id="cf7pacr[payment_type]"]').val(tab === 'onetime' ? 'payment' : 'subscription').trigger('change');
					}
				});

				// Trigger change event on page load to set initial state
				$('[id="cf7pacr[payment_type]"]').trigger('change');
			});
		</script>
<?php 
        wp_nonce_field( 'save_checkout_redirect_setting', 'save_checkout_redirect_setting_nonce' );
    }

}
