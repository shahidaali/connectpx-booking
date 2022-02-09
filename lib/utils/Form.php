<?php
namespace ConnectpxBooking\Lib\Utils;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    ConnectpxBooking
 * @subpackage ConnectpxBooking\Lib\Utils
 * @author     Shahid Hussain <shahidhussainaali@gmail.com>
 */
class Form {

	/**
	 *
	 * @since    1.0.0
	 */
	public static function old( $field, $data = [], $default = null ) {
		$old = array_merge($data, $_REQUEST);
		return self::nestedValue($old, $field, $default);
	}

	/**
	 *
	 * @since    1.0.0
	 */
	public static function nestedValue($array, $search_array, $default = null) {
	    if( !is_array($array) ) 
	        return $default;

	    if( ! is_array($search_array) ) 
	        return (isset($array[ $search_array ])) ? $array[ $search_array ] : $default;;

	    $found_value = null;
	    if(is_array($search_array)) {
	        foreach($search_array as $search) {
	            if(is_array($array) && array_key_exists($search, $array)) {
	                $found_value = $array[ $search ];
	                $array = $found_value;
	            } else {
	                $found_value = null;
	                break;
	            }
	        }
	    }

	    return ($found_value) ? $found_value : $default;
	}

	/**
	 *
	 * @since    1.0.0
	 */
	public static function isChecked( $value, $compare = 1 ) {
		return ($value == $compare) ? 'checked="checked"' : '';
	}
	
	/**
	 *
	 * @since    1.0.0
	 */
	public static function selectOptions($rows, $selected_option = null, $empty_lable = "", $use_key = true) {
		if( !is_array($rows) ) return;

	    $options = "";

	    // Selected value to array for multiple values
	    if($selected_option && !is_array($selected_option)) {
	        $selected_option = array($selected_option);
	    }

	    // Empty label
	    if( $empty_lable != "" ) {
	        $options .= "<option value=\"\">{$empty_lable}</option>";
	    }

	    // Creaye options from array
	    foreach ($rows as $key => $value) {
	        $value_item = ($use_key) ? $key : $value;
	        $selected = (!empty($selected_option) && in_array($value_item, $selected_option)) ? 'selected="selected"' : "";

	        $options .= "<option value=\"{$value_item}\" {$selected}>{$value}</option>";
	    }
	    return $options;
	}

}
