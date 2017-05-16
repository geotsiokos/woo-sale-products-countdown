<?php
/**
 * Plugin Name: WooCommerce Sale Products Countdown
 * Plugin URI: http://www.netpad.gr
 * Description: Shortcode to show products on sale with a countdown
 * @todo test with 2.x.x
 * Version: 1.0.0
 * Author: George Tsiokos
 * Author URI: http://www.netpad.gr
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Copyright (c) 2015-2016 "gtsiokos" George Tsiokos www.netpad.gr
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
define( 'WSPC_URL',			plugin_dir_url( __FILE__ ) );
define( 'WSPC_URL_PUBLIC',	WSPC_URL . 'public/' );
define( 'WSPC_DOMAIN',		'woo-sale-products-countdown' );

add_action( 'plugins_loaded', 'woo_sale_products_countdown_plugins_loaded' );
function woo_sale_products_countdown_plugins_loaded () {
	if ( defined( 'WC_VERSION' ) ) {
		add_action( 'wp_enqueue_scripts', 'wspc_enqueue_scripts' );
		add_shortcode ( 'woo_sale_products_countdown', 'woo_sale_products_countdown' );
	} else {
		echo '<div class="error">' .
		__( '<strong>WooCommerce Sale Products Countdown</strong> requires the <a href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a> plugin. Please install and activate it.', WSPC_DOMAIN ).
		'</div>';
	}
}

/**
 * Enqueue scripts and styles
 */
function wspc_enqueue_scripts() {
	wp_register_style( 	'wspc-style', WSPC_URL_PUBLIC . 'css/style.css', array() );
	wp_register_script( 'wspc-countdown', WSPC_URL_PUBLIC . 'js/jquery.countdown.js', array( 'jquery' ) );
	wp_enqueue_style( 'wspc-style' );
	wp_enqueue_script( 'wspc-countdown' );
}

/**
 * Outputs two products on sale with a countdown
 * @param array $attr
 * @return string $result
 */
function woo_sale_products_countdown( $attr = array() ) {
	$result = '';
	$sub_result1 = '';
	$sub_result2 = '';
	$wspc_first_product = '';
	$wspc_counter = '';
	$first_product = false;
	$second_product = false;
	
	$options = shortcode_atts(
			array(
					'first_product'  => null,
					'second_product'	=> null
			),
			$attr
	);
	
	if ( isset( $options['first_product'] ) || !empty( $options['first_product'] ) ) {
		$first_product = wc_get_product( $options['first_product'] );
		if ( $first_product ) {
			$first_product_id = $first_product->get_id();
			$sub_result1 .= '<div class="wspc-products">';			
			$sub_result1 .= do_shortcode( '[product id="'. $first_product_id .'"]' );
			if ( $first_product->is_on_sale() ) {
				$first_product_to_date = $first_product->get_date_on_sale_to();
				$sub_result1 .= wspc_date_countdown( $first_product_to_date->date_i18n( 'Y/m/d'), 1 );
			} else {
				$sub_result1 .= wspc_date_countdown( false, 1 );
				
			}
			$sub_result1 .= '</div>';
		}		
	}
	if ( isset( $options['first_product'] ) || !empty( $options['second_product'] ) ) {
		$second_product = wc_get_product( $options['second_product'] );
		if ( $second_product ) {
			$second_product_id = $second_product->get_id();
			$sub_result2 .= '<div class="wspc-products">';			
			$sub_result2 .= do_shortcode( '[product id="'. $second_product_id .'"]' );
			if ( $second_product->is_on_sale() ) {
				$second_product_to_date = $second_product->get_date_on_sale_to();
				$sub_result2 .= wspc_date_countdown( $second_product_to_date->date_i18n( 'Y/m/d'), 2 );
			} else {
				$sub_result2 .= wspc_date_countdown( false, 1 );
				
			}
			$sub_result2 .= '</div>';
		}		
	}
	
	$result .= $sub_result1;	
	$result .= $sub_result2;
	
	return $result;
}

/**
 * Adds a div with the countdown 
 * until which the sale is active
 * @param string $formatted_date
 * @param int $counter
 * @return string
 */
function wspc_date_countdown( $formatted_date, $counter ) {
	$output = '';
	if ( $formatted_date ) {
		$days = __( 'days', WSPC_DOMAIN );
		$output .= '<div id="wspc-counter">';
		$output .= '<p class="wspc-message">'. __( 'This offer ends in:', WSPC_DOMAIN ) .'</p>';
		$output .= '<p class="wspc-counter-'. $counter .'"></p>';
		$output .= '<script type="text/javascript">';
		$output .= 		'jQuery(document).ready(function($){ ';
		$output .= 			'$(".wspc-counter-'. $counter .'").countdown("'. $formatted_date .'", function(event) {';
		$output .= 			  	'$(this).html(event.strftime("%D '. $days .' %H:%M:%S"));';
		$output .= 			'});';
		$output .= 		'});';
		$output .= '</script>';
		$output .= '</div>';
	} else {
		$output .= '<div id="wspc-counter">';
		$output .= '<p class="wspc-message">'. __( 'This offer has expired', WSPC_DOMAIN ) .'</p>';
		$output .= '</div>';
	}
	return $output;
}
