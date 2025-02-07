<?php
/*
Plugin Name: Shopp Admin Extras
Plugin URI:
Description: Adds navigation links on the Orders page and allows you to edit the order status inside the Order page. Also allows you to change the comment and ping status of all Shopp products.
Version: 1.1
Author: Chris Runnells
Author URI: http://chrisrunnells.com
License: GPLv2 or later

/*
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined( 'ABSPATH' ) or die( 'Nope!' );

class ShoppAdminExtras {
	public $dir = '';
	public static $url = '';

	public static function init () {
		add_action( 'plugins_loaded', array( __CLASS__, 'loader' ) );
	}

	public static function loader () {
		if (defined('SHOPP_VERSION') and version_compare(SHOPP_VERSION, '1.2') >= 0)
			new ShoppAdminExtras;
	}

	function __construct () {

		if ( ! is_admin() ) return;

		$this->dir = dirname(__FILE__);
		self::$url = WP_PLUGIN_URL.'/'.basename($this->dir);

		require $this->dir.'/inc/admin.php';

		add_action( 'shopp_order_management_scripts', array( $this, 'save_order_status' ) );
		add_action( 'shopp_order_admin_script', array( $this, 'order_number_navigation' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_init', array( $this, 'meta_box' ) );
		add_filter( 'admin_menu', array( $this, 'register_setting_page'), 50);
	}

	function  meta_box () {
		add_meta_box( 'shopp_order_status', __( 'Order Status' ), array( $this, 'order_status_box' ), 'toplevel_page_shopp-orders', 'side', 'high' );
	}

	function register_setting_page() {
		shopp_admin_add_submenu( 'Extras', 'shopp-admin-extras', 'shopp-setup',
			array( 'ShoppExtrasAdmin', 'controller' ), 'shopp_settings' );
	}

	function admin_styles () {
		$screen = get_current_screen();
		if ( 'toplevel_page_shopp-orders' != $screen->base ) return;

		echo '<style type="text/css">';
		echo '.shopp_navlinks { display: block; clear: both; height: 1.5em; } ';
		echo '.prevlink { display:block; float:left; } ';
		echo '.nextlink { display:block; float:right; } ';
		echo '#shopp_order_status .button-secondary { float: right; } ';
		echo '</style>'."\n";
	}

	/* Prints the box content */
	function order_status_box () {

		// snag the order status from the order object
		$Purchase = ShoppPurchase();
		$status = $Purchase->status;
		$statusLabels = shopp_setting('order_status');

		echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '?id=' . $_GET['id'] . '&page=' . $_GET['page'] . '">';
		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), 'order_status_noncename' );
		// create a select menu of Order Status'
		echo '<select name="newstatus" id="newstatus">';
		echo menuoptions($statusLabels,$status,true);
		echo '</select> ';

		echo '<button id="update-status-button" class="button-secondary" value="update" type="submit">Update</button></form>';

	}

	/* When the post is saved, saves our custom data */
	function save_order_status () {
		if ( ! current_user_can('shopp_products') ) return;

		// Check if the user intended to change this value.
		if ( ! isset( $_POST['order_status_noncename'] ) || ! wp_verify_nonce( $_POST['order_status_noncename'], plugin_basename( __FILE__ ) ) )
			return;

		//sanitize user input
		$newstatus = sanitize_text_field( $_POST['newstatus'] );
		$order_id = sanitize_text_field( $_GET['id'] );

		$Purchase = new Purchase($order_id);
		$Purchase->status = $newstatus;
		$Purchase->save();

	}


	// check the current order number, and return the next and previous order numbers
	function order_number_navigation () {
		// We'll have to inject the links via JS since there aren't any easy hooks to insert content with
		$prevlink = '';
		$nextlink = '';

		// setup the link URLs
		$Purchase = ShoppPurchase();
		$url = add_query_arg('page','shopp-orders', admin_url('admin.php') );

		// Get the next and previous ID numbers
		$prev = $this->prev_order_number();
		$next = $this->next_order_number();

		if ( ! empty( $prev ) ) {
			$prevurl = add_query_arg('id',$prev,$url);
			$prevlink = '<a href="'. esc_url($prevurl) . '" title="' . __( 'View Order', 'shopp-admin-extras' ) . ' #' . $prev . '" class="prevlink">&laquo; ' . __( 'Previous Order', 'shopp-admin-extras' ) . ' #'. $prev .'</a> ';
		}

		if ( ! empty( $next ) ) {
			$nexturl = add_query_arg('id',$next,$url);
			$nextlink = ' <a href="'. esc_url($nexturl) . '" title="' . __( 'View Order', 'shopp-admin-extras' ) . ' #' . $next . '" class="nextlink">' . __( 'Next Order', 'shopp-admin-extras' ) . ' #'. $next .' &raquo;</a>';
		}

		$output = '<div class="shopp_navlinks">' . $prevlink . $nextlink . '</div>';

		echo "	$('#order').prepend('" . $output . "');\n";
	}

	function next_order_number () {
		global $wpdb;

		$Purchase = ShoppPurchase();
		if ( empty( $Purchase->id ) ) return;
		$id = $Purchase->id;

		$purchase_table = DatabaseObject::tablename('purchase');
		$query = "SELECT id FROM $purchase_table WHERE id > $id AND status = 0 ORDER BY id ASC LIMIT 0, 1";
		$next_id = $wpdb->get_var($query);

		return $next_id;
	}

	function prev_order_number () {
		global $wpdb;

		$Purchase = ShoppPurchase();
		if ( empty( $Purchase->id ) ) return;
		$id = $Purchase->id;

		$purchase_table = DatabaseObject::tablename('purchase');
		$query = "SELECT id FROM $purchase_table WHERE id < $id AND status = 0 ORDER BY id DESC LIMIT 0, 1";
		$prev_id = $wpdb->get_var($query);

		return $prev_id;
	}

	// Updates comment and ping statuses for Shopp products
	function change_status () {

		$shopp_comments = $_POST['shopp_comments'];
		$shopp_pings = $_POST['shopp_pings'];

		$changes = array();

		if ( 'Enable' == $shopp_comments ){
			$changes[] = 'comment_status = "open"';
		} else if ( 'Disable' == $shopp_comments ) {
			$changes[] = 'comment_status = "closed"';
		}

		if ( 'Enable' == $shopp_pings ){
			$changes[] = 'ping_status = "open"';
		} else if ( 'Disable' == $shopp_pings ) {
			$changes[] = 'ping_status = "closed"';
		}

		if ( ! empty( $changes ) ){
			global $wpdb;

			$change_status = implode(', ', $changes);

			$product_table = WPDatabaseObject::tablename(ShoppProduct::$table);
			$query = "UPDATE $product_table SET $change_status WHERE post_type = \"shopp_product\"";
			$result = $wpdb->query( $query );
			return $result;
		} else {
			return;
		}

	}

} // end Class

ShoppAdminExtras::init();
