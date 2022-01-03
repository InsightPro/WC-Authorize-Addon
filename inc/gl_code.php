<?php

  /**
	 * Display GL CODE
	 * @since 1.0.0
	 */
	function cfwc_create_custom_field2() {
		$args = array(
			'id'            => 'custom_text_field_title2',
			'label'         => __( 'GL Code', 'wcpf' ),
			'class'			=> 'cfwc-custom-field2',
			'desc_tip'      => true,
			'description'   => __( 'Enter GL Code.', 'wcpf' ),
		);
		woocommerce_wp_text_input( $args );
	}
	add_action( 'woocommerce_product_options_inventory_product_data', 'cfwc_create_custom_field2' );

	/**
	 * Save the custom field
	 * @since 1.0.0
	 */
	function cfwc_save_custom_field2( $post_id ) {
		$product2 = wc_get_product( $post_id );
		$title2 = isset( $_POST['custom_text_field_title2'] ) ? $_POST['custom_text_field_title2'] : '';
		$product2->update_meta_data( 'custom_text_field_title2', sanitize_text_field( $title2 ) );
		$product2->save();
	}
	add_action( 'woocommerce_process_product_meta', 'cfwc_save_custom_field2' );

	/**
	 * Display custom field on the front end
	 * @since 1.0.0
	 */
	function cfwc_display_custom_field2() {
		global $post;
		// Check for the custom field value
		$product2 = wc_get_product( $post->ID );
		$title2 = $product2->get_meta( 'custom_text_field_title2' );
		if( $title2 != "" ) {
			echo '<div class="cfwc-custom-field-wrapper"><input type="hidden" id="cfwc-title-field2" name="cfwc-title-field2" value="'.$title2.'"></div>';
			// Only display our field if we've got a value for the field title
			// printf(
			// 	'<div class="cfwc-custom-field-wrapper"><label for="cfwc-title-field">%s</label><input type="text" id="cfwc-title-field" name="cfwc-title-field" value=""></div>',
			// );
		}
	}
	add_action( 'woocommerce_before_add_to_cart_button', 'cfwc_display_custom_field2' );

	/**
	 * Validate the text field
	 * @since 1.0.0
	 * @param Array 		$passed					Validation status.
	 * @param Integer   $product_id     Product ID.
	 * @param Boolean  	$quantity   		Quantity
	 */
	function cfwc_validate_custom_field2( $passed, $product_id, $quantity ) {
		if( empty( $_POST['cfwc-title-field2'] ) ) {
			// Fails validation
			$passed = false;
			wc_add_notice( __( 'Please enter a value into the text field', 'wcpf' ), 'error' );
		}
		return $passed;
	}
	add_filter( 'woocommerce_add_to_cart_validation', 'cfwc_validate_custom_field2', 10, 3 );

	/**
	 * Add the text field as item data to the cart object
	 * @since 1.0.0
	 * @param Array 	$cart_item_data Cart item meta data.
	 * @param Integer   $product_id     Product ID.
	 * @param Integer   $variation_id   Variation ID.
	 * @param Boolean  	$quantity   		Quantity
	 */
	function cfwc_add_custom_field_item_data2( $cart_item_data, $product_id, $variation_id, $quantity ) {
		if( ! empty( $_POST['cfwc-title-field2'] ) ) {
			// Add the item data
			$cart_item_data['title_field2'] = $_POST['cfwc-title-field2'];
			$product2 = wc_get_product( $product_id ); // Expanded function
			$price2 = $product2->get_price(); // Expanded function
			$cart_item_data['total_price2'] = $price2; // Expanded function
		}
		return $cart_item_data;
	}
	add_filter( 'woocommerce_add_cart_item_data', 'cfwc_add_custom_field_item_data2', 10, 4 );

	/**
	 * Update the price in the cart
	 * @since 1.0.0
	 */
	function cfwc_before_calculate_totals2( $cart_obj ) {
	  if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
	    return;
	  }
	  // Iterate through each cart item
	  foreach( $cart_obj->get_cart() as $key=>$value ) {
	    if( isset( $value['total_price2'] ) ) {
	      $price2 = $value['total_price2'];
	      $value['data']->set_price( ( $price2 ) );
	    }
	  }
	}
	add_action( 'woocommerce_before_calculate_totals', 'cfwc_before_calculate_totals2', 10, 1 );

	// /**
	//  * Display the custom field value in the cart
	//  * @since 1.0.0
	//  */
	// function cfwc_cart_item_name2( $name, $cart_item, $cart_item_key ) {
	// 	if( isset( $cart_item['title_field2'] ) ) {
	// 	  $name .= sprintf(
	// 			'<p>%s</p>',
	// 			esc_html( $cart_item['title_field2'] )
	// 		);
	// 	}
	// 	return $name;
	// }
	// add_filter( 'woocommerce_cart_item_name', 'cfwc_cart_item_name2', 10, 3 );

	/**
	 * Add custom field to order object
	 */
	function cfwc_add_custom_data_to_order2( $item, $cart_item_key, $values, $order ) {
		foreach( $item as $cart_item_key=>$values ) {
			if( isset( $values['title_field2'] ) ) {
				$item->add_meta_data( __( 'gl_code', 'wcpf' ), $values['title_field2'], true );
			}
		}
	}
	add_action( 'woocommerce_checkout_create_order_line_item', 'cfwc_add_custom_data_to_order2', 10, 4 );