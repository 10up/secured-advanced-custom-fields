<?php 

/*
*  third_party
*
*  @description: 
*  @since 3.5.1
*  @created: 23/06/12
*/
 
class acf_third_party 
{

	var $parent,
		$data;
	
	
	/*
	*  __construct
	*
	*  @description: 
	*  @since 3.1.8
	*  @created: 23/06/12
	*/
	
	function __construct($parent)
	{
		// vars
		$this->parent = $parent;
		$this->data['metaboxes'] = array();
		
		
		// Tabify Edit Screen - http://wordpress.org/extend/plugins/tabify-edit-screen/
		add_action('admin_head-settings_page_tabify-edit-screen', array($this,'admin_head_tabify'));
		
		
		// Duplicate Post - http://wordpress.org/extend/plugins/duplicate-post/
		add_action('dp_duplicate_page', array($this, 'dp_duplicate_page'), 11, 2);
		
		
		// Post Type Switcher - http://wordpress.org/extend/plugins/post-type-switcher/
		add_filter('pts_post_type_filter', array($this, 'pts_post_type_filter'));
		
		
		// WordPres Importer
		add_filter('import_post_meta_key', array($this, 'import_post_meta_key'), 10, 1);
		add_action('import_post_meta', array($this, 'import_post_meta'), 10, 3);
		
	}
	
	
	/*
	*  pts_allowed_pages
	*
	*  @description: 
	*  @since 3.5.3
	*  @created: 19/11/12
	*/
	
	function pts_post_type_filter( $args )
	{
		
		// global
		global $typenow;
		
		if( $typenow == "acf" )
		{
			$args = array(
				'public'  => false,
				'show_ui' => true
			);
		}
		
		
		// return
		return $args;
	}
	
	
	/*
	*  admin_head_tabify
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 9/10/12
	*/
	
	function admin_head_tabify()
	{
		// remove ACF from the tabs
		add_filter('tabify_posttypes', array($this, 'tabify_posttypes'));
		
		
		// add acf metaboxes to list
		add_action('tabify_add_meta_boxes' , array($this,'tabify_add_meta_boxes'));
		
	}
	
	
	/*
	*  tabify_posttypes
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 9/10/12
	*/
	
	function tabify_posttypes( $posttypes )
	{
		if( isset($posttypes['acf']) )
		{
			unset( $posttypes['acf'] );
		}
	
		return $posttypes;
	}
	
	
	/*
	*  tabify_add_meta_boxes
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 9/10/12
	*/
	
	function tabify_add_meta_boxes( $post_type )
	{
		// get acf's
		$acfs = apply_filters('acf/get_field_groups', false);
		
		if($acfs)
		{
			foreach($acfs as $acf)
			{
				// add meta box
				add_meta_box(
					'acf_' . $acf['id'], 
					$acf['title'], 
					array($this, 'dummy'), 
					$post_type
				);
				
			}
			// foreach($acfs as $acf)
		}
		// if($acfs)
	}
	
	function dummy(){ /* Do Nothing */ }
	
	
	
	/*
	*  dp_duplicate_page
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 9/10/12
	*/
	
	function dp_duplicate_page( $new_post_id, $old_post_object )
	{
		// only for acf
		if( $old_post_object->post_type != "acf" )
		{
			return;
		}
		
		
		// update keys
		$metas = get_post_custom( $new_post_id );
		
		if( $metas )
		{
			foreach( $metas as $field_key => $field )
			{
				if( strpos($field_key, 'field_') !== false )
				{
					$field = maybe_unserialize($field[0]);
					
					// delete old field
					delete_post_meta($new_post_id, $field_key);

					
					// set new keys (recursive for sub fields)
					$field = $this->create_new_field_keys( $field );
					

					// save it!
					update_post_meta($new_post_id, $field['key'], $field);
					
				}
				// if( strpos($field_key, 'field_') !== false )
			}
			// foreach( $metas as $field_key => $field )
		}
		// if( $metas )
	
	}
	
	
	/*
	*  create_new_field_keys
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 9/10/12
	*/
	
	function create_new_field_keys( $field )
	{
		// get next id
		$next_id = (int) get_option('acf_next_field_id', 1);
		
		
		// update the acf_next_field_id
		update_option('acf_next_field_id', ($next_id + 1) );
		
		
		// update key
		$field['key'] = 'field_' . $next_id;
		
		
		// update sub field's keys
		if( isset( $field['sub_fields'] ) )
		{
			foreach( $field['sub_fields'] as $k => $v )
			{
				$field['sub_fields'][ $k ] = $this->create_new_field_keys( $v );
			}
		}
		
		
		return $field;
	}
	
	
	/*
	*  import_post_meta
	*
	*  @description: 
	*  @since: 3.5.5
	*  @created: 31/12/12
	*/
	
	function import_post_meta_key( $meta_key )
	{
		if( strpos($meta_key, 'field_') !== false )
		{
			$meta_key = 'field_' . $this->parent->get_next_field_id();
		}
		
		return $meta_key;
	}
	
	
	/*
	*  import_post_meta
	*
	*  @description: 
	*  @since: 3.5.5
	*  @created: 1/01/13
	*/
	
	function import_post_meta( $post_id, $key, $value )
	{
		if( strpos($key, 'field_') !== false )
		{
			$value['key'] = $key;
			
			update_post_meta( $post_id, $key, $value );
		}
	}


}

?>