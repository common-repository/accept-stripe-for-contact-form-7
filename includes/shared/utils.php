<?php

namespace CF7PA_Pay_Addons\Shared;

class Utils {


	public static function format_stripe_date( $seconds ) {
		// return date('Y-m-d H:i:s', $seconds);
		return date_i18n( 'F j, Y H:i:s', $seconds );
	}

	public static function format_amount_with_symbol( $amount, $currency ) {
		$amount = intval( $amount ) / 100;
		if ( class_exists( 'NumberFormatter' ) ) {
			$formatter = new \NumberFormatter( get_locale(), \NumberFormatter::CURRENCY );
			return $formatter->formatCurrency( $amount, $currency );
		}
		$symbol = self::get_currency_symbol( $currency );
		return $symbol . $amount;
	}

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

	public static function get_formatted_address( $args = array(), $separator = '<br/>' ) {
		$address_string = $args['line1'] . $separator;

		if ( ! empty( $args['line2'] ) ) {
			$address_string .= $args['line2'] . $separator;
		}

		$address_string .= $args['city'] . ', ' . $args['state'] . ' ' . $args['postal_code'] . ' ';
		$address_string .= $args['country'];

		// We're done!
		return $address_string;
	}

	public static function get_currencies() {
		$currencies = array(
			'USD' => array( __( 'US Dollars (USD)', 'elementor-pay-addons' ), '$' ),
			'EUR' => array( __( 'Euros (EUR)', 'elementor-pay-addons' ), '€' ),
			'GBP' => array( __( 'Pounds Sterling (GBP)', 'elementor-pay-addons' ), '£' ),
			'AUD' => array( __( 'Australian Dollars (AUD)', 'elementor-pay-addons' ), 'AU$' ),
			'AED' => array( __( 'United Arab Emirates Dirham (AED)', 'elementor-pay-addons' ), 'د.إ' ),
			'ARS' => array( __( 'Argentine Peso (ARS)', 'elementor-pay-addons' ), 'ARS' ),
			'BAM' => array( __( 'Bosnia and Herzegovina Convertible Mark (BAM)', 'elementor-pay-addons' ), 'KM' ),
			'BGN' => array( __( 'Bulgarian Lev (BGN)', 'elementor-pay-addons' ), 'Лв.' ),
			'BRL' => array( __( 'Brazilian Real (BRL)', 'elementor-pay-addons' ), 'R$' ),
			'CAD' => array( __( 'Canadian Dollars (CAD)', 'elementor-pay-addons' ), 'CA$' ),
			'CLP' => array( __( 'Chilean Peso (CLP)', 'elementor-pay-addons' ), 'CLP' ),
			'CNY' => array( __( 'Chinese Yuan (CNY)', 'elementor-pay-addons' ), 'CN￥' ),
			'COP' => array( __( 'Colombian Peso (COP)', 'elementor-pay-addons' ), 'COL$' ),
			'CZK' => array( __( 'Czech Koruna (CZK)', 'elementor-pay-addons' ), 'Kč' ),
			'DKK' => array( __( 'Danish Krone (DKK)', 'elementor-pay-addons' ), 'kr' ),
			'DOP' => array( __( 'Dominican Peso (DOP)', 'elementor-pay-addons' ), 'RD$' ),
			'EGP' => array( __( 'Egyptian Pound (EGP)', 'elementor-pay-addons' ), 'E£' ),
			'HKD' => array( __( 'Hong Kong Dollar (HKD)', 'elementor-pay-addons' ), 'HK$' ),
			'HUF' => array( __( 'Hungarian Forint (HUF)', 'elementor-pay-addons' ), 'Ft' ),
			'INR' => array( __( 'Indian Rupee (INR)', 'elementor-pay-addons' ), '₹' ),
			'IDR' => array( __( 'Indonesia Rupiah (IDR)', 'elementor-pay-addons' ), 'Rp' ),
			'ILS' => array( __( 'Israeli Shekel (ILS)', 'elementor-pay-addons' ), '₪' ),
			'JPY' => array( __( 'Japanese Yen (JPY)', 'elementor-pay-addons' ), '¥' ),
			'LBP' => array( __( 'Lebanese Pound (LBP)', 'elementor-pay-addons' ), 'ل.ل' ),
			'MYR' => array( __( 'Malaysian Ringgits (MYR)', 'elementor-pay-addons' ), 'RM' ),
			'MXN' => array( __( 'Mexican Peso (MXN)', 'elementor-pay-addons' ), 'MX$' ),
			'NZD' => array( __( 'New Zealand Dollar (NZD)', 'elementor-pay-addons' ), 'NZ$' ),
			'NOK' => array( __( 'Norwegian Krone (NOK)', 'elementor-pay-addons' ), 'kr' ),
			'PEN' => array( __( 'Peruvian Nuevo Sol (PEN)', 'elementor-pay-addons' ), 'S/' ),
			'PHP' => array( __( 'Philippine Pesos (PHP)', 'elementor-pay-addons' ), '₱' ),
			'PLN' => array( __( 'Polish Zloty (PLN)', 'elementor-pay-addons' ), 'zł' ),
			'RON' => array( __( 'Romanian Leu (RON)', 'elementor-pay-addons' ), 'lei' ),
			'RUB' => array( __( 'Russian Ruble (RUB)', 'elementor-pay-addons' ), '₽' ),
			'SAR' => array( __( 'Saudi Riyal (SAR)', 'elementor-pay-addons' ), 'ر.س' ),
			'SGD' => array( __( 'Singapore Dollar (SGD)', 'elementor-pay-addons' ), 'SG$' ),
			'ZAR' => array( __( 'South African Rand (ZAR)', 'elementor-pay-addons' ), 'R' ),
			'KRW' => array( __( 'South Korean Won (KRW)', 'elementor-pay-addons' ), '₩' ),
			'SEK' => array( __( 'Swedish Krona (SEK)', 'elementor-pay-addons' ), 'kr' ),
			'CHF' => array( __( 'Swiss Franc (CHF)', 'elementor-pay-addons' ), 'CHF' ),
			'TWD' => array( __( 'Taiwan New Dollars (TWD)', 'elementor-pay-addons' ), 'NT$' ),
			'THB' => array( __( 'Thai Baht (THB)', 'elementor-pay-addons' ), '฿' ),
			'TRY' => array( __( 'Turkish Lira (TRY)', 'elementor-pay-addons' ), '₺' ),
			'UYU' => array( __( 'Uruguayan Peso (UYU)', 'elementor-pay-addons' ), '$U' ),
			'VND' => array( __( 'Vietnamese Dong (VND)', 'elementor-pay-addons' ), '₫' ),
		);

		return $currencies;
	}

