<?php

use CF7PA_Pay_Addons\Shared\Utils;
use CF7PA_Pay_Addons\Stripe\Stripe_Webhook_State;

$webhook_latest_message = Stripe_Webhook_State::get_webhook_status_message();
$webhook_no_event       = Stripe_Webhook_State::is_no_event_received();
$webhook_url            = rest_url( CF7PA_ADDONS_REST_API . 'stripe/webhook' );
$currencies             = Utils::get_currencies_options();
?>
<div id="settings" class="cf7pa-tab-panel p-8 active">
	<div class="flex justify-between items-center pb-5 border-b border-gray-300">
	<div>
		<h4>Stripe Settings</h4>
		<span class="text-gray-400">&nbsp;</span>
	</div>
	</div>
	<div class="py-4">
	<form id="stripe_setting">
		<table class="form-table">
		<tbody>
			<tr style="display:none">
			<th><label for="using_connect">Connect Method</label></th>
			<td>
				<fieldset>
				<ul class="flex">
					<li class="mr-2">
					<label><input name="using_connect" id="radio_quick" value="true" type="radio" checked="checked">Quick Connect</label>
					</li>
					<li>
					<label><input name="using_connect" id="radio_api" value="false" type="radio">API key Connect</label>
					</li>
				</ul>
				</fieldset>
			</td>
			</tr>
			<tr style="display:none">
			<th><label for="is_connected">Connection status</label></th>
			<td>
				<button id="btn_connect" type="button" class="text-white bg-[#1da1f2] hover:bg-[#1da1f2]/90 focus:ring-4 focus:outline-none focus:ring-[#1da1f2]/50 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:focus:ring-[#1da1f2]/55 mr-2 mb-2">
				Connect with Stripe
				</button>
				<button id="btn_disconnect" type="button" class="invisible focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">
				Disconnect
				</button>
			</td>
			</tr>
			<tr>
			<th><label for="test_mode">Mode</label></th>
			<td>
				<fieldset>
				<ul class="flex">
					<li class="mr-2">
					<label><input id="live_mode" name="test_mode" value="false" type="radio" checked="checked">Live</label>
					</li>
					<li>
					<label><input id="test_mode" name="test_mode" value="true" type="radio"> Test</label>
					</li>
				</ul>
				</fieldset>
			</td>
			</tr>
			<tr>
			<th><label for="test_publishable_key">Test Key</label></th>
			<td>
				<input id="test_publishable_key" placeholder="pk_test_xxxxx" name="test_publishable_key" type="text" class="regular-text">
				<br />
				<span>No account yet, try to <a href class="btn-import-test cf7pa-button-link">import</a> a temporary account. </span>
			</td>
			</tr>
			<tr>
			<th><label for="test_secret_key">Test Secret</label></th>
			<td><input id="test_secret_key" placeholder="sk_test_xxxxx" name="test_secret_key" type="text" class="regular-text"></td>
			</tr>
			<tr>
			<th><label for="live_publishable_key">Live Key</label></th>
			<td><input id="live_publishable_key" placeholder="pk_live_xxxxx" name="live_publishable_key" type="text" class="regular-text"></td>
			</tr>
			<tr>
			<th><label for="live_secret_key">Live Secret</label></th>
			<td><input id="live_secret_key" placeholder="sk_live_xxxxx" name="live_secret_key" type="text" class="regular-text"></td>
			</tr>
			<tr>
			<th><label for="currency">Default Currency</label></th>
			<td>
				<select id="default_currency" name="default_currency">
				<?php
				foreach ( $currencies as $key => $value ) {
					?>
				<option value="<?php echo esc_html( $key ); ?>"><?php echo esc_html( $value ); ?></option>
					<?php
				}
				?>
				</select>
			</td>
			</tr>
			<tr>
			<th><label for="webhook_url">Webhook URL</label></th>
			<td>
				<div>
				Add the following webhook endpoint
				<strong class="bg-gray-200"><?php echo esc_url($webhook_url); ?></strong>
				<button type="button" data-copy-state="copy" class="cf7pa-button-copy text-xs font-medium text-gray-600 dark:text-gray-400 dark:bg-gray-800 hover:text-blue-700 dark:hover:text-white">
					<svg class="cf7pa-webhook-copy w-3.5 h-3.5 mr-2" data-url="<?php echo esc_url($webhook_url); ?>" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
					<path d="M5 9V4.13a2.96 2.96 0 0 0-1.293.749L.879 7.707A2.96 2.96 0 0 0 .13 9H5Zm11.066-9H9.829a2.98 2.98 0 0 0-2.122.879L7 1.584A.987.987 0 0 0 6.766 2h4.3A3.972 3.972 0 0 1 15 6v10h1.066A1.97 1.97 0 0 0 18 14V2a1.97 1.97 0 0 0-1.934-2Z" />
					<path d="M11.066 4H7v5a2 2 0 0 1-2 2H0v7a1.969 1.969 0 0 0 1.933 2h9.133A1.97 1.97 0 0 0 13 18V6a1.97 1.97 0 0 0-1.934-2Z" />
					</svg>
				</button>
				to your
				<a class="cf7pa-button-link" href="https://dashboard.stripe.com/account/webhooks" target="_blank" rel="external noreferrer noopener">Stripe account settings
					<svg class="w-3 h-3 inline text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
					<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11v4.833A1.166 1.166 0 0 1 13.833 17H2.167A1.167 1.167 0 0 1 1 15.833V4.167A1.166 1.166 0 0 1 2.167 3h4.618m4.447-2H17v5.768M9.111 8.889l7.778-7.778" />
					</svg>
				</a> (if there isn't one already). This will enable you to receive notifications on the charge statuses.
				</div>
				<div class="p-1 mt-2 text-sm text-gray-800 rounded-lg bg-gray-50 dark:bg-gray-800 dark:text-gray-300" role="alert">
				<div class="<?php echo $webhook_no_event ? 'text-gray-400' : ''; ?>">
					<span class="inline-flex items-center justify-center w-2 h-2 mr-1 text-xs font-semibold rounded-full <?php echo $webhook_no_event ? 'bg-gray-400' : 'bg-green-500'; ?>"></span>
					<span class="font-medium">status:</span> <?php echo esc_html( $webhook_latest_message ); ?> 
				</div>
				</div>
			</td>
			</tr>
			<tr>
			<th><label for="webhook_secret">Webhook Secret</label></th>
			<td>
				<input id="webhook_secret" placeholder="whsec_js_xxxxx" name="webhook_secret" type="text" class="regular-text">
				<div>(Reveal the signing secret of the bove webhook and copy it to here to increasing your webhook security)</div>
			</td>
			</tr>
			<tr>
			<th><label for="logging">Enable logging</label></th>
			<td><input id="logging" type="checkbox" name="enable_logging" class="regular-text"></td>
			</tr>
		</tbody>
		</table>
	</form>
	</div>
	<div class="flex justify-end">
	<button name="btnSaveSettings" class="btn-save-setting cf7pa-button-primary" type="submit">Save changes</button>
	</div>
	<div id="alert"></div>
</div>