<?php
if ( ! class_exists( "WP_List_Table" ) ) {
	require_once( ABSPATH . "wp-admin/includes/class-wp-list-table.php" );
}

class Orders_Table extends WP_List_Table {

	private $_items;

	function set_data( $data ) {
		$this->_items = $data;

	}

  function extra_tablenav( $which ) {
		if ( $which == "top" ) : ?>
    <div class="actions alignleft">
      <input type="text" class="custom_date" name="date_from" placeholder = "Date From" value="<?php if(isset($_REQUEST['date_from'])){ echo $_REQUEST['date_from']; } ?>"/>
      <input type="text" class="custom_date" name="date_to" placeholder = "Date To" value="<?php if(isset($_REQUEST['date_to'])){ echo $_REQUEST['date_to']; } ?>"/>
      <?php
			  submit_button(__('Filter','wcpf'),'button','submit',false);
			?>
    </div>
		<?php
    
      $path = wp_upload_dir();   // or where ever you want the file to go
      $outstream = fopen($path['path']."/shippinglabels.csv", "w");  // the file name you choose
  
      $fields = array('Date', 'Account', 'GoodGuy', 'Invoice', 'Class', 'GL Code', 'Item', 'Description', 'Sold By', 'Pay Method', 'Pay Note', 'Item Sold', 'Unit Price', 'Total Price', 'Payment');  // the oder header information you want in the csv file

      fputcsv($outstream, $fields);  //creates the first line in the csv file
      
      // global $wpdb;
		  // $order_ids = $wpdb->get_results("SELECT order_id FROM wp_woocommerce_order_items ORDER BY order_id DESC");
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
      //var_dump($order_id);
		  $date = date_create($order->get_date_created());
		  $date = date_format($date,"Y-m-d");
        if ($order_id != 0) :
          $get_items = $order->get_items();
          $customer_id = $order->get_customer_id();
          $customer_name = $order->get_formatted_billing_full_name();
          $subtotal = $order->get_subtotal();
          // Get and Loop Over Order Items
          $user = $order->get_user();
          $pay_note = $order->get_customer_note();

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
            $unit_price = $product_instance->get_price();
            $payment = $item->get_total();
          }
          //var_dump($item);

          $s_data = [
            'date' => $date,
            'customer_name'	=> $customer_name,
            'customer_id' => $order->get_user_id(),
            'order_id' => $order_id,
            'product_class' => $product_class,
            'gl_code' => $gl_code,
            'item' => $item->get_name(),
            'short_desc' => $isMember . ' membership (' . $item->get_name() . ') for ' . $order->get_billing_last_name() . ', ' . $order->get_billing_first_name(),
            'sold_by' => 'NA',
            'pay_method' => 'e-commerce',
            'pay_note' => $pay_note,
            'quantity' => $quantity,
            'unit_price' => "$ ".$unit_price,
            'subtotal' => "$ ".$subtotal,
            'payment' => "$ ".$payment,
          ];
          // echo $s_data['date'];
          // var_dump($s_data);
          $date_from = $_REQUEST['date_from'];
		      $date_to = $_REQUEST['date_to'];
          if( $date_from <= $s_data['date'] && $date_to >= $s_data['date'] ){
            if ( isset( $_REQUEST['date_from'] ) && !empty($_REQUEST['date_from']) ) {
              $data = array_filter($s_data);
              //var_dump($data);
              array_push($data, $s_data);
              fputcsv($outstream, $s_data);  //output the order info line to the csv file
            }
          }
        endif;
		  endforeach;

      fclose($outstream);
      echo '<a class="button" href="'.$path['url'].'/shippinglabels.csv">CSV Export</a>';
      echo '<a class="button" style="margin-left: 10px;" href="'.site_url().'/wp-admin/admin.php?page=order_reports">Clear Filter</a>';
    ?>
		<?php endif;
	}

	function get_columns() {
		return [
      'date'  => __('Date', 'wcpf'),
      'customer_name' => __( 'Account', 'wcpf' ),
      'customer_id' => __('GoodGuy #', 'wcpf'),
			'order_id'  => __( 'Invoice', 'wcpf' ),
      'product_class'   => __( 'Class', 'wcpf' ),
      'gl_code'   => __( 'GL Code', 'wcpf' ),
      'item'   => __( 'Item', 'wcpf' ),
      'short_desc'   => __( 'Description', 'wcpf' ),
      'sold_by' => __('Sold By', 'wcpf'),
      'pay_method' => __('Pay Method', 'wcpf'),
      'pay_note' => __('Pay Note', 'wcpf'),
      'quantity'   => __( 'Item Sold', 'wcpf' ),
      'unit_price'   => __( 'Unit Price', 'wcpf' ),
      'subtotal'   => __( 'Total Price', 'wcpf' ),
      'payment'   => __( 'Payment', 'wcpf' ),
		];
	}


	function prepare_items() {
		$paged                 = $_REQUEST['paged'] ?? 1;
		$per_page              = 15;
		$total_items           = count( $this->_items );
		$this->_column_headers = array( $this->get_columns(), array(),$this->get_sortable_columns() );
		$data_chunks           = array_chunk( $this->_items, $per_page );
		$this->items           = $data_chunks[ $paged - 1 ];
		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( count( $this->_items ) / $per_page )
		] );
	}

	function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}


}