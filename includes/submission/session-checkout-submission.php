<?php

namespace CF7PA_Pay_Addons\submission;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
use CF7PA_Pay_Addons\Stripe\Stripe_API;
use CF7PA_Pay_Addons\Shared\Logger;
use CF7PA_Pay_Addons\Shared\Utils;
class Session_Checkout_Submission {
    static $session_prefix = 'cf7pa_user_data_';

    public function __construct() {
        add_action(
            'wpcf7_before_send_mail',
            [$this, 'session_checkout_handler'],
            10,
            3
        );
        add_action( 'wp', [$this, 'session_checkout_handler_return'] );
        add_action( 'init', [$this, 'start_session'], 1 );
    }

    function start_session() {
        if ( !session_id() && !headers_sent() ) {
            session_start();
        }
    }

    public function session_checkout_handler( $contact_form, &$abort, $submission ) {
        // Get form ID
        $form_id = $contact_form->id();
        // Get payment settings
        $form_settings = get_post_meta( $form_id, 'cf7pa_checkout_redirect_setting', true );
        // Check if payment is enabled for this form
        if ( empty( $form_settings ) || empty( $form_settings['enable'] ) ) {
            return;
        }
        // Get form data
        $form_data = $submission->get_posted_data();
        // append form id
        $form_data['form_id'] = $form_id;
        $is_stripe_link = $form_settings['enable_link'] == 1;
        // Create Checkout Session
        try {
            // Store form ID and POST data in transient for later use
            self::set_session_transient( [
                'form_id'   => $form_id,
                'post_data' => $_POST,
            ] );
            $redirect_link = '';
            if ( $is_stripe_link ) {
                $redirect_link = $form_settings['stripe_link'];
            } else {
                $checkout_session = $this->create_session_checkout( $form_data, $form_settings );
                $redirect_link = $checkout_session->url;
            }
            $response = array(
                'success'     => true,
                'redirectUrl' => $redirect_link,
            );
            $submission->set_status( 'stripe-redirect' );
            $submission->set_response( $response );
        } catch ( \Exception $e ) {
            // Handle errors
            $submission->set_status( 'stripe-error' );
            $submission->set_response( $e->getMessage() );
        }
        // Abort the normal form submission
        $abort = true;
    }

    public function create_session_checkout( $contact_form_data, $contact_form_seting ) {
        $session_checkout_args = $this->collect_session_checkout_args( $contact_form_data, $contact_form_seting );
        $session_checkout = Stripe_API::create_checkout_session( $session_checkout_args );
        return $session_checkout;
    }

    protected function get_current_url() {
        $current_url = wp_get_referer();
        // If wp_get_referer() returns false, fallback to home_url()
        if ( !$current_url ) {
            $current_url = home_url( add_query_arg( array() ) );
        }
        return $current_url;
    }

    // Function to set session-specific transient
    static function set_session_transient( $data, $expiration = 3600 ) {
        $session_id = session_id();
        set_transient( self::$session_prefix . $session_id, $data, $expiration );
    }

    // Function to get session-specific transient
    static function get_session_transient() {
        $session_id = session_id();
        return get_transient( self::$session_prefix . $session_id );
    }

    static function clear_session_transient() {
        $session_id = session_id();
        delete_transient( self::$session_prefix . $session_id );
    }

    protected function is_subscription( $form_settings ) {
        return false;
    }

