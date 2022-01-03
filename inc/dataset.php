<?php
$args = array(
  'limit' => -1,
  'return' => 'ids',
 );
$query = new WC_Order_Query( $args );
$orders = $query->get_orders();
$data = [];
foreach ($orders  as $order_id):
  $order = wc_get_order( $order_id );
  $order_id = $order->get_id();
  $date = date_create($order->get_date_created());
  $date = date_format($date,"F d, Y");
  $date2 = date_create($order->get_date_created());
  $date2 = date_format($date2,"Y-m-d");
  
  if ($order_id != 0) :
    $get_items = $order->get_items();
    $customer_id = $order->get_customer_id();
    $customer_name = $order->get_formatted_billing_full_name();
    $subtotal = $order->get_subtotal_to_display();
    // Get and Loop Over Order Items
    $user = $order->get_user();
    $pay_note = $order->get_customer_note();


    //$coupon_id = $wpdb->get_results("SELECT discount_amount FROM wp_wc_order_coupon_lookup WHERE order_id = $order_id");
    //var_dump($coupon_id);

    //$customer_status = $wpdb->get_results("SELECT returning_customer FROM wp_wc_order_stats WHERE order_id = $order_id");
    //var_dump($customer_status);
	$isMember = 'New';
	if( wc_memberships_is_user_member($customer_id) ) {
		$isMember = 'Renew';
	}

    foreach ($get_items as $item_id => $item) {
      $taxstat = $item->get_tax_status();
      $product_id = $item->get_product_id();
      $quantity = $item->get_quantity();
      $product_class = $item->get_meta( 'product_class');
      $gl_code = $item->get_meta( 'gl_code');
      $product_instance = wc_get_product($product_id);
      $product_short_description = $product_instance->get_short_description();
      $unit_price = wc_price($product_instance->get_price());
      $payment = wc_price($item->get_total());
    }
    //var_dump($item);

    $s_data = [
      'date' => $date,
      'date2' => $date2,
      'customer_id' => $order->get_user_id(),
      'order_id' => $order_id,
      'order_status' => $order->get_status(),
      'customer_name'	=> $customer_name,
      'item' => $item->get_name(),
      'short_desc' => $isMember . ' membership (' . $item->get_name() . ') for ' . $order->get_billing_last_name() . ', ' . $order->get_billing_first_name(),
      'sold_by' => 'NA',
      'pay_method' => 'e-commerce',
      'pay_note' => $pay_note,
      'quantity' => $quantity,
      'unit_price' => $unit_price,
      'subtotal' => $subtotal,
      'payment' => $payment,
      'gl_code' => $gl_code,
      'product_class' => $product_class,
    ];
    array_push($data, $s_data);
  endif;
  endforeach;