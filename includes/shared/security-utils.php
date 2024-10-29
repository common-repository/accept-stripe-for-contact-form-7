<?php

namespace CF7PA_Pay_Addons\Shared;

use WP_Error;
use WP_REST_Request;

class Security_Utils {

	public static function admin_check() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				esc_html__( 'You dont have the right permissions', 'cf7_pay_addons' ),
				array( 'status' => 401 )
			);
		}

		return true;
	}

	public static function client_access_check( WP_REST_Request $request ) {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_Error(
				'rest_forbidden',
				esc_html__( 'You are not allowed to do that', 'cf7_pay_addons' ),
				array( 'status' => 403 )
			);
		}
		return true;
	}
}
