<?php

namespace CF7PA_Pay_Addons\API;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// Exit if accessed directly
use CF7PA_Pay_Addons\Stripe\Stripe_API;
use CF7PA_Pay_Addons\Shared\Security_Utils;
use CF7PA_Pay_Addons\Shared\Logger;
use CF7PA_Pay_Addons\submission\Session_Checkout_Submission;

class Rest_API_Stripe_Checkout_Controller extends \WP_REST_Controller {
	public function __construct() {
		$this->namespace = CF7PA_ADDONS_REST_API . 'stripe';
		$this->rest_base = 'checkout-session';
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/retrieve',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'retrieve_checkout_session' ),
					'permission_callback' => array( $this, 'verify_access' ),
				),
			)
		);
	}

	public function verify_access( \WP_REST_Request $request ) {
		return Security_Utils::client_access_check( $request );
	}

	public function retrieve_checkout_session( \WP_REST_Request $request ) {
		$postData = $request->get_params();
		$session_id = sanitize_text_field( $postData['session_id'] );
		$stored_data = Session_Checkout_Submission::get_session_transient();
		if ($stored_data && isset($stored_data['form_id']) && isset($stored_data['post_data'])) {
			$form_id = $stored_data['form_id'];
		}
		// Retrieve the Contact Form 7 form messages
		$form = \WPCF7_ContactForm::get_instance($form_id);
		if (!$form) {
			return new \WP_Error('form_not_found', __('Contact form not found' . $form_id), array('status' => 404));
		}

		$messages = $form->prop('messages');
		if (empty($messages)) {
			$messages = wpcf7_messages();
		}

		try {
			$session = Stripe_API::retrieve_checkout_session( $session_id, [ 'expand' => ['payment_intent'] ] );
			return new \WP_REST_Response( [
				'status' => $session->payment_intent->status ?? $session->payment_status, // stripe link doesn't have payment intent
				'messages' => $messages,
				'form_id' => $form_id,
			] );
		} catch ( \Exception $ex ) {
			Logger::error( 'retrieve_checkout_session ' . $ex->getMessage() );
			return new \WP_Error( 'stripe_error', __( $ex->getMessage() ), array( 'status' => 400 ) );
		}
	}
}
