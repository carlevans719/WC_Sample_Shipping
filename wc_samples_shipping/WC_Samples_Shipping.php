<?php
/**
 * @link              TBC
 * @since             0.1.0
 * @package           woocommerce_sample_shipping
 *
 * @wordpress-plugin
 * Plugin Name:		  Woocommerce Samples Shipping
 * Plugin URI:        TBC
 * Description:       Sets up shipping for samples
 * Version:           0.1.0-alpha
 * Author:            Carl Evans
 * Author URI:        TBC
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       TBC
 */

require_once('WC_Samples_Shipping_Method_first.php');
require_once('WC_Samples_Shipping_Method_second.php');

if (!class_exists("WC_Sample_Shipping")) {
	class WC_Sample_Shipping {
		
		public static function cwe_cap_samples_at_three( $passed, $product_id, $quantity, $variation_id = '', $variations= '' ) {
			global $woocommerce;
			$passed = true;
			$sampleCount = 0;

		    foreach ($woocommerce->cart->cart_contents as $item)
		    	if (!empty($item['sample']) && intval($item['sample']) === 1) 
		    		$sampleCount += 1;

		    if ($sampleCount > 2) {
				$passed = false;
				wc_add_notice( 'You can\'t add more than 3 samples to your cart!', 'error' );
		    }

		    return $passed;

		}


		/**
		 * Get packages for the checkout
		 *
		 * @access public
		 */
		public static function cwe_generate_packages( $packages ) {

			// Reset the packages
			$packages = array();
			$others_package = array(
				'contents' => array(),
				'contents_cost' => 0
			);

			// separate each item into a package
			foreach ( WC()->cart->get_cart() as $item ) {
				if ( $item['data']->needs_shipping() && !empty($item['sample']) && intval($item['sample']) === 1) {
					// Make dedicated $packages[]

					array_unshift($packages, array(
						'contents' => array($item),
						'contents_cost' => array_sum( wp_list_pluck( array($item), 'line_total' ) ),
						'applied_coupons' => array(),
						'destination' => array(
							'country' => WC()->customer->get_shipping_country(),
							'state' => WC()->customer->get_shipping_state(),
							'postcode' => WC()->customer->get_shipping_postcode(),
							'city' => WC()->customer->get_shipping_city(),
							'address' => WC()->customer->get_shipping_address(),
							'address_2' => WC()->customer->get_shipping_address_2()
						)
					));

					// Determine if 'ship_via' applies
					if( sizeof($packages) > 1 ) {
						$packages[ 0 ]['ship_via'] = array('first_sample_shipping');
					} elseif( sizeof($packages) === 1 ) {
						$packages[ 0 ]['ship_via'] = array('second_sample_shipping');
					}

				} else {
					// Add to $others_package
					array_push($others_package['contents'], $item);
					$others_package['contents_cost'] = array_sum( wp_list_pluck( array($item), 'line_total' ) ) + $others_package['contents_cost'];
				}
			}

			if (sizeof($others_package['contents']) > 0) {
				$others_package['applied_coupons'] = WC()->cart->applied_coupons;
				$others_package['destination'] = array(
					'country' => WC()->customer->get_shipping_country(),
					'state' => WC()->customer->get_shipping_state(),
					'postcode' => WC()->customer->get_shipping_postcode(),
					'city' => WC()->customer->get_shipping_city(),
					'address' => WC()->customer->get_shipping_address(),
					'address_2' => WC()->customer->get_shipping_address_2()
				);

				// TODO: For shipping classes (return only shipping ids shared by ALL $items)
				// $key = $item['data']->get_shipping_class_id();
				// $others_package['ship_via'] = array(/*IDS HERE*/);

				$packages[] = $others_package;
			}


			return $packages;

		}

		public static function cwe_strip_sample_shipping($rates, $packages) {

			$contents = $packages['contents'];
			$has_sample = false;
			for ($i=0; $i < count($contents); $i++) { 
				if (!empty($contents[$i]['sample'])) {
					$has_sample = true;
				}
			}

			if (!$has_sample) {
				if (isset($rates['second_sample_shipping'])) unset($rates['second_sample_shipping']);
				if (isset($rates['first_sample_shipping'])) unset($rates['first_sample_shipping']);
			}


			return $rates;
		}


	}
}




add_action('woocommerce_add_to_cart_validation', array('WC_Sample_Shipping', 'cwe_cap_samples_at_three'), 10, 5);
add_filter('woocommerce_cart_shipping_packages', array('WC_Sample_Shipping', 'cwe_generate_packages'), 10, 1);
add_filter('woocommerce_package_rates', array('WC_Sample_Shipping', 'cwe_strip_sample_shipping'), 10, 2);

new WC_Sample_Shipping();




/*					   *\
----- FOR DEBUGGING -----
\*					   */
if (!function_exists('logit')) {
	function logit($message) {
	    if (WP_DEBUG === true) {
	        if (is_array($message) || is_object($message)) {
	            error_log(print_r($message, true));
	        } else {
	            error_log($message);
	        }
	    }
	}
}