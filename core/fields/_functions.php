<?php

/*
*  Field Functions
*
*  @description: The API for all fields
*  @since: 3.6
*  @created: 23/01/13
*/

class acf_field_functions
{
	
	/*
	*  __construct
	*
	*  @description: 
	*  @since 3.1.8
	*  @created: 23/06/12
	*/
	
	function __construct()
	{
		//value
		add_filter('acf/load_value', array($this, 'load_value'), 5, 3);
		add_action('acf/update_value', array($this, 'update_value'), 5, 4);
		add_action('acf/delete_value', array($this, 'delete_value'), 5, 2);
		add_action('acf/format_value', array($this, 'format_value'), 5, 3);
		add_action('acf/format_value_for_api', array($this, 'format_value_for_api'), 5, 3);
		
		
		// field
		add_filter('acf/load_field', array($this, 'load_field'), 5, 3);
		add_action('acf/update_field', array($this, 'update_field'), 5, 2);
		add_action('acf/delete_field', array($this, 'delete_field'), 5, 2);
		add_action('acf/create_field', array($this, 'create_field'), 5, 1);
		add_action('acf/create_field_options', array($this, 'create_field_options'), 5, 1);
		
		
		// extra
		add_filter('acf/load_field_defaults', array($this, 'load_field_defaults'), 5, 1);
	}
	
	
	/*
	*  load_value
	*
	*  @description: loads basic value from the db
	*  @since: 3.6
	*  @created: 23/01/13
	*/
	
	function load_value($value, $post_id, $field)
	{
		$cache = wp_cache_get( 'load_value/post_id=' . $post_id . '/name=' . $field['name'], 'acf', false, $found );
		
		if( $found )
		{
			return $cache;
		}
		
		
		// set default value
		$value = false;
		
		
		// if $post_id is a string, then it is used in the everything fields and can be found in the options table
		if( is_numeric($post_id) )
		{
			$v = get_post_meta( $post_id, $field['name'], false );
			
			// value is an array
			if( isset($v[0]) )
			{
			 	$value = $v[0];
		 	}

		}
		elseif( strpos($post_id, 'user_') !== false )
		{
			$post_id = str_replace('user_', '', $post_id);
			
			$v = get_user_meta( $post_id, $field['name'], false );
			
			// value is an array
			if( isset($v[0]) )
			{
			 	$value = $v[0];
		 	}
		 	
		}
		else
		{
			$v = get_option( $post_id . '_' . $field['name'], false );
			
			if( !is_null($value) )
			{
				$value = $v;
		 	}
		}
		
		
		// no value?
		if( $value === false )
		{
			if( isset($field['default_value']) && $field['default_value'] !== "" )
			{
				$value = $field['default_value'];
			}
		}
		
		
		// if value was duplicated, it may now be a serialized string!
		$value = maybe_unserialize($value);
		
		
		// apply filters
		foreach( array('type', 'name', 'key') as $key )
		{
			// run filters
			$value = apply_filters('acf/load_value/' . $key . '=' . $field[ $key ], $value, $post_id, $field); // new filter
		}
		
		
		//update cache
		wp_cache_set( 'load_value/post_id=' . $post_id . '/name=' . $field['name'], $value, 'acf' );

		
		return $value;
	}
	
	
	/*
	*  format_value
	*
	*  @description: uses the basic value and allows the field type to format it
	*  @since: 3.6
	*  @created: 26/01/13
	*/
	
	function format_value( $value, $post_id, $field )
	{
		$value = apply_filters('acf/format_value/type=' . $field['type'], $value, $post_id, $field);
		
		return $value;
	}
	
	
	/*
	*  format_value_for_api
	*
	*  @description: uses the basic value and allows the field type to format it or the api functions
	*  @since: 3.6
	*  @created: 26/01/13
	*/
	
	function format_value_for_api( $value, $post_id, $field )
	{
		$value = apply_filters('acf/format_value_for_api/type=' . $field['type'], $value, $post_id, $field);
		
		return $value;
	}
	
	
	/*
	*  update_value
	*
	*  updates a value into the db
	*
	*  @type	action
	*  @date	23/01/13
	*
	*  @param	{mixed}		$value		the value to be saved
	*  @param	{int}		$post_id 	the post ID to save the value to
	*  @param	{array}		$field		the field array
	*  @param	{boolean}	$exact		allows the update_value filter to be skipped
	*  @return	N/A
	*/
	
