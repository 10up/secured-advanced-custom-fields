<?php 

/*
*  acf_location
*
*  @description: 
*  @since: 3.5.7
*  @created: 3/01/13
*/

class acf_location
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
		
		
		// ajax
		add_action('wp_ajax_acf/location/match_field_groups_ajax', array($this, 'match_field_groups_ajax'));
		
		
		// filters
		add_filter('acf/location/match_field_groups', array($this, 'match_field_groups'), 10, 2);
		
		
		// Basic
		add_filter('acf/location/rule_match/post_type', array($this, 'rule_match_post_type'), 10, 3);
		add_filter('acf/location/rule_match/user_type', array($this, 'rule_match_user_type'), 10, 3);
		
		// Page
		add_filter('acf/location/rule_match/page', array($this, 'rule_match_post'), 10, 3);
		add_filter('acf/location/rule_match/page_type', array($this, 'rule_match_page_type'), 10, 3);
		add_filter('acf/location/rule_match/page_parent', array($this, 'rule_match_page_parent'), 10, 3);
		add_filter('acf/location/rule_match/page_template', array($this, 'rule_match_page_template'), 10, 3);
		
		// Post
		add_filter('acf/location/rule_match/post', array($this, 'rule_match_post'), 10, 3);
		add_filter('acf/location/rule_match/post_category', array($this, 'rule_match_post_category'), 10, 3);
		add_filter('acf/location/rule_match/post_format', array($this, 'rule_match_post_format'), 10, 3);
		add_filter('acf/location/rule_match/taxonomy', array($this, 'rule_match_taxonomy'), 10, 3);
		
		// Other
		add_filter('acf/location/rule_match/ef_taxonomy', array($this, 'rule_match_ef_taxonomy'), 10, 3);
		add_filter('acf/location/rule_match/ef_user', array($this, 'rule_match_ef_user'), 10, 3);
		add_filter('acf/location/rule_match/ef_media', array($this, 'rule_match_ef_media'), 10, 3);
		
		// Options Page
		add_filter('acf/location/rule_match/options_page', array($this, 'rule_match_options_page'), 10, 3);

		
		
	}

	
	/*
	*  match_field_groups_ajax
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function match_field_groups_ajax()
	{
		
		// vars
		$options = array(
			'nonce' => '',
			'return' => 'json'
		);
		
		
		// load post options
		$options = array_merge($options, $_POST);
		
		
		// verify nonce
		if( ! wp_verify_nonce($options['nonce'], 'acf_nonce') )
		{
			die(0);
		}
		
		
		// return array
		$return = array();
		$return = apply_filters( 'acf/location/match_field_groups', $return, $options );
		
		
		// echo json
		echo json_encode( $return );
		
		
		die();	
	}
	
	
	/*
	*  match_field_groups
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function match_field_groups( $return, $options )
	{
		// vars
		$defaults = array(
			'post_id' => 0,
			'post_type' => 0,
			'page_template' => 0,
			'page_parent' => 0,
			'page_type' => 0,
			'post_category' => array(),
			'post_format' => 0,
			'taxonomy' => array(),
			'ef_taxonomy' => 0,
			'ef_user' => 0,
			'ef_media' => 0,
			'lang' => 0,
			'return' => 'php'
		);
		
		
		// merge in $options
		$options = array_merge($defaults, $options);
		
		
		// Parse values
		$options = apply_filters( 'acf_parse_value', $options );
		

		// WPML
		if( $options['lang'] )
		{
			global $sitepress;
			$sitepress->switch_lang( $options['lang'] );
		}
		
		
		// find all acf objects
		$acfs = $this->parent->get_field_groups();
		
		
		// blank array to hold acfs
		$return = array();
		
		
		if( $acfs )
		{
			foreach( $acfs as $acf )
			{
				// vars
				$add_box = false;
				
				
				// if all of the rules are required to match, start at true and let any !$match set $add_box to false
				if( $acf['location']['allorany'] == 'all' )
				{
					$add_box = true;
				}
						
				
				if( $acf['location']['rules'] )
				{
					// defaults
					$rule_defaults = array(
						'param' => '',
						'operator' => '==',
						'value' => ''
					);
					
					foreach($acf['location']['rules'] as $rule)
					{
						// make sure rule has all 3 keys
						$rule = array_merge( $rule_defaults, $rule );
						
						
						// $match = true / false
						$match = false;
						$match = apply_filters( 'acf/location/rule_match/' . $rule['param'] , $match, $rule, $options );
						
						
						if( $acf['location']['allorany'] == 'all' && !$match )
						{
							// if all of the rules are required to match and this rule did not, don't add this box!
							$add_box = false;
						}
						elseif($acf['location']['allorany'] == 'any' && $match )
						{
							// if any of the rules are required to match and this rule did, add this box!
							$add_box = true;
						}

					}
				}
					
				
				// add ID to array	
				if( $add_box )
				{
					$return[] = $acf['id'];
				}
				
			}
		}
		
	
		return $return;
	}
	
	
	/*
	*  rule_match_post_type
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_post_type( $match, $rule, $options )
	{
		$post_type = $options['post_type'];

		if( !$post_type )
		{
			$post_type = get_post_type( $options['post_id'] );
		}
		

        if( $rule['operator'] == "==" )
        {
        	$match = ( $post_type == $rule['value'] );
        }
        elseif( $rule['operator'] == "!=" )
        {
        	$match = ( $post_type != $rule['value'] );
        }
		
	
		return $match;
	}
	
	
	/*
	*  rule_match_post
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_post( $match, $rule, $options )
	{
		// validation
		if( !$options['post_id'] )
		{
			return false;
		}
		
		$post = $options['post_id'];
		        
        if($rule['operator'] == "==")
        {
        	$match = ( $post == $rule['value'] );
        }
        elseif($rule['operator'] == "!=")
        {
        	$match = ( $post != $rule['value'] );
        }
        
        return $match;

	}
	
	
	/*
	*  rule_match_page_type
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_page_type( $match, $rule, $options )
	{
		// validation
		if( !$options['post_id'] )
		{
			return false;
		}

		$post = get_post( $options['post_id'] );
		        
        if( $rule['value'] == 'front_page')
        {
        	
	        $front_page = (int) get_option('page_on_front');
	        
	        
	        if($rule['operator'] == "==")
	        {
	        	$match = ( $front_page == $post->ID );
	        }
	        elseif($rule['operator'] == "!=")
	        {
	        	$match = ( $front_page != $post->ID );
	        }
	        
        }
        elseif( $rule['value'] == 'posts_page')
        {
        
	        $posts_page = (int) get_option('page_for_posts');
	        
	        
	        if($rule['operator'] == "==")
	        {
	        	$match = ( $posts_page == $post->ID );
	        }
	        elseif($rule['operator'] == "!=")
	        {
	        	$match = ( $posts_page != $post->ID );
	        }
	        
        }
        elseif( $rule['value'] == 'parent')
        {
        
        	$children = get_pages(array(
        		'post_type' => $post->post_type,
        		'child_of' =>  $post->ID,
        	));
        	
	        
	        if($rule['operator'] == "==")
	        {
	        	$match = ( count($children) > 0 );
	        }
	        elseif($rule['operator'] == "!=")
	        {
	        	$match = ( count($children) == 0 );
	        }
	        
        }
        elseif( $rule['value'] == 'child')
        {
        
        	$post_parent = $post->post_parent;
        	if( $options['page_parent'] )
        	{
	        	$post_parent = $options['page_parent'];
        	}
	        
	        
	        if($rule['operator'] == "==")
	        {
	        	$match = ( $post_parent != 0 );
	        }
	        elseif($rule['operator'] == "!=")
	        {
	        	$match = ( $post_parent == 0 );
	        }
	        
        }
        
        return $match;

	}
	
	
	/*
	*  rule_match_page_parent
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_page_parent( $match, $rule, $options )
	{
		// validation
		if( !$options['post_id'] )
		{
			return false;
		}
		
		
		// vars
		$post = get_post( $options['post_id'] );
		$page_parent = $post->post_parent;
        
        
        if($rule['operator'] == "==")
        {
        	$match = ( $page_parent == $rule['value'] );
        }
        elseif($rule['operator'] == "!=")
        {
        	$match = ( $page_parent != $rule['value'] );
        }
                
        return $match;

	}
	
	
	/*
	*  rule_match_page_template
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_page_template( $match, $rule, $options )
	{
		$page_template = $options['page_template'];
		if( ! $page_template )
		{
			$page_template = get_post_meta( $options['post_id'], '_wp_page_template', true );
		}
		
		
        if($rule['operator'] == "==")
        {
        	$match = ( $page_template == $rule['value'] );
        	
        	if( $rule['value'] == "default" && !$page_template )
        	{
        		$match = true;
        	}
        }
        elseif($rule['operator'] == "!=")
        {
        	$match = ( $page_template != $rule['value'] );
        }
                
        return $match;

	}
	
	
	/*
	*  rule_match_post_category
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_post_category( $match, $rule, $options )
	{
		$cats = $options['post_category'];
		
		if( empty($cats) )
		{
			if( !$options['post_id'] )
			{
				return false;
			}
			
			$all_cats = get_the_category( $options['post_id'] );
        	foreach( $all_cats as $cat )
			{
				$cats[] = $cat->term_id;
			}
		}

        if($rule['operator'] == "==")
        {
        	$match = false;
        	
        	if($cats)
			{
				if( in_array($rule['value'], $cats) )
				{
					$match = true; 
				}
			}
  
        }
        elseif($rule['operator'] == "!=")
        {
        	$match = true;
        	
        	if($cats)
			{
				if( in_array($rule['value'], $cats) )
				{
					$match = false; 
				}
			}

        }
        
        return $match;
        
    }
    
    
    /*
	*  rule_match_user_type
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_user_type( $match, $rule, $options )
	{
		if($rule['operator'] == "==")
        {
        	$match = ( current_user_can($rule['value']) );
        }
        elseif($rule['operator'] == "!=")
        {
        	$match = ( ! current_user_can($rule['value']) );
        }
        
        return $match;
        
    }
    
    
    /*
	*  rule_match_user_type
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_options_page( $match, $rule, $options )
	{
		global $plugin_page;
		    	
		    	
		// older location rules may be "options-pagename"
		if( substr($rule['value'], 0, 8) == 'options-' )
		{
			$rule['value'] = 'acf-' . $rule['value'];
		}
		
		
		// older location ruels may be "Pagename"
		if( substr($rule['value'], 0, 11) != 'acf-options' )
		{
			$rule['value'] = 'acf-options-' . sanitize_title( $rule['value'] );
			
			// value may now be wrong (acf-options-options)
			if( $rule['value'] == 'acf-options-options' )
			{
				$rule['value'] = 'acf-options';
			}
		}
		
		
		if($rule['operator'] == "==")
        {
        	$match = ( $plugin_page == $rule['value'] );
        }
        elseif($rule['operator'] == "!=")
        {
        	$match = ( $plugin_page != $rule['value'] );
        }
        
        
        return $match;
        
    }
    
    
    /*
	*  rule_match_post_format
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_post_format( $match, $rule, $options )
	{
		// vars
		$post_format = $options['post_format'];
		if( ! $post_format )
		{
			$post_format = get_post_format( $options['post_id'] );
		}
       
       	
       	if($rule['operator'] == "==")
        {
        	$match = ( $post_format == $rule['value'] );
        }
        elseif($rule['operator'] == "!=")
        {
        	$match = ( $post_format != $rule['value'] );
        }
         
        
        return $match;
        
    }
    
    
    /*
	*  rule_match_taxonomy
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_taxonomy( $match, $rule, $options )
	{
		$terms = $options['taxonomy'];
		
		
		if( empty($terms) )
		{
			if( !$options['post_id'] )
			{
				return false;
			}
			
			$post_type = get_post_type( $options['post_id'] );
			$taxonomies = get_object_taxonomies( $post_type );
			
        	if($taxonomies)
        	{
	        	foreach($taxonomies as $tax)
				{
					$all_terms = get_the_terms( $options['post_id'], $tax );
					if($all_terms)
					{
						foreach($all_terms as $all_term)
						{
							$terms[] = $all_term->term_id;
						}
					}
				}
			}
		}

        
        if($rule['operator'] == "==")
        {
        	$match = false;
        	
        	if($terms)
			{
				if( in_array($rule['value'], $terms) )
				{
					$match = true; 
				}
			}
  
        }
        elseif($rule['operator'] == "!=")
        {
        	$match = true;
        	
        	if($terms)
			{
				if( in_array($rule['value'], $terms) )
				{
					$match = false; 
				}
			}

        }
    
        
        return $match;
        
    }
    
    
    /*
	*  rule_match_ef_taxonomy
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_ef_taxonomy( $match, $rule, $options )
	{
	
		$ef_taxonomy = $options['ef_taxonomy'];
		
		
		if( $ef_taxonomy )
		{
			if($rule['operator'] == "==")
	        {
	        	$match = ( $ef_taxonomy == $rule['value'] );
	        	
	        	// override for "all"
		        if( $rule['value'] == "all" )
				{
					$match = true;
				}
				
	        }
	        elseif($rule['operator'] == "!=")
	        {
	        	$match = ( $ef_taxonomy != $rule['value'] );
	        		
	        	// override for "all"
		        if( $rule['value'] == "all" )
				{
					$match = false;
				}
				
	        }
			
	        
	        
			
		}
		
        
        return $match;
        
    }
    
    
    /*
	*  rule_match_ef_user
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_ef_user( $match, $rule, $options )
	{
	
		$ef_user = $options['ef_user'];
		
		
		if( $ef_user )
		{
			if($rule['operator'] == "==")
	        {
	        	$match = ( user_can($ef_user, $rule['value']) );
	        	
	        	// override for "all"
		        if( $rule['value'] == "all" )
				{
					$match = true;
				}
	        }
	        elseif($rule['operator'] == "!=")
	        {
	        	$match = ( !user_can($ef_user, $rule['value']) );
	        	
	        	// override for "all"
		        if( $rule['value'] == "all" )
				{
					$match = false;
				}
	        }

		}
		
        
        return $match;
        
    }
    
    
    /*
	*  rule_match_ef_media
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_ef_media( $match, $rule, $options )
	{
		global $wp_version;

		
		if( version_compare($wp_version, '3.5', '>=') )
		{
			// in 3.5, the media rule should check the post type
			$rule['param'] = 'post_type';
			$rule['value'] = 'attachment';
			return $this->rule_match_post_type( $match, $rule, $options );
		}
		
		
		$ef_media = $options['ef_media'];
		
        if( $ef_media )
		{
			if($rule['operator'] == "==")
	        {
	        	// override for "all"
		        if( $rule['value'] == "all" )
				{
					$match = true;
				}
	        }
	        elseif($rule['operator'] == "!=")
	        {
	        	// override for "all"
		        if( $rule['value'] == "all" )
				{
					$match = false;
				}
	        }

		}
		
        return $match;
        
    }

}

?>