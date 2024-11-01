<?php

class ShoppExtrasAdmin extends ShoppAdminController {

	public $dir;

	public static function controller () {
		new self();
	}

	public function __construct () {
		$this->dir = dirname(__FILE__);
		$this->form();
	}

	protected function form () {

		$status_message = "";

		if (wp_verify_nonce($_GET['_wpnonce'], 'changestatus')) {
			$status = ShoppAdminExtras::change_status();

			if ( isset( $status ) && $status != '0' ){
				$class = 'notice notice-success';
				$message = __( 'Product settings changed successfully.', 'shopp-admin-extras' );

				$status_message = sprintf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
			}
		}


		$total_comments = $this->total_comments();
		foreach ($total_comments as $total){
			$shopp_comments[$total['comment_status']] = $total['count'];
		}

		$total_pings = $this->total_pings();
		foreach ($total_pings as $total){
			$shopp_pings[$total['ping_status']] = $total['count'];
		}

		$action = wp_nonce_url(
			get_admin_url(null, 'admin.php?page=shopp-admin-extras'),
			'changestatus');

		include $this->dir.'/adminui.php';

	}

	function total_comments () {
		global $wpdb;

		$product_table = WPDatabaseObject::tablename(ShoppProduct::$table);
		$query = "SELECT comment_status, count(*) AS count FROM $product_table WHERE post_type = 'shopp_product' GROUP BY comment_status ORDER BY comment_status";
		$result = $wpdb->get_results($query, 'ARRAY_A');

		return $result;
	}

	function total_pings () {
		global $wpdb;

		$product_table = WPDatabaseObject::tablename(ShoppProduct::$table);
		$query = "SELECT ping_status, count(*) AS count FROM $product_table WHERE post_type = 'shopp_product' GROUP BY ping_status ORDER BY ping_status";
		$result = $wpdb->get_results($query, 'ARRAY_A');

		return $result;

	}


}
