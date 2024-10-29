<?php

function cf7pa_help_tip( $tip, $allow_html = false ) {
	if ( $allow_html ) {
		$sanitized_tip = wc_sanitize_tooltip( $tip );
	} else {
		$sanitized_tip = esc_attr( $tip );
	}

	/**
	 * Filter the help tip.
	 *
	 * @since 7.7.0
	 *
	 * @param string $tip_html       Help tip HTML.
	 * @param string $sanitized_tip  Sanitized help tip text.
	 * @param string $tip            Original help tip text.
	 * @param bool   $allow_html     Allow sanitized HTML if true or escape.
	 *
	 * @return string
	 */
	return apply_filters( 'cf7pa_help_tip', '<span class="epa-help-tip" data-tip="' . $sanitized_tip . '"></span>', $sanitized_tip, $tip, $allow_html );
}

function cf7pa_upgrade_link() {
	echo '<section class="flex justify-center">
	<a class="font-medium text-blue-600 dark:text-blue-500 hover:underline" href="' . esc_url(cf7pa_fs()->get_upgrade_url()) . '">' . esc_html__('Upgrade to unlock this feature!', 'contact-form-7-stripe-addon') . '</a>
	</section>';
}
