<?php

namespace CF7PA_Pay_Addons\Stripe;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

use CF7PA_Pay_Addons\Shared\Utils;
use CF7PA_Pay_Addons\Stripe\Stripe_API;

// Exit if accessed directly
// refer from https://github.com/woocommerce/woocommerce-gateway-stripe/blob/develop/includes/class-wc-stripe-helper.php

class Stripe_Helper {

	/**
	 * Checks Stripe minimum order value authorized per currency
	 */
	public static function get_minimum_amount( $currency ) {
		// Check order amount
		switch ( $currency ) {
			case 'USD':
			case 'CAD':
			case 'EUR':
			case 'CHF':
			case 'AUD':
			case 'SGD':
				$minimum_amount = 50;
				break;
			case 'GBP':
				$minimum_amount = 30;
				break;
			case 'DKK':
				$minimum_amount = 250;
				break;
			case 'NOK':
			case 'SEK':
				$minimum_amount = 300;
				break;
			case 'JPY':
				$minimum_amount = 5000;
				break;
			case 'MXN':
				$minimum_amount = 1000;
				break;
			case 'HKD':
				$minimum_amount = 400;
				break;
			default:
				$minimum_amount = 50;
				break;
		}

		return $minimum_amount;
	}

	/**
	 * Gets all the saved setting options from a specific method.
	 * If specific setting is passed, only return that.
	 *
	 * @since 4.0.0
	 * @version 4.0.0
	 * @param string $method The payment method to get the settings from.
	 * @param string $setting The name of the setting to get.
	 */
	public static function get_settings( $method = null, $setting = null ) {
		// $all_settings = null === $method ? get_option('woocommerce_stripe_settings', []) : get_option('woocommerce_stripe_' . $method . '_settings', []);

		// if (null === $setting) {
		//   return $all_settings;
		// }

		// return isset($all_settings[$setting]) ? $all_settings[$setting] : '';
	}

	/**
		* Gets the webhook URL for Stripe triggers. Used mainly for
		* asyncronous redirect payment methods in which statuses are
		* not immediately chargeable.
		*
		* @since 4.0.0
		* @version 4.0.0
		* @return string
		*/
	public static function get_webhook_url() {
		// return add_query_arg( 'wc-api', 'wc_stripe', trailingslashit( get_home_url() ) );
	}

	/**
		* Sanitize statement descriptor text.
		*
		* Stripe requires max of 22 characters and no special characters.
		*
		* @since 4.0.0
		* @param string $statement_descriptor Statement descriptor.
		* @return string $statement_descriptor Sanitized statement descriptor.
		*/
	public static function clean_statement_descriptor( $statement_descriptor = '' ) {
		$disallowed_characters = array( '<', '>', '\\', '*', '"', "'", '/', '(', ')', '{', '}' );

		// Strip any tags.
		$statement_descriptor = wp_strip_all_tags( $statement_descriptor );

		// Strip any HTML entities.
		// Props https://stackoverflow.com/questions/657643/how-to-remove-html-special-chars .
		$statement_descriptor = preg_replace( '/&#?[a-z0-9]{2,8};/i', '', $statement_descriptor );

		// Next, remove any remaining disallowed characters.
		$statement_descriptor = str_replace( $disallowed_characters, '', $statement_descriptor );

		// Trim any whitespace at the ends and limit to 22 characters.
		$statement_descriptor = substr( trim( $statement_descriptor ), 0, 22 );

		return $statement_descriptor;
	}

	public static function format_stripe_date( $seconds ) {
		// return date('Y-m-d H:i:s', $seconds);
		return date_i18n( 'F j, Y', $seconds );
	}

	public static function format_amount_with_symbol( $amount, $currency ) {
		$amount = intval( $amount ) / 100;
		if ( class_exists( 'NumberFormatter' ) ) {
			$formatter = new \NumberFormatter( get_locale(), \NumberFormatter::CURRENCY );
			return $formatter->formatCurrency( $amount, $currency );
		}
		$symbol = Utils::get_currency_symbol( $currency );
		return $symbol . $amount;
	}