    protected function collect_session_checkout_args( $contact_form_data, $contact_form_seting ) {
        $form_id = $contact_form_data['form_id'];
        $form_settings = $this->get_form_settings( $contact_form_data, $contact_form_seting );
        $is_subscription = $this->is_subscription( $form_settings );
        $line_item = [
            'quantity' => floatval( $form_settings[( $is_subscription ? 'sub_quantity_field' : 'onetime_quantity_field' )] ),
        ];
        if ( $is_subscription && $form_settings['sub_enable_pricing_plan'] == 'yes' ) {
            $price_id = $form_settings['sub_price_id_field'];
            if ( empty( $price_id ) ) {
                $price_id = $form_settings['sub_price_id'];
            }
            $line_item['price'] = $price_id;
        } else {
            $currency = strtolower( $form_settings[( $is_subscription ? 'sub_currency' : 'onetime_currency' )] );
            $unit_amount = floatval( $form_settings[( $is_subscription ? 'sub_amount_field' : 'onetime_amount_field' )] );
            $name = $form_settings[( $is_subscription ? 'sub_product_name_field' : 'onetime_product_name_field' )];
            $line_item['price_data'] = [
                'currency'     => $currency,
                'unit_amount'  => $unit_amount * 100,
                'product_data' => [
                    'name' => $name,
                ],
            ];
            $desc = $form_settings[( $is_subscription ? 'sub_product_desc_field' : 'onetime_product_desc_field' )];
            if ( !empty( $desc ) ) {
                $line_item['price_data']['product_data']['description'] = $desc;
            }
            if ( $is_subscription ) {
                $line_item['price_data']['recurring'] = [
                    'interval'       => strtolower( $form_settings['sub_interval_field'] ),
                    'interval_count' => floatval( $form_settings['sub_interval_count_field'] ),
                ];
            }
            if ( $form_settings['automatic_tax'] == 'yes' ) {
                $checkout_session['automatic_tax'] = [
                    'enabled' => true,
                ];
                $checkout_session['tax_id_collection'] = [
                    'enabled' => true,
                ];
                $line_item['price_data']['tax_behavior'] = $form_settings['tax_behavior'];
            }
        }
        $success_url = $this->get_current_url();
        $cancel_url = $this->get_current_url();
        if ( !empty( $form_settings['success_url'] ) ) {
            $success_url = $form_settings['success_url'];
        }
        if ( !empty( $form_settings['cancel_url'] ) ) {
            $cancel_url = $form_settings['cancel_url'];
        }
        $checkout_session = [
            'payment_method_types'       => $form_settings['payment_method_types'],
            'line_items'                 => [$line_item],
            'mode'                       => ( $is_subscription ? 'subscription' : 'payment' ),
            'success_url'                => add_query_arg( [
                'session_id' => '{CHECKOUT_SESSION_ID}',
                'form_id'    => $form_id,
            ], esc_url_raw( $success_url ) ),
            'cancel_url'                 => esc_url_raw( $cancel_url ),
            'billing_address_collection' => ( $form_settings['billing_address_collection'] == 'yes' ? 'required' : 'auto' ),
            'allow_promotion_codes'      => ( $form_settings['allow_promotion_codes'] == 'yes' ? true : false ),
        ];
        if ( in_array( 'automatic', $checkout_session['payment_method_types'] ) ) {
            unset($checkout_session['payment_method_types']);
        }
        if ( !empty( $form_settings['email_field'] ) ) {
            $checkout_session['customer_email'] = $form_settings['email_field'];
        }
        if ( $checkout_session['mode'] == 'payment' && !empty( $form_settings['submit_type'] ) ) {
            $checkout_session['submit_type'] = $form_settings['submit_type'];
        }
        if ( $form_settings['phone_number_collection'] == 'yes' ) {
            $checkout_session['phone_number_collection'] = [
                'enabled' => true,
            ];
        }
        if ( $form_settings['terms_of_service'] == 'yes' ) {
            $checkout_session['consent_collection'] = [
                'terms_of_service' => ( $form_settings['terms_of_service'] == 'yes' ? 'required' : 'none' ),
            ];
        }
        if ( !empty( $form_settings['shipping_address_collection'] ) ) {
            $checkout_session['shipping_address_collection'] = [
                'allowed_countries' => $form_settings['shipping_address_collection'],
            ];
        }
        $checkout_session['payment_intent_data'] = [];
        $checkout_session['subscription_data'] = [];
        if ( !empty( $form_settings['metadata'] ) ) {
            $meta_list = $form_settings['metadata'];
            foreach ( $meta_list as $meta ) {
                $key = $meta['metadata_key'];
                if ( $key !== '' ) {
                    $meta_obj[$key] = $meta['metadata_value'];
                }
            }
            if ( !$is_subscription && !empty( $meta_obj ) ) {
                $checkout_session['payment_intent_data']['metadata'] = $meta_obj;
            }
            if ( $is_subscription && !empty( $meta_obj ) ) {
                $checkout_session['subscription_data']['metadata'] = $meta_obj;
            }
        }
        if ( $name ) {
            $description = Utils::format_stripe_desc( $name, $desc );
            if ( !$is_subscription ) {
                $checkout_session['payment_intent_data']['description'] = $description;
            }
            if ( $is_subscription ) {
                $checkout_session['subscription_data']['description'] = $description;
            }
        }
        return $checkout_session;
    }

