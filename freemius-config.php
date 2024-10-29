<?php

if ( !function_exists( 'cf7pa_fs' ) ) {
    // Create a helper function for easy SDK access.
    function cf7pa_fs() {
        global $cf7pa_fs;
        if ( !isset( $cf7pa_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $cf7pa_fs = fs_dynamic_init( array(
                'id'             => '16626',
                'slug'           => 'contact-form-7-pay-addons',
                'type'           => 'plugin',
                'public_key'     => 'pk_d791faf1f7d7806a1da084dacb568',
                'is_premium'     => false,
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                    'days'               => 14,
                    'is_require_payment' => true,
                ),
                'menu'           => array(
                    'slug'    => 'contact-form-7-pay-addons',
                    'support' => false,
                ),
                'is_live'        => true,
            ) );
        }
        return $cf7pa_fs;
    }

    // Init Freemius.
    cf7pa_fs();
    // Signal that SDK was initiated.
    do_action( 'cf7pa_fs_loaded' );
}