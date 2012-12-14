<?php

/*
 *	This is the base acf field frow which
 *	all other fields extend. Here you can 
 *	find every function for your field
 *
 */
 
class acf_Field
{
	var $name;
	var $title;
	var $parent;
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	Constructor
	*	- $parent is passed buy reference so you can play with the acf functions
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function __construct($parent)
	{
		$this->parent = $parent;
	}


	/*--------------------------------------------------------------------------------------
	*
	*	create_field
	*	- called in lots of places to create the html version of the field
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function create_field($field)
	{
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	create_options
	*	- called from core/field_meta_box.php to create special options
	*
	*	@params : 	$key (int) - neccessary to group field data together for saving
	*				$field (array) - the field data from the database
	*	@author Elliot Condon
	*	@since 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function create_options($key, $field)
	{
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_head
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_head()
	{

	}

	
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_print_scripts / admin_print_styles
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_print_scripts()
	{
	
	}
	
	function admin_print_styles()
	{
		
	}

	
	/*--------------------------------------------------------------------------------------
	*
	*	update_value
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function update_value($post_id, $field, $value)
	{
		// strip slashes
		$value = stripslashes_deep($value);
		
		
		// apply filters
		$value = apply_filters('acf_update_value', $value, $field, $post_id );
		
		$keys = array('type', 'name', 'key');
		foreach( $keys as $key )
		{
			if( isset($field[ $key ]) )
			{
				$value = apply_filters('acf_update_value-' . $field[ $key ], $value, $field, $post_id);
			}
		}
				
		
		// if $post_id is a string, then it is used in the everything fields and can be found in the options table
		if( is_numeric($post_id) )
		{
			update_post_meta($post_id, $field['name'], $value);
			update_post_meta($post_id, '_' . $field['name'], $field['key']);
		}
		elseif( strpos($post_id, 'user_') !== false )
		{
			$post_id = str_replace('user_', '', $post_id);
			update_user_meta($post_id, $field['name'], $value);
			update_user_meta($post_id, '_' . $field['name'], $field['key']);
		}
		else
		{
			update_option( $post_id . '_' . $field['name'], $value );
			update_option( '_' . $post_id . '_' . $field['name'], $field['key'] );
		}
		
		
		//clear the cache for this field
		wp_cache_delete('acf_get_field_' . $post_id . '_' . $field['name']);
		
	}
	
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_value
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_value($post_id, $field)
	{
		$value = false;
		
		// if $post_id is a string, then it is used in the everything fields and can be found in the options table
		if( is_numeric($post_id) )
		{
			$value = get_post_meta( $post_id, $field['name'], false );
			
			// value is an array, check and assign the real value / default value
			if( !isset($value[0]) )
			{
				if( isset($field['default_value']) )
				{
					$value = $field['default_value'];
				}
				else
				{
					$value = false;
				}
		 	}
		 	else
		 	{
			 	$value = $value[0];
		 	}
		}
		elseif( strpos($post_id, 'user_') !== false )
		{
			$post_id = str_replace('user_', '', $post_id);
			
			$value = get_user_meta( $post_id, $field['name'], false );
			
			// value is an array, check and assign the real value / default value
			if( !isset($value[0]) )
			{
				if( isset($field['default_value']) )
				{
					$value = $field['default_value'];
				}
				else
				{
					$value = false;
				}
		 	}
		 	else
		 	{
			 	$value = $value[0];
		 	}
		}
		else
		{
			$value = get_option( $post_id . '_' . $field['name'], null );
			
			if( is_null($value) )
			{
				if( isset($field['default_value']) )
				{
					$value = $field['default_value'];
				}
				else
				{
					$value = false;
				}
		 	}

		}
		
		
		// if value was duplicated, it may now be a serialized string!
		$value = maybe_unserialize($value);
		
		
		// apply filters
		$value = apply_filters('acf_load_value', $value, $field, $post_id );
		
		$keys = array('type', 'name', 'key');
		foreach( $keys as $key )
		{
			if( isset($field[ $key ]) )
			{
				$value = apply_filters('acf_load_value-' . $field[ $key ], $value, $field, $post_id);
			}
		}
		
		
		
		return $value;
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_value_for_api
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_value_for_api($post_id, $field)
	{
		return $this->get_value($post_id, $field);
	}
	
}

?>