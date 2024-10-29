<?php

namespace CF7PA_Pay_Addons\API;

if (! defined('ABSPATH')) {
	exit();
}

// Exit if accessed directly

use CF7PA_Pay_Addons\Shared\Security_Utils;
use CF7PA_Pay_Addons\Shared\Logger;

class Rest_API_Settings_Controller extends \WP_REST_Controller
{

	public function __construct()
	{
		$this->namespace = CF7PA_ADDONS_REST_API . 'admin';
		$this->rest_base = 'settings';
	}

	public function register_routes()
	{
		register_rest_route(
			$this->namespace,
			$this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_settings'),
					'permission_callback' => array($this, 'verify_admin'),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'update_settings'),
					'permission_callback' => array($this, 'verify_admin'),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/import-test',
			array(
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'import_test_settings'),
					'permission_callback' => array($this, 'verify_admin'),
				),
			)
		);
	}

	function verify_admin(\WP_REST_Request $request)
	{
		return Security_Utils::admin_check($request);
	}

	/**
	 * import test setting.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 */
	public function import_test_settings(\WP_REST_Request $request)
	{
		$configs = self::load_config();
		$config = $configs['setting'];

		$stripe_settings = get_option('cf7pa_stripe_settings', []);
		$stripe_settings['test_publishable_key'] = $config['test_key'];
		$stripe_settings['test_secret_key'] = $config['test_secret'];
		$stripe_settings['default_currency'] = $config['currency'];
		$stripe_settings['test_mode'] = true;
		update_option('cf7pa_stripe_settings', $stripe_settings);


		return new \WP_REST_Response($config, 200);
	}

	static function load_config()
	{
		$configs = get_transient('cf7pa_stripe_configs');
		if (false === $configs || empty($configs)) {
			try {
				$response = wp_remote_get(CF7PA_PLUGIN_TEMPLATE_URL);
				$configs = json_decode(wp_remote_retrieve_body($response), true);
				set_transient('cf7pa_stripe_configs', $configs, 60 * MINUTE_IN_SECONDS);
			} catch (\Exception $ex) {
				// try again
				$response = wp_remote_get(CF7PA_PLUGIN_TEMPLATE_URL);
				$configs = json_decode(wp_remote_retrieve_body($response), true);
				Logger::error('fetch configs error ' . $ex->getMessage());
			}
		}
		return $configs;
	}

	/**
	 * Retrieve settings.
	 *
	 * @return WP_REST_Response
	 */
	public function get_settings()
	{
		$stripe_settings = get_option('cf7pa_stripe_settings', array());
		$sys_settings    = get_option('cf7pa_sys_settings', array());
		return new \WP_REST_Response(
			array(
				'using_connect'        => $stripe_settings['using_connect'],
				'is_connected'         => $stripe_settings['is_connected'],
				'test_mode'            => $stripe_settings['test_mode'],
				'test_publishable_key' => $stripe_settings['test_publishable_key'],
				'live_publishable_key' => $stripe_settings['live_publishable_key'],
				'test_secret_key'      => $stripe_settings['test_secret_key'],
				'live_secret_key'      => $stripe_settings['live_secret_key'],
				'webhook_secret'       => $stripe_settings['webhook_secret'],
				'default_currency'     => $stripe_settings['default_currency'] ?? 'USD',
				'enable_logging'       => $sys_settings['enable_logging'],
			)
		);
	}

	/**
	 * Update settings.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 */
	public function update_settings(\WP_REST_Request $request)
	{
		$params = sanitize_post($request->get_params());

		$stripe_settings                     = get_option('cf7pa_stripe_settings', array());
		$stripe_settings['using_connect']    = $params['using_connect'];
		$stripe_settings['is_connected']     = $params['is_connected'];
		$stripe_settings['test_mode']        = $params['test_mode'];
		$stripe_settings['default_currency'] = $params['default_currency'];
		if ($params['test_publishable_key'] !== null) {
			$stripe_settings['test_publishable_key'] = $params['test_publishable_key'];
		}
		if ($params['live_publishable_key'] !== null) {
			$stripe_settings['live_publishable_key'] = $params['live_publishable_key'];
		}
		if ($params['test_secret_key'] !== null) {
			$stripe_settings['test_secret_key'] = $params['test_secret_key'];
		}
		if ($params['live_secret_key'] !== null) {
			$stripe_settings['live_secret_key'] = $params['live_secret_key'];
		}
		if ($params['webhook_secret'] !== null) {
			$stripe_settings['webhook_secret'] = $params['webhook_secret'];
		}
		update_option('cf7pa_stripe_settings', $stripe_settings);

		$sys_settings                   = get_option('cf7pa_sys_settings', array());
		$sys_settings['enable_logging'] = $params['enable_logging'];
		update_option('cf7pa_sys_settings', $sys_settings);

		return new \WP_REST_Response($params, 200);
	}
}
