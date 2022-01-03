<?php
	/*
	  Plugin Name: WC Authorize Addon
	  Plugin URI:
	  Description: This plugin will modify  the authorize payment data and generate a custom reports for wc
	  Version: 1.0.0
	  Author: Good Guys Market Place
	  Author URI: https://goodguysmarketplace.com/
	  License: GPLv2 or later
	  Text Domain: wcpf
	*/
  
	include( plugin_dir_path( __FILE__ ) . 'inc/class.orders-table.php');
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	function wcpf_load_textdomain() {
		load_plugin_textdomain( 'wcpf', false, dirname( __FILE__ ) . "/languages" );
	}
	
	add_action( "plugins_loaded", "wcpf_load_textdomain" );

	// register jquery and style on initialization
	function register_script() {
		wp_register_style( 'wcpf_style', plugins_url('/css/style.css', __FILE__), false, time(), 'all');

	}
	add_action('init', 'register_script');

	// use the registered jquery and style above
	function enqueue_style(){
		wp_enqueue_style( 'wcpf_style' );
 	}
	add_action('wp_enqueue_scripts', 'enqueue_style');

	function wpdocs_selectively_enqueue_admin_script( $hook ) {

		wp_enqueue_style('wcpf_style_date_ui', '//code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css');

		wp_enqueue_script( 'wcpf_script_jquery_ui', '//code.jquery.com/ui/1.13.0/jquery-ui.js' );
    wp_enqueue_script( 'wcpf_script', plugins_url('/js/main.js', __FILE__) );
	}
	add_action( 'admin_enqueue_scripts', 'wpdocs_selectively_enqueue_admin_script' );

	// File Includes
	include( plugin_dir_path( __FILE__ ) . 'inc/product_class.php');
	include( plugin_dir_path( __FILE__ ) . 'inc/gl_code.php');

	// My Order Menu
  function register_my_custom_submenu_page() {
    add_submenu_page( 'woocommerce', 'Order Reports', 'Order Reports', 'manage_options', 'order_reports', 'order_reports' ); 
	}
	add_action('admin_menu', 'register_my_custom_submenu_page',99);

	function datatable_search_by_name( $item ) {
		$name        = strtolower( $item['order_id'] );
		$search_name = sanitize_text_field( $_REQUEST['s'] );
		if ( strpos( $name, $search_name ) !== false ) {
			return true;
		}
	
		return false;
	}

	function datatable_filter_date($item){
    $date_from = $_REQUEST['date_from'];
		$date_to = $_REQUEST['date_to'];
		if( $date_from <= $item['date2'] && $date_to >= $item['date2'] ){
			return true;
		}
    return false;
	}

	function order_reports() {
		include_once ( plugin_dir_path( __FILE__ ) . 'inc/dataset.php');
		$orderby = $_REQUEST['orderby'] ?? '';
		$order   = $_REQUEST['order'] ?? '';
		if ( isset( $_REQUEST['s'] ) && !empty($_REQUEST['s']) ) {
			$data = array_filter( $data, 'datatable_search_by_name' );
		}

		if ( isset( $_REQUEST['date_from'] ) && !empty($_REQUEST['date_from']) ) {
			$data = array_filter( $data, 'datatable_filter_date' );
		}

		$table = new Orders_Table();

		$table->set_data( $data );
		$table->prepare_items();
	?>
		<div class="wrap">
			<h2><?php _e( "Order Reports", "wcpf" ); ?></h2>
			
			<form method="GET">
				<?php
					$table->search_box( 'search', 'order_id' );
					$table->display();
				?>
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>">
			</form>
		</div>
	<?php
	}
	



