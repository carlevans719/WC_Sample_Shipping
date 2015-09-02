<?php

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	function cwe_second_shipping_init() {
		if ( ! class_exists( 'WC_Samples_Shipping_Method_Second' ) ) {
			class WC_Samples_Shipping_Method_Second extends WC_Shipping_Method {
				/**
				 * Constructor for your shipping class
				 *
				 * @access public
				 * @return void
				 */
				public function __construct() {
					$this->id                 = 'second_sample_shipping';
					$this->method_title       = __( 'Additional Sample Delivery' );
					$this->method_description = __( 'Standard delivery cost for additional product samples' );

					$this->enabled            = "yes";
					$this->title              = "Additional Sample Delivery";
					$this->availability		  = "all";

					$this->init();
				}

				/**
				 * Init your settings
				 *
				 * @access public
				 * @return void
				 */
				function init() {
					// Load the settings API
					$this->init_settings();

					$this->rate_price = $this->get_option('rate_price');
					$this->form_fields = array(
						'enabled' => array(
							'title' 		=> 'Enable/Disable',
							'type' 			=> 'checkbox',
							'label' 		=> 'Enable this shipping method',
							'default' 		=> 'yes',
						),
						'rate_price' => array(
							'title' 		=> 'Cost',
							'type' 			=> 'price',
							'placeholder'	=> wc_format_localized_price( 3.00 ),
							'description'	=> 'Enter a cost (excluding tax) for the second sample in the cart, e.g. 3.00. Default is 3.00.',
							'default'		=> '3.00',
							'desc_tip'		=> true
						)
					);
					// Save settings in admin if you have any defined
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
				}

				/**
				 * calculate_shipping function.
				 *
				 * @access public
				 * @param mixed $package
				 * @return void
				 */
				public function calculate_shipping( $package ) {
					$rate = array(
						'id' => $this->id,
						'label' => $this->title,
						'cost' => $this->rate_price,
						'calc_tax' => 'per_item'
					);

					// Register the rate
					$this->add_rate( $rate );
				}
			}
		}
	}

	add_action( 'woocommerce_shipping_init', 'cwe_second_shipping_init' );

	function cwe_add_second_shipping_method( $methods ) {
		$methods[] = 'WC_Samples_Shipping_Method_Second';
		return $methods;
	}

	add_filter( 'woocommerce_shipping_methods', 'cwe_add_second_shipping_method' );
}