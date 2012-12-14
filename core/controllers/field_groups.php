<?php 

class acf_field_groups 
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
		
		
		// actions
		add_action('admin_menu', array($this,'admin_menu'));

	}
	
	
	/*
	*  admin_menu
	*
	*  @description: 
	*  @created: 2/08/12
	*/
	
	function admin_menu()
	{
		
		// validate page
		if( ! $this->validate_page() ) return;
		
		add_action('admin_print_scripts', array($this,'admin_print_scripts'));
		add_action('admin_print_styles', array($this,'admin_print_styles'));
		add_action('admin_footer', array($this,'admin_footer'));
		
		add_filter( 'manage_edit-acf_columns', array($this,'acf_edit_columns') );
		add_action( 'manage_acf_posts_custom_column' , array($this,'acf_columns_display'), 10, 2 );
		
	}
	
	
	/*
	*  validate_page
	*
	*  @description: returns true | false. Used to stop a function from continuing
	*  @since 3.2.6
	*  @created: 23/06/12
	*/
	
	function validate_page()
	{
		// global
		global $pagenow;
		
		
		// vars
		$return = false;
		
		
		// validate page
		if( in_array( $pagenow, array('edit.php') ) )
		{
		
			// validate post type
			if( isset($_GET['post_type']) && $_GET['post_type'] == 'acf' )
			{
				$return = true;
			}
			
			
			if( isset($_GET['page']) )
			{
				$return = false;
			}
			
		}
		
		
		// return
		return $return;
	}
	
	
	/*
	*  admin_print_scripts
	*
	*  @description: 
	*  @since 3.1.8
	*  @created: 23/06/12
	*/
	
	function admin_print_scripts()
	{
		wp_enqueue_script(array(
			'jquery',
			'thickbox',
		));
	}
	
	
	/*
	*  admin_print_styles
	*
	*  @description: 
	*  @since 3.1.8
	*  @created: 23/06/12
	*/
	
	function admin_print_styles()
	{
		wp_enqueue_style(array(
			'thickbox',
			'acf-global',
			'acf',
		));
	}
	
	
	/*
	*  acf_edit_columns
	*
	*  @description: 
	*  @created: 2/08/12
	*/
	
	function acf_edit_columns( $columns )
	{
		$columns = array(
			'cb'	 	=> '<input type="checkbox" />',
			'title' 	=> __("Title"),
			'fields' 	=> __("Fields", 'acf')
		);
		
		return $columns;
	}
	
	
	/*
	*  acf_columns_display
	*
	*  @description: 
	*  @created: 2/08/12
	*/
	
	function acf_columns_display( $column, $post_id )
	{
		// vars
		$options = $this->parent->get_acf_options( $post_id );
		
		
		switch ($column)
	    {
	        case "fields":
	            
	            // vars
				$count =0;
				$keys = get_post_custom_keys( $post_id );
				
				if($keys)
				{
					foreach($keys as $key)
					{
						if(strpos($key, 'field_') !== false)
						{
							$count++;
						}
					}
			 	}
			 	
			 	echo $count;

	            break;
	        
	         case "order":
	        	
	        	global $post;
				
	        	echo $order = $post->menu_order;
	        	
	        	break;
	        	
	        case "position":
	        	
	        	$choices = array(
					'normal'	=>	__("Normal",'acf'),
					'side'		=>	__("Side",'acf'),
				);
				
	        	echo $choices[$options['position']];
	        	
	        	break;
	        
	        case "style":
	        	
	        	$choices = array(
					'default'	=>	__("Standard Metabox",'acf'),
					'no_box'	=>	__("No Metabox",'acf'),
				);
				
	        	echo $choices[$options['layout']];
	    }
	}
	
	
	/*
	*  admin_footer
	*
	*  @description: 
	*  @since 3.1.8
	*  @created: 23/06/12
	*/
	
	function admin_footer()
	{
		?>
<div id="acf-col-right" class="hidden">

	<div class="wp-box">
		<div class="inner">
			<h3 class="h2"><?php _e("Advanced Custom Fields",'acf'); ?> <span>v<?php echo $this->parent->version; ?></span></h3>

			<h3><?php _e("Changelog",'acf'); ?></h3>
			<p><?php _e("See what's new in",'acf'); ?> <a class="thickbox" href="<?php echo admin_url('plugin-install.php'); ?>?tab=plugin-information&plugin=advanced-custom-fields&section=changelog&TB_iframe=true&width=640&height=559">v<?php echo $this->parent->version; ?></a>
			
			<h3><?php _e("Resources",'acf'); ?></h3>
			<p><?php _e("Read documentation, learn the functions and find some tips &amp; tricks for your next web project.",'acf'); ?><br />
			<a href="http://www.advancedcustomfields.com/" target="_blank"><?php _e("Visit the ACF website",'acf'); ?></a></p>

		</div>
		<div class="footer footer-blue">
			<ul class="left hl">
				<li><?php _e("Created by",'acf'); ?> Elliot Condon</li>
			</ul>
			<ul class="right hl">
				<li><a href="http://wordpress.org/extend/plugins/advanced-custom-fields/"><?php _e("Vote",'acf'); ?></a></li>
				<li><a href="http://twitter.com/elliotcondon"><?php _e("Follow",'acf'); ?></a></li>
			</ul>
		</div>
	</div>
</div>
<script type="text/javascript">
(function($){
	$('#wpbody .wrap').wrapInner('<div id="acf-col-left" />');
	$('#wpbody .wrap').wrapInner('<div id="acf-cols" />');
	$('#acf-col-right').removeClass('hidden').prependTo('#acf-cols');
	
	$('#acf-col-left > .icon32').insertBefore('#acf-cols');
	$('#acf-col-left > h2').insertBefore('#acf-cols');
})(jQuery);
</script>
		<?php
	}
			
}

?>