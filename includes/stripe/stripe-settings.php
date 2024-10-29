<?php
namespace CF7PA_Pay_Addons\Stripe;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class Stripe_Settings {


	/**
	 * Secret API Key.
	 *
	 * @var string
	 */
	private static $secret_key = '';

	/**
	 * Set secret API Key.
	 *
	 * @param string $key
	 */
	public static function set_secret_key( $secret_key ) {
		self::$secret_key = $secret_key;
	}

	/**
	 * Get secret key.
	 *
	 * @return string
	 */
	public static function get_secret_key() {
		if ( ! self::$secret_key ) {
			$secret_key      = self::get_setting( 'live_secret_key' );
			$test_secret_key = self::get_setting( 'test_secret_key' );
			$test_mode       = self::is_test_mode();

			self::set_secret_key( $test_mode ? $test_secret_key : $secret_key );
		}
		return self::$secret_key;
	}

	public static function get_publishable_key() {
		$test_mode = self::is_test_mode();
		return $test_mode ? self::get_setting( 'test_publishable_key' ) : self::get_setting( 'live_publishable_key' );
	}

	public static function is_test_mode() {
		return self::get_setting( 'test_mode' );
	}

	public static function get_setting( $key ) {
		$stripe_settings = get_option( 'cf7pa_stripe_settings', array() );
		return $stripe_settings[ $key ] ?? '';
	}
}