	public static function get_currencies_options() {
		$currenciesOptions = array();
		$all_currencies    = self::get_currencies();
		foreach ( $all_currencies as $cur => $val ) {
			$currenciesOptions[ $cur ] = $val[1] . ' ' . $val[0];
		}
		return $currenciesOptions;
	}

	public static function get_supported_payment_methods( $currency ) {
		$default_methods = array(
			'automatic' => __( 'Automatic collect', 'elementor-pay-addons' ),
			'card'      => __( 'Card', 'elementor-pay-addons' ),
		);
		$china_methods   = array(
			'automatic'  => __( 'Automatic collect', 'elementor-pay-addons' ),
			'card'       => __( 'Card', 'elementor-pay-addons' ),
			'alipay'     => __( 'Alipay', 'elementor-pay-addons' ),
			'wechat_pay' => __( 'WeChat', 'elementor-pay-addons' ),
		);
		$usd_methods     = array(
			'automatic'         => __( 'Automatic collect', 'elementor-pay-addons' ),
			'card'              => __( 'Card', 'elementor-pay-addons' ),
			'affirm'            => __( 'Affirm', 'elementor-pay-addons' ),
			'afterpay_clearpay' => __( 'Afterpay (Clearpay)', 'elementor-pay-addons' ),
			'alipay'            => __( 'Alipay', 'elementor-pay-addons' ),
			'us_bank_account'   => __( 'ACH Direct Debit', 'elementor-pay-addons' ),
			'wechat_pay'        => __( 'WeChat', 'elementor-pay-addons' ),
		);
		$payment_methods = array(
			'USD' => $usd_methods,
			'CAD' => $usd_methods,
			'CNY' => $china_methods,
			'HKD' => $china_methods,
			'SGD' => $china_methods,
			'JPY' => $china_methods,
			'EUR' => array(
				'automatic'  => __( 'Automatic collect', 'elementor-pay-addons' ),
				'bancontact' => __( 'Bancontact', 'elementor-pay-addons' ),
				'card'       => __( 'Card', 'elementor-pay-addons' ),
				'eps'        => __( 'EPS', 'elementor-pay-addons' ),
				'ideal'      => __( 'iDEAL', 'elementor-pay-addons' ),
				'giropay'    => __( 'giropay', 'elementor-pay-addons' ),
				'sepa_debit' => __( 'SEPA Direct Debit', 'elementor-pay-addons' ),
				'sofort'     => __( 'SOFORT', 'elementor-pay-addons' ),
			),
			'MYR' => array(
				'fpx' => __( 'FPX', 'elementor-pay-addons' ),
			),
		);

		return $payment_methods[ $currency ] ?? $default_methods;
	}

	public static function get_currency_symbol( $currency ) {
		$currency       = strtoupper( $currency );
		$all_currencies = self::get_currencies();
		if ( array_key_exists( $currency, $all_currencies ) ) {
			return $all_currencies[ $currency ][1];
		}
		return '$';
	}

	public static function get_pages_options() {
		$pages   = get_pages(
			array(
				'sort_column'  => 'menu_order',
				'sort_order'   => 'ASC',
				'hierarchical' => 0,
			)
		);
		$options = array();
		foreach ( $pages as $page ) {
			$options[ get_page_link( $page->ID ) ] = ! empty( $page->post_title ) ? $page->post_title : '#' . $page->ID;
		}

		return $options;
	}

	public static function make_classname( $dirname ) {
		$dirname    = pathinfo( $dirname, PATHINFO_FILENAME );
		$class_name = explode( '-', $dirname );
		$class_name = array_map( 'ucfirst', $class_name );
		$class_name = implode( '_', $class_name );

		return $class_name;
	}

	public static function format_stripe_desc( $name, $desc ) {
		return $name . ( $desc ? ' - ' . $desc : '' );
	}
}