	public static function get_receipt_placeholders_sample() {
		$placeholder = array(
			'date'                               => self::format_stripe_date( 1614418416 ),
			'customer.email'                     => 'jenny@example.com',
			'amount_currency'                    => '$20',
			'payment_method'                     => 'card',
			'payment_method.card.brand'          => 'visa',
			'payment_method.card.last4'          => '3212',
			'billing_detail.address.city'        => 'Allendale',
			'billing_detail.address.country'     => 'US',
			'billing_detail.address.line1'       => '3811  Kimberly Way',
			'billing_detail.address.line2'       => '',
			'billing_detail.address.postal_code' => '49401',
			'billing_detail.address.state'       => 'MI',
			'billing_detail.name'                => 'Jenny Rosen',
			'billing_detail.email'               => 'jenny@example.com',
			'billing_detail.phone'               => '619-419-9408',
		);
		return $placeholder;
	}

	public static function get_receipt_placeholders( $session ) {
		$customer        = $session['customer_details'];
		$payment_intent  = $session['payment_intent'];
		$shipping_detail = $session['shipping_details'];

		if ( empty( $payment_intent ) ) {
			$payment_intent = $session['invoice']['payment_intent'];
		}

		$billDetail = $payment_intent['payment_method']['billing_details'];

		$placeholders                   = array();
		$placeholders['customer.email'] = $customer['email'];
		$placeholders['customer.name']  = $customer['name'];
		$placeholders['customer.phone'] = $customer['phone'];

		$placeholders['shipping_details.address.country']     = $shipping_detail['address']['country'];
		$placeholders['shipping_details.address.state']       = $shipping_detail['address']['state'];
		$placeholders['shipping_details.address.city']        = $shipping_detail['address']['city'];
		$placeholders['shipping_details.address.line1']       = $shipping_detail['address']['line1'];
		$placeholders['shipping_details.address.line2']       = $shipping_detail['address']['line2'];
		$placeholders['shipping_details.address.postal_code'] = $shipping_detail['address']['postal_code'];

		$placeholders['description']     = $payment_intent['description'];
		$placeholders['amount']          = intval( $payment_intent['amount'] / 100 );
		$placeholders['currency']        = $payment_intent['currency'];
		$placeholders['created']         = self::format_stripe_date( $payment_intent['created'] );
		$placeholders['amount_currency'] = self::format_amount_with_symbol( $payment_intent['amount'], $payment_intent['currency'] );

		$placeholders['billing_detail.address.country']     = $billDetail['address']['country'];
		$placeholders['billing_detail.address.state']       = $billDetail['address']['state'];
		$placeholders['billing_detail.address.city']        = $billDetail['address']['city'];
		$placeholders['billing_detail.address.line1']       = $billDetail['address']['line1'];
		$placeholders['billing_detail.address.line2']       = $billDetail['address']['line2'];
		$placeholders['billing_detail.address.postal_code'] = $billDetail['address']['postal_code'];
		$placeholders['billing_detail.email']               = $billDetail['email'];
		$placeholders['billing_detail.name']                = $billDetail['name'];
		$placeholders['billing_detail.phone']               = $billDetail['phone'];

		$placeholders['payment_method']            = $payment_intent['payment_method']['type'];
		$placeholders['payment_method.card.brand'] = $payment_intent['payment_method']['card']['brand'];
		$placeholders['payment_method.card.last4'] = $payment_intent['payment_method']['card']['last4'];

		$placeholders['receipt.url'] = $payment_intent['latest_charge']['receipt_url'];

		return $placeholders;
	}

	public static function format_price( $price ) {
		if ( $price['recurring'] ) {
			$recurring_amount   = self::format_amount_with_symbol( $price['unit_amount'], $price['currency'] );
			$recurring_interval = $price['recurring']['interval_count'] == 1 ? $price['recurring']['interval'] : $price['recurring']['interval_count'] . ' ' . $price['recurring']['interval'];
			return $recurring_amount . '/' . $recurring_interval;
		}
		return self::format_amount_with_symbol( $price['unit_amount'], $price['currency'] );
	}

	public static function get_price_item_list() {
		$prices = get_transient( 'cf7pa_stripe_price_list' );
		if ( false === $prices ) {
			try {
				$prices = Stripe_API::retrieve_prices();
				set_transient( 'cf7pa_stripe_price_list', $prices, 5 * MINUTE_IN_SECONDS );
			} catch ( \Exception $ex ) {
				Stripe_Logger::log( 'fetch price error ' . $ex->getMessage() );
			}
		}
		$list     = array();
		$list[''] = __( 'Select one' );
		foreach ( $prices->data as $price ) {
			$list[ $price['id'] ] = $price['product']['name'] . ' - ' . self::format_price( $price );
		}
		return $list;
	}
}
