<?php

class acf_Relationship extends acf_Field
{
	
	/*--------------------------------------------------------------------------------------
	*
	*	Constructor
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	*	@updated 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function __construct($parent)
	{
    	parent::__construct($parent);
    	
    	$this->name = 'relationship';
		$this->title = __("Relationship",'acf');
		
		
		// actions
		add_action('wp_ajax_acf_get_relationship_results', array($this, 'acf_get_relationship_results'));
		
   	}
   	
   	
   	/*
   	*  my_posts_where
   	*
   	*  @description: 
   	*  @created: 3/09/12
   	*/
   	
   	function posts_where( $where, &$wp_query )
	{
	    global $wpdb;
	    
	    if ( $title = $wp_query->get('like_title') )
	    {
	        $where .= " AND " . $wpdb->posts . ".post_title LIKE '%" . esc_sql( like_escape(  $title ) ) . "%'";
	    }
	    
	    return $where;
	}
	
	
   	/*--------------------------------------------------------------------------------------
	*
	*	acf_get_relationship_results
	*
	*	@author Elliot Condon
	*   @description: Generates HTML for Left column relationship results
	*   @created: 5/07/12
	* 
	*-------------------------------------------------------------------------------------*/
	
   	function acf_get_relationship_results()
   	{
   		// vars
		$options = array(
			'post_type'	=>	'',
			'taxonomy' => 'all',
			'posts_per_page' => 10,
			'paged' => 0,
			'orderby' => 'title',
			'order' => 'ASC',
			'post_status' => array('publish', 'private', 'draft', 'inherit', 'future'),
			'suppress_filters' => false,
			's' => '',
			'lang' => false,
			'update_post_meta_cache' => false,
			'field_name' => '',
			'field_key' => ''
		);
		$ajax = isset( $_POST['action'] ) ? true : false;
		
		
		// override options with posted values
		if( $ajax )
		{
			$options = array_merge($options, $_POST);
		}
		
		
		// WPML
		if( $options['lang'] )
		{
			global $sitepress;
			
			$sitepress->switch_lang( $options['lang'] );
		}
		
		
		// convert types
		$options['post_type'] = explode(',', $options['post_type']);
		$options['taxonomy'] = explode(',', $options['taxonomy']);
		
		
		// load all post types by default
		if( !$options['post_type'] || !is_array($options['post_type']) || $options['post_type'][0] == "" )
		{
			$options['post_type'] = $this->parent->get_post_types();
		}
		
		
		// attachment doesn't work if it is the only item in an array???
		if( is_array($options['post_type']) && count($options['post_type']) == 1 )
		{
			$options['post_type'] = $options['post_type'][0];
		}
		
		
		// create tax queries
		if( ! in_array('all', $options['taxonomy']) )
		{
			// vars
			$taxonomies = array();
			$options['tax_query'] = array();
			
			foreach( $options['taxonomy'] as $v )
			{
				
				// find term (find taxonomy!)
				// $term = array( 0 => $taxonomy, 1 => $term_id )
				$term = explode(':', $v); 
				
				
				// validate
				if( !is_array($term) || !isset($term[1]) )
				{
					continue;
				}
				
				
				// add to tax array
				$taxonomies[ $term[0] ][] = $term[1];
				
			}
			
			
			// now create the tax queries
			foreach( $taxonomies as $k => $v )
			{
				$options['tax_query'][] = array(
					'taxonomy' => $k,
					'field' => 'id',
					'terms' => $v,
				);
			}
		}
		
		unset( $options['taxonomy'] );
		
		
		// search
		if( $options['s'] )
		{
			$options['like_title'] = $options['s'];
			
			add_filter( 'posts_where', array($this, 'posts_where'), 10, 2 );
		}
		
		unset( $options['s'] );
		
		
		// filters
		$options = apply_filters('acf_relationship_query', $options);
		$options = apply_filters('acf_relationship_query-' . $options['field_name'] , $options);
		$options = apply_filters('acf_relationship_query-' . $options['field_key'], $options);
		
		
		$results = false;
		$results = apply_filters('acf_relationship_results', $results, $options);
		$results = apply_filters('acf_relationship_results-' . $options['field_name'] , $results, $options);
		$results = apply_filters('acf_relationship_results-' . $options['field_key'], $results, $options);
		
		
		if( ! $results )
		{
			// load the posts
			$posts = get_posts( $options );
			
			if( $posts )
			{
				foreach( $posts  as $post )
				{
					// right aligned info
					$title = '<span class="relationship-item-info">';
					
						$title .= $post->post_type;
						
						// WPML
						if( $options['lang'] )
						{
							$title .= ' (' . $options['lang'] . ')';
						}
						
					$title .= '</span>';
					
					// find title. Could use get_the_title, but that uses get_post(), so I think this uses less Memory
					$title .= apply_filters( 'the_title', $post->post_title, $post->ID );
	
					// status
					if($post->post_status != "publish")
					{
						$title .= " ($post->post_status)";
					}
					
					
					$title = apply_filters('acf_relationship_result', $title);
					$title = apply_filters('acf_relationship_result-' . $options['field_name'] , $title);
					$title = apply_filters('acf_relationship_result-' . $options['field_key'], $title);
					
					
					echo '<li><a href="' . get_permalink($post->ID) . '" data-post_id="' . $post->ID . '">' . $title .  '<span class="acf-button-add"></span></a></li>';
				}
			}
		}
		
		
		// die?
		if( $ajax )
		{
			die();
		}
		
	}
   		
	
	/*--------------------------------------------------------------------------------------
	*
	*	create_field
	*
	*	@author Elliot Condon
	*	@since 2.0.5
	*	@updated 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function create_field($field)
	{
		// vars
		$defaults = array(
			'post_type'	=>	'',
			'max' 		=>	-1,
			'taxonomy' 	=>	array('all'),
		);
		
		$field = array_merge($defaults, $field);
		
		
		// validate types
		$field['max'] = (int) $field['max'];
		
		
		// row limit <= 0?
		if( $field['max'] <= 0 )
		{
			$field['max'] = 9999;
		}
		
		
		// load all post types by default
		if( !$field['post_type'] || !is_array($field['post_type']) || $field['post_type'][0] == "" )
		{
			$field['post_type'] = $this->parent->get_post_types();
		}
		
		
		?>
<div class="acf_relationship" data-max="<?php echo $field['max']; ?>" data-s="" data-paged="1" data-post_type="<?php echo implode(',', $field['post_type']); ?>" data-taxonomy="<?php echo implode(',', $field['taxonomy']); ?>" <?php if( defined('ICL_LANGUAGE_CODE') ){ echo 'data-lang="' . ICL_LANGUAGE_CODE . '"';} ?>>
	
	<!-- Hidden Blank default value -->
	<input type="hidden" name="<?php echo $field['name']; ?>" value="" />
	
	<!-- Template for value -->
	<script type="text/html" class="tmpl-li">
	<li>
		<a href="#" data-post_id="{post_id}">{title}<span class="acf-button-remove"></span></a>
		<input type="hidden" name="<?php echo $field['name']; ?>[]" value="{post_id}" />
	</li>
	</script>
	<!-- / Template for value -->
	
	<!-- Left List -->
	<div class="relationship_left">
		<table class="widefat">
			<thead>
				<tr>
					<th>
						<label class="relationship_label" for="relationship_<?php echo $field['name']; ?>"><?php _e("Search",'acf'); ?>...</label>
						<input class="relationship_search" type="text" id="relationship_<?php echo $field['name']; ?>" />
						<div class="clear_relationship_search"></div>
					</th>
				</tr>
			</thead>
		</table>
		<ul class="bl relationship_list">
			<li class="load-more">
				<div class="acf-loading"></div>
			</li>
		</ul>
	</div>
	<!-- /Left List -->
	
	<!-- Right List -->
	<div class="relationship_right">
		<ul class="bl relationship_list">
		<?php

		if( $field['value'] )
		{
			foreach( $field['value'] as $post )
			{
				// check that post exists (my have been trashed)
				if( !is_object($post) )
				{
					continue;
				}
				
				
				// right aligned info
				$title = '<span class="relationship-item-info">';
				
					$title .= $post->post_type;
					
					// WPML
					if( defined('ICL_LANGUAGE_CODE') )
					{
						$title .= ' (' . ICL_LANGUAGE_CODE . ')';
					}
					
				$title .= '</span>';
				
				// find title. Could use get_the_title, but that uses get_post(), so I think this uses less Memory
				$title .= apply_filters( 'the_title', $post->post_title, $post->ID );

				// status
				if($post->post_status != "publish")
				{
					$title .= " ($post->post_status)";
				}
				
				echo '<li>
					<a href="' . get_permalink($post->ID) . '" class="" data-post_id="' . $post->ID . '">' . $title . '<span class="acf-button-remove"></span></a>
					<input type="hidden" name="' . $field['name'] . '[]" value="' . $post->ID . '" />
				</li>';
				
					
			}
		}
			
		?>
		</ul>
	</div>
	<!-- / Right List -->
	
</div>
		<?php

	
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	create_options
	*
	*	@author Elliot Condon
	*	@since 2.0.6
	*	@updated 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function create_options($key, $field)
	{
		// vars
		$defaults = array(
			'post_type'	=>	'',
			'max' 		=>	'',
			'taxonomy' 	=>	array('all'),
		);
		
		$field = array_merge($defaults, $field);
		
		
		// validate taxonomy
		if( !is_array($field['taxonomy']) )
		{
			$field['taxonomy'] = array('all');
		}
		
		
		?>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label for=""><?php _e("Post Type",'acf'); ?></label>
			</td>
			<td>
				<?php 
				
				$choices = array(
					''	=>	__("All",'acf')
				);
				
				$post_types = $this->parent->get_post_types();
				
				foreach( $post_types as $post_type )
				{
					$choices[$post_type] = $post_type;
				}
				
				do_action('acf/create_field', array(
					'type'	=>	'select',
					'name'	=>	'fields['.$key.'][post_type]',
					'value'	=>	$field['post_type'],
					'choices'	=>	$choices,
					'multiple'	=>	1,
				));
				
				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Filter from Taxonomy",'acf'); ?></label>
			</td>
			<td>
				<?php 
				$choices = array(
					'' => array(
						'all' => __("All",'acf')
					)
				);
				$choices = array_merge($choices, $this->parent->get_taxonomies_for_select());
				do_action('acf/create_field', array(
					'type'	=>	'select',
					'name'	=>	'fields['.$key.'][taxonomy]',
					'value'	=>	$field['taxonomy'],
					'choices' => $choices,
					'optgroup' => true,
					'multiple'	=>	1,
				));
				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Maximum posts",'acf'); ?></label>
			</td>
			<td>
				<?php 
				do_action('acf/create_field', array(
					'type'	=>	'text',
					'name'	=>	'fields['.$key.'][max]',
					'value'	=>	$field['max'],
				));
				?>
			</td>
		</tr>
		<?php
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_value
	*
	*	@author Elliot Condon
	*	@since 3.3.3
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_value($post_id, $field)
	{
		// get value
		$value = parent::get_value($post_id, $field);
		
		
		// empty?
		if( !$value )
		{
			return $value;
		}
		
		
		// Pre 3.3.3, the value is a string coma seperated
		if( !is_array($value) )
		{
			$value = explode(',', $value);
		}
		
		
		// empty?
		if( empty($value) )
		{
			return $value;
		}
		
		
		// find posts (DISTINCT POSTS)
		$posts = get_posts(array(
			'numberposts' => -1,
			'post__in' => $value,
			'post_type'	=>	$this->parent->get_post_types(),
			'post_status' => array('publish', 'private', 'draft', 'inherit', 'future'),
		));

		
		$ordered_posts = array();
		foreach( $posts as $post )
		{
			// create array to hold value data
			$ordered_posts[ $post->ID ] = $post;
		}
		
		
		// override value array with attachments
		foreach( $value as $k => $v)
		{
			// check that post exists (my have been trashed)
			if( isset($ordered_posts[ $v ]) )
			{
				$value[ $k ] = $ordered_posts[ $v ];
			}
		}
		
				
		// return value
		return $value;	
	}
	

	
}

?>