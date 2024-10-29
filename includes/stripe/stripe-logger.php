<?php

namespace CF7PA_Pay_Addons\Stripe;

use CF7PA_Pay_Addons\Shared\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Log all things!
 *
 * @since 4.0.0
 * @version 4.0.0
 */
class Stripe_Logger {

	public static $logger;
	const WC_LOG_FILENAME = 'cf7-pay-addons';

	/**
	 * Utilize WC logger class
	 *
	 * @since 4.0.0
	 * @version 4.0.0
	 */
	public static function log( $message, $start_time = null, $end_time = null ) {
		// TODO
		Logger::debug( $message );
	}
}