	function update_value( $value, $post_id, $field, $exact = false )
	{
	
		// strip slashes
		// - not needed? http://support.advancedcustomfields.com/discussion/3168/backslashes-stripped-in-wysiwyg-filed
		//if( get_magic_quotes_gpc() )
		//{
			$value = stripslashes_deep($value);
		//}
		
		
		// apply filters
		if( !$exact )
		{
			foreach( array('key', 'name', 'type') as $key )
			{
				// run filters
				$value = apply_filters('acf/update_value/' . $key . '=' . $field[ $key ], $value, $post_id, $field); // new filter
			}
		}
		
		
		// if $post_id is a string, then it is used in the everything fields and can be found in the options table
		if( is_numeric($post_id) )
		{
			// allow ACF to save to revision!
			update_metadata('post', $post_id, $field['name'], $value );
			update_metadata('post', $post_id, '_' . $field['name'], $field['key']);
		}
		elseif( strpos($post_id, 'user_') !== false )
		{
			$user_id = str_replace('user_', '', $post_id);
			update_metadata('user', $user_id, $field['name'], $value);
			update_metadata('user', $user_id, '_' . $field['name'], $field['key']);
		}
		else
		{
			// for some reason, update_option does not use stripslashes_deep.
			// update_metadata -> http://core.trac.wordpress.org/browser/tags/3.4.2/wp-includes/meta.php#L82: line 101 (does use stripslashes_deep)
			// update_option -> http://core.trac.wordpress.org/browser/tags/3.5.1/wp-includes/option.php#L0: line 215 (does not use stripslashes_deep)
			$value = stripslashes_deep($value);
			
			update_option( $post_id . '_' . $field['name'], $value );
			update_option( '_' . $post_id . '_' . $field['name'], $field['key'] );
		}
		
		
		// update the cache
		wp_cache_set( 'load_value/post_id=' . $post_id . '/name=' . $field['name'], $value, 'acf' );
		
		
		// update temp cache
		if( isset($GLOBALS['acf_update_values']) )
		{
			$GLOBALS['acf_update_values'][ $field['name'] ] = array(
				'post_id' => $post_id,
				'name' => $field['name'],
				'key' => $field['key']
			);
		}
	}
	
	
	/*
	*  delete_value
	*
	*  @description: deletes a value from the database
	*  @since: 3.6
	*  @created: 23/01/13
	*/
	
	function delete_value( $post_id, $key )
	{
		// if $post_id is a string, then it is used in the everything fields and can be found in the options table
		if( is_numeric($post_id) )
		{
			delete_post_meta( $post_id, $key );
			delete_post_meta( $post_id, '_' . $key );
		}
		elseif( strpos($post_id, 'user_') !== false )
		{
			$post_id = str_replace('user_', '', $post_id);
			delete_user_meta( $post_id, $key );
			delete_user_meta( $post_id, '_' . $key );
		}
		else
		{
			delete_option( $post_id . '_' . $key );
			delete_option( '_' . $post_id . '_' . $key );
		}
		
		wp_cache_delete( 'load_value/post_id=' . $post_id . '/name=' . $key, 'acf' );
	}
	
	
	/*
	*  load_field
	*
	*  @description: loads a field from the database
	*  @since 3.5.1
	*  @created: 14/10/12
	*/
	
	function load_field( $field, $field_key, $post_id = false )
	{
		// load cache
		if( !$field )
		{
			$field = wp_cache_get( 'load_field/key=' . $field_key, 'acf' );
		}
		
		
		// load from DB
		if( !$field )
		{
			// vars
			global $wpdb;
			
			
			// get field from postmeta
			$sql = $wpdb->prepare("SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = %s", $field_key);
			
			if( $post_id )
			{
				$sql .= $wpdb->prepare("AND post_id = %d", $post_id);
			}
	
			$rows = $wpdb->get_results( $sql, ARRAY_A );
			
			
			
			// nothing found?
			if( !empty($rows) )
			{
				$row = $rows[0];
				
				
				/*
				*  WPML compatibility
				*
				*  If WPML is active, and the $post_id (Field group ID) was not defined,
				*  it is assumed that the load_field functio has been called from the API (front end).
				*  In this case, the field group ID is never known and we can check for the correct translated field group
				*/
				
				if( defined('ICL_LANGUAGE_CODE') && !$post_id )
				{
					$wpml_post_id = icl_object_id($row['post_id'], 'acf', true, ICL_LANGUAGE_CODE);
					
					foreach( $rows as $r )
					{
						if( $r['post_id'] == $wpml_post_id )
						{
							// this row is a field from the translated field group
							$row = $r;
						}
					}
				}
				
				
				// return field if it is not in a trashed field group
				if( get_post_status( $row['post_id'] ) != "trash" )
				{
					$field = $row['meta_value'];
					$field = maybe_unserialize( $field );
					$field = maybe_unserialize( $field ); // run again for WPML
				}
				
				
				// add field_group ID
				$field['field_group'] = $row['post_id'];
				
			}
		}
		
		
		// apply filters
		$field = apply_filters('acf/load_field_defaults', $field);
		
		
		// apply filters
		foreach( array('type', 'name', 'key') as $key )
		{
			// run filters
			$field = apply_filters('acf/load_field/' . $key . '=' . $field[ $key ], $field); // new filter
		}
		
	
		// set cache
		wp_cache_set( 'load_field/key=' . $field_key, $field, 'acf' );
		
		return $field;
	}
	
	
	/*
	*  load_field_defaults
	*
	*  @description: applies default values to the field after it has been loaded
	*  @since 3.5.1
	*  @created: 14/10/12
	*/
	
