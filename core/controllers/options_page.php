<?php 

class acf_options_page 
{
	
	/*
	*  Vars
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 5/12/12
	*/
	
	var $parent,
		$dir,
		$data;
	
	
	/*
	*  construct
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 5/12/12
	*/

	function __construct($parent)
	{
		// vars
		$this->parent = $parent;
		$this->dir = $parent->dir;
		
		
		// data for passing variables
		$this->data = array();
		
		
		// actions
		add_action('admin_menu', array($this,'admin_menu'));
		
	}
	
	
	/*
	*  admin_menu
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 5/12/12
	*/
	
	function admin_menu() 
	{
		// validate
		if( !$this->parent->is_field_unlocked('options_page') )
		{
			return true;
		}
		
		
		// vars
		$defaults = $this->parent->defaults['options_page'];
		$parent_slug = 'acf-options';
		$parent_title = $defaults['title'];
		$parent_menu = $defaults['title'];
		
		
		// redirect to first child
		if( !empty($defaults['pages']) )
		{	
			$parent_title = $defaults['pages'][0];
			$parent_slug = 'acf-options-' . sanitize_title( $parent_title );
		}
		
		
		// Parent
		$parent_page = add_menu_page($parent_title, $parent_menu, $defaults['capability'], $parent_slug, array($this, 'html'));	
		
		
		// some fields require js + css
		add_action('admin_print_scripts-'.$parent_page, array($this, 'admin_print_scripts'));
		add_action('admin_print_styles-'.$parent_page, array($this, 'admin_print_styles'));
		
		
		// Add admin head
		add_action('admin_head-'.$parent_page, array($this,'admin_head'));
		add_action('admin_footer-'.$parent_page, array($this,'admin_footer'));
		
		
		if( !empty($defaults['pages']) )
		{
			foreach($defaults['pages'] as $c)
			{
				$sub_title = $c;
				$sub_slug = 'acf-options-' . sanitize_title( $sub_title );
				
				$child_page = add_submenu_page($parent_slug, $sub_title, $sub_title, $defaults['capability'], $sub_slug, array($this, 'html'));
				
				// some fields require js + css
				add_action('admin_print_scripts-'.$child_page, array($this, 'admin_print_scripts'));
				add_action('admin_print_styles-'.$child_page, array($this, 'admin_print_styles'));
				
				// Add admin head
				add_action('admin_head-'.$child_page, array($this,'admin_head'));
				add_action('admin_footer-'.$child_page, array($this,'admin_footer'));
			}
		}

	}
	
	
	/*
	*  admin_head
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 5/12/12
	*/
	
	function admin_head()
	{	
	
		// save
		if( isset($_POST['acf_options_page']) )
		{
			if( wp_verify_nonce($_POST['acf_options_page'], 'acf_options_page') )
			{
				do_action('acf_save_post', 'options');
			
				$this->data['admin_message'] = __("Options Updated",'acf');
			}
		}
		
		
		// get field groups
		$filter = array();
		$metabox_ids = array();
		$metabox_ids = apply_filters( 'acf/location/match_field_groups', $metabox_ids, $filter );

		
		if(empty($metabox_ids))
		{
			$this->data['no_fields'] = true;
			return false;	
		}
		
		// Style
		echo '<style type="text/css">#side-sortables.empty-container { border: 0 none; }</style>';
		
		
		// add user js + css
		do_action('acf_head-input');
		

		// get acf's
		$acfs = $this->parent->get_field_groups();
		if($acfs)
		{
			foreach($acfs as $acf)
			{
				// hide / show
				$show = in_array($acf['id'], $metabox_ids) ? 1 : 0;

				if( $show )
				{				
					// add meta box
					add_meta_box(
						'acf_' . $acf['id'], 
						$acf['title'], 
						array($this->parent->input, 'meta_box_input'), 
						'acf_options_page', 
						$acf['options']['position'], 
						'high', 
						array( 'fields' => $acf['fields'], 'options' => $acf['options'], 'show' => $show, 'post_id' => "options" )
					);
				}
			}
		}
		
	}
	
	
	/*
	*  admin_footer
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 5/12/12
	*/
	
	function admin_footer()
	{
		// add togle open / close postbox
		?>
		<script type="text/javascript">
		(function($){
			
			$('.postbox .handlediv').live('click', function(){
				
				var postbox = $(this).closest('.postbox');
				
				if( postbox.hasClass('closed') )
				{
					postbox.removeClass('closed');
				}
				else
				{
					postbox.addClass('closed');
				}
				
			});
			
		})(jQuery);
		</script>
		<?php
	}
	
	
	/*
	*  admin_print_scripts
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 5/12/12
	*/
	
	function admin_print_scripts()
	{
  		do_action('acf_print_scripts-input');
	}
	
	
	/*
	*  admin_print_styles
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 5/12/12
	*/
	
	function admin_print_styles()
	{
		do_action('acf_print_styles-input');
	}
	
	
	/*
	*  html
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 5/12/12
	*/
	
	function html()
	{
		?>
		<div class="wrap no_move">
		
			<div class="icon32" id="icon-options-general"><br></div>
			<h2><?php echo get_admin_page_title(); ?></h2>
			
			<?php if(isset($this->data['admin_message'])): ?>
			<div id="message" class="updated"><p><?php echo $this->data['admin_message']; ?></p></div>
			<?php endif; ?>
			
			<?php if(isset($this->data['no_fields'])): ?>
			<div id="message" class="updated"><p><?php _e("No Custom Field Group found for the options page",'acf'); ?>. <a href="<?php echo admin_url(); ?>post-new.php?post_type=acf"><?php _e("Create a Custom Field Group",'acf'); ?></a></p></div>
			<?php else: ?>
			
			<form id="post" method="post" name="post">
			<div class="metabox-holder has-right-sidebar" id="poststuff">
				
				<!-- Sidebar -->
				<div class="inner-sidebar" id="side-info-column">
					
					<!-- Update -->
					<div class="postbox">
						<h3 class="hndle"><span><?php _e("Publish",'acf'); ?></span></h3>
						<div class="inside">
							<input type="hidden" name="HTTP_REFERER" value="<?php echo $_SERVER['HTTP_REFERER'] ?>" />
							<input type="hidden" name="acf_options_page" value="<?php echo wp_create_nonce( 'acf_options_page' ); ?>" />
							<input type="submit" class="acf-button" value="<?php _e("Save Options",'acf'); ?>" />
						</div>
					</div>
					
					<?php $meta_boxes = do_meta_boxes('acf_options_page', 'side', null); ?>
					
				</div>
					
				<!-- Main -->
				<div id="post-body">
				<div id="post-body-content">
					<?php $meta_boxes = do_meta_boxes('acf_options_page', 'normal', null); ?>
					<script type="text/javascript">
					(function($){
					
						$('#poststuff .postbox[id*="acf_"]').addClass('acf_postbox');

					})(jQuery);
					</script>
				</div>
				</div>
			
			</div>
			</form>
			
			<?php endif; ?>
		
		</div>
		
		<?php
				
	}
			
}

?>