    public function get_form_settings( $contact_form_data, $contact_form_seting ) {
        $processed_fields = [];
        $field_types = [
            'email_field',
            'onetime_currency_field',
            'onetime_quantity_field',
            'onetime_amount_field',
            'onetime_product_name_field',
            'onetime_product_desc_field',
            'sub_quantity_field',
            'sub_amount_field',
            'sub_interval_count_field',
            'sub_interval_field',
            'sub_product_name_field',
            'sub_product_desc_field',
            'payment_type_condition_field'
        ];
        foreach ( $field_types as $field_type ) {
            if ( isset( $contact_form_seting[$field_type] ) ) {
                $field_value = $contact_form_seting[$field_type];
                $value = $field_value;
                if ( preg_match( '/^\\[(.+)\\]$/', $field_value, $matches ) ) {
                    $field_name = $matches[1];
                    $value = ( isset( $contact_form_data[$field_name] ) ? $contact_form_data[$field_name] : '' );
                }
                $processed_fields[$field_type] = $value;
            }
        }
        return array_merge( $contact_form_seting, $processed_fields );
    }

    public function session_checkout_handler_return() {
        if ( isset( $_GET['session_id'] ) ) {
            $session_id = $_GET['session_id'];
            // Verify the payment status
            try {
                $session = Stripe_API::retrieve_checkout_session( $session_id, [] );
                if ( $session->payment_status === 'paid' ) {
                    $stored_data = self::get_session_transient();
                    if ( $stored_data && isset( $stored_data['form_id'] ) && isset( $stored_data['post_data'] ) ) {
                        $form_id = $stored_data['form_id'];
                        $post_data = $stored_data['post_data'];
                        // Restore POST data
                        $_POST = $post_data;
                        // Trigger Contact Form 7 submission
                        $contact_form = wpcf7_contact_form( $form_id );
                        if ( $contact_form ) {
                            // Remove our hook temporarily
                            remove_action( 'wpcf7_before_send_mail', [$this, 'session_checkout_handler'] );
                            // Submit the form
                            $result = $contact_form->submit();
                            // Reattach our hook
                            add_action(
                                'wpcf7_before_send_mail',
                                [$this, 'session_checkout_handler'],
                                10,
                                3
                            );
                            if ( $result['status'] === 'mail_sent' ) {
                                Logger::info( 'Form submitted successfully for form ID: ' . $form_id );
                            } else {
                                Logger::error( 'Form submission failed for form ID: ' . $form_id . '. Status: ' . $result['status'] );
                            }
                        } else {
                            Logger::error( 'Contact form not found for ID: ' . $form_id );
                        }
                        // Clear the transient
                        // self::clear_session_transient();
                    } else {
                        Logger::error( 'Stored data not found for session ID: ' . $session_id );
                    }
                } else {
                    Logger::info( 'Payment not completed for session ID: ' . $session_id );
                }
            } catch ( \Exception $e ) {
                Logger::error( 'Error processing Stripe session: ' . $e->getMessage() );
            }
        }
    }

}