	function load_field_defaults( $field )
	{
		// validate $field
		if( !is_array($field) )
		{
			$field = array();
		}
		
		
		// defaults
		$defaults = array(
			'key' => '',
			'label' => '',
			'name' => '',
			'type' => 'text',
			'order_no' => 1,
			'instructions' => '',
			'required' => 0,
			'id' => '',
			'class' => '',
			'conditional_logic' => array(
				'status' => 0,
				'allorany' => 'all',
				'rules' => 0
			),
		);
		$field = array_merge($defaults, $field);
		
		
		// Parse Values
		$field = apply_filters( 'acf/parse_types', $field );
		
		
		// field specific defaults
		$field = apply_filters('acf/load_field_defaults/type=' . $field['type'] , $field);
				
		
		// class
		if( !$field['class'] )
		{
			$field['class'] = $field['type'];
		}
		
		
		// id
		if( !$field['id'] )
		{
			$id = $field['name'];
			$id = str_replace('][', '_', $id);
			$id = str_replace('fields[', '', $id);
			$id = str_replace('[', '-', $id); // location rules (select) does'nt have "fields[" in it
			$id = str_replace(']', '', $id);
			
			$field['id'] = 'acf-field-' . $id;
		}
		
		
		// return
		return $field;
	}
	
	
	/*
	*  update_field
	*
	*  @description: updates a field in the database
	*  @since: 3.6
	*  @created: 24/01/13
	*/
	
	function update_field( $field, $post_id )
	{
		// sanitize field name
		// - http://support.advancedcustomfields.com/discussion/5262/sanitize_title-on-field-name
		// - issue with camel case! Replaced with JS
		//$field['name'] = sanitize_title( $field['name'] );
		
		
		// filters
		$field = apply_filters('acf/update_field/type=' . $field['type'], $field, $post_id ); // new filter
		
		
		// save
		update_post_meta( $post_id, $field['key'], $field );
	}
	
	
	/*
	*  delete_field
	*
	*  @description: deletes a field in the database
	*  @since: 3.6
	*  @created: 24/01/13
	*/
	
	function delete_field( $post_id, $field_key )
	{
		// delete
		delete_post_meta($post_id, $field_key);
	}
	
	
	/*
	*  create_field
	*
	*  @description: renders a field into a HTML interface
	*  @since: 3.6
	*  @created: 23/01/13
	*/
	
	function create_field( $field )
	{
		// load defaults
		// if field was loaded from db, these default will already be appield
		// if field was written by hand, it may be missing keys
		$field = apply_filters('acf/load_field_defaults', $field);
		
		
		// create field specific html
		do_action('acf/create_field/type=' . $field['type'], $field);
		
		
		// conditional logic
		// - isset is needed for the edit field group page where fields are created without many parameters
		if( $field['conditional_logic']['status'] ):
			
			$join = ' && ';
			if( $field['conditional_logic']['allorany'] == "any" )
			{
				$join = ' || ';
			}
			
			?>
<script type="text/javascript">
(function($){
	
	// create the conditional function
	$(document).live('acf/conditional_logic/<?php echo $field['key']; ?>', function(){
		
		var field = $('.field_key-<?php echo $field['key']; ?>');

<?php

		$if = array();
		foreach( $field['conditional_logic']['rules'] as $rule )
		{
			$if[] = 'acf.conditional_logic.calculate({ field : "'. $field['key'] .'", toggle : "' . $rule['field'] . '", operator : "' . $rule['operator'] .'", value : "' . $rule['value'] . '"})' ;
		}
		
?>
		if(<?php echo implode( $join, $if ); ?>)
		{
			field.removeClass('acf-conditional_logic-hide').addClass('acf-conditional_logic-show');
		}
		else
		{
			field.removeClass('acf-conditional_logic-show').addClass('acf-conditional_logic-hide');
		}
		
	});
	
	
	// add change events to all fields
<?php 

$already_added = array();

foreach( $field['conditional_logic']['rules'] as $rule ): 

	if( in_array( $rule['field'], $already_added) )
	{
		continue;
	}
	else
	{
		$already_added[] = $rule['field'];
	}
	
	?>
	$('.field_key-<?php echo $rule['field']; ?> *[name]').live('change', function(){
		$(document).trigger('acf/conditional_logic/<?php echo $field['key']; ?>');
	});
<?php endforeach; ?>
	
	$(document).live('acf/setup_fields', function(e, postbox){
		$(document).trigger('acf/conditional_logic/<?php echo $field['key']; ?>');
	});
		
})(jQuery);
</script>
			<?php
		endif;
	}
	
	
	/*
	*  create_field_options
	*
	*  @description: renders a field into a HTML interface
	*  @since: 3.6
	*  @created: 23/01/13
	*/
	
	function create_field_options($field)
	{
		// load standard + field specific defaults
		$field = apply_filters('acf/load_field_defaults', $field);
		
		// render HTML
		do_action('acf/create_field_options/type=' . $field['type'], $field);
	}

	
	
}

new acf_field_functions();

?>