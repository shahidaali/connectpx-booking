<?php
namespace ConnectpxBooking\Backend\Wp\Tables;

use ConnectpxBooking\Lib;

if( ! class_exists( '\WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    ConnectpxBooking
 * @subpackage ConnectpxBooking\Backend
 * @author     Shahid Hussain <shahidhussainaali@gmail.com>
 */
class Services extends \WP_List_Table {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	/** Class constructor */
	public function __construct() {

		parent::__construct([
			'singular' => __( 'Service', 'connectpx_booking' ),
			'plural' => __( 'Services', 'connectpx_booking' ),
			'ajax' => false
		]);
	}

	function _sub_service_cols( $values = [] ){
		$columns = [];

		foreach (Lib\Utils\Common::getSubServices() as $key => $sub_service):
			$enabled = isset($values[$key]['enabled']) && $values[$key]['enabled'] == 'yes' ? 1 : 0;
			if($enabled) {
				$fields = [];
				foreach (Lib\Utils\Common::getSubServicesFields() as $field):
					$fields[] = "<strong>" . $field['label'] . ":</strong> " . (isset($values[$key][$field['name']]) ? $values[$key][$field['name']] : 0);
				endforeach;
				$columns["sub_services_" . $key] = implode("<br>", $fields);
			} else {
				$columns["sub_services_" . $key] = __('Disabled', 'connectpx_booking');
			}
		endforeach;

		return $columns;
	}

	function get_columns(){
		$columns = array(
			'title' => __('Title'),
		);

		foreach (Lib\Utils\Common::getSubServices() as $key => $sub_service):
			$columns["sub_services_" . $key] = $sub_service['title'];
		endforeach;
		
		return $columns;
	}

	function column_default( $item, $column_name ) {
		if( strpos($column_name, 'sub_services_') !== false ) {
			$sub_service_cols = $this->_sub_service_cols( $item['sub_services'] );
			return $sub_service_cols[$column_name];
		}

		switch( $column_name ) { 
			case 'id':
			case 'title':
				return $item[$column_name];
			case 'sub_services':
				return $this->_sub_service_cols($item['sub_services']);
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	* Handles data query and filter, sorting, and pagination.
	*/
	function prepare_items() {
		$query = Lib\Entities\Service::query( 's' )
                    ->select( 's.*' );
        $total_items = $query->count();

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = [];

		$this->_column_headers = array($columns, $hidden, $sortable);

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page = $this->get_items_per_page( 'services_per_page', 5 );
		$current_page = $this->get_pagenum() * $per_page;

		// $query->limit( $per_page )->offset( $current_page );

		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page' => $per_page
		]);

		$items = [];
		foreach ($query->fetchArray() as $item) {
			$items[] = [
				'id' => $item['id'],
				'title' => $item['title'],
				'sub_services' => json_decode($item['sub_services'], true),
			];
		}
		$this->items = $items;
	}

	function column_title($item) {
	  	$actions = array(
            'edit'      => sprintf('<a href="?page=%s&tab=%s&id=%s">Edit</a>',$_REQUEST['page'], 'edit', $item['id']),
            'delete'    => sprintf('<a href="?page=%s&tab=%s&book=%s">Delete</a>',$_REQUEST['page'], 'delete', $item['id']),
        );

	  	return sprintf('%1$s %2$s', sprintf('<a href="?page=%s&tab=%s&id=%s">%s</a>', $_REQUEST['page'], 'edit', $item['id'], $item['title']), $this->row_actions($actions) );
	}
}
