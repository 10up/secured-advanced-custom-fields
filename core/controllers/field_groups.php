<?php 

/*
*  acf_field_groups
*
*  @description: 
*  @since: 3.6
*  @created: 25/01/13
*/

class acf_field_groups 
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
		if( ! $this->validate_page() )
		{
			return;
		}
		
		
		// actions
		add_action('admin_print_scripts', array($this,'admin_print_scripts'));
		add_action('admin_print_styles', array($this,'admin_print_styles'));
		add_action('admin_footer', array($this,'admin_footer'));
		
		
		// columns
		add_filter( 'manage_edit-acf_columns', array($this,'acf_edit_columns'), 10, 1 );
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
		// vars
		$version = apply_filters('acf/get_info', 'version');
		$dir = apply_filters('acf/get_info', 'dir');
		$path = apply_filters('acf/get_info', 'path');
		$show_tab = isset($_GET['info']);
		$tab = isset($_GET['info']) ? $_GET['info'] : 'changelog';
		
		?>
<script type="text/html" id="tmpl-acf-col-right">
<div id="acf-col-right">

	<div class="wp-box">
		<div class="inner">
			<h3 class="h2"><?php _e("Advanced Custom Fields",'acf'); ?> <?php echo $version; ?></h3>

			<h3><?php _e("Changelog",'acf'); ?></h3>
			<p><?php _e("See what's new in",'acf'); ?> <a href="<?php echo admin_url('edit.php?post_type=acf&info=changelog'); ?>">version <?php echo $version; ?></a>
			
			<h3><?php _e("Resources",'acf'); ?></h3>
			<ul>
				<li><a href="http://www.advancedcustomfields.com/resources/#getting-started" target="_blank"><?php _e("Getting Started",'acf'); ?></a></li>
				<li><a href="http://www.advancedcustomfields.com/resources/#field-types" target="_blank"><?php _e("Field Types",'acf'); ?></a></li>
				<li><a href="http://www.advancedcustomfields.com/resources/#functions" target="_blank"><?php _e("Functions",'acf'); ?></a></li>
				<li><a href="http://www.advancedcustomfields.com/resources/#actions" target="_blank"><?php _e("Actions",'acf'); ?></a></li>
				<li><a href="http://www.advancedcustomfields.com/resources/#filters" target="_blank"><?php _e("Filters",'acf'); ?></a></li>
				<li><a href="http://www.advancedcustomfields.com/resources/#how-to" target="_blank"><?php _e("\"How to\" guides",'acf'); ?></a></li>
				<li><a href="http://www.advancedcustomfields.com/resources/#tutorials" target="_blank"><?php _e("Tutorials",'acf'); ?></a></li>
			</ul>
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
</script>
<script type="text/html" id="tmpl-acf-about">
<!-- acf-about -->
<div id="acf-about" class="acf-content">
	
	<!-- acf-content-title -->
	<div class="acf-content-title">
		<h1><?php _e("Welcome to Advanced Custom Fields",'acf'); ?> <?php echo $version; ?></h1>
		<h2><?php _e("Thank you for updating to the latest version!",'acf'); ?> <br />ACF <?php echo $version; ?> <?php _e("is more polished and enjoyable than ever before. We hope you like it.",'acf'); ?></h2>
	</div>
	<!-- / acf-content-title -->
	
	<!-- acf-content-body -->
	<div class="acf-content-body">
		<h2 class="nav-tab-wrapper">
			<a class="acf-tab-toggle nav-tab <?php if( $tab == 'whats-new' ){ echo 'nav-tab-active'; } ?>" href="<?php echo admin_url('edit.php?post_type=acf&info=whats-new'); ?>"><?php _e("What’s New",'acf'); ?></a>
			<a class="acf-tab-toggle nav-tab <?php if( $tab == 'changelog' ){ echo 'nav-tab-active'; } ?>" href="<?php echo admin_url('edit.php?post_type=acf&info=changelog'); ?>"><?php _e("Changelog",'acf'); ?></a>
		</h2>

<?php if( $tab == 'whats-new' ): ?>

		<table id="acf-add-ons-table" class="alignright">
			<tr>
				<td><img src="<?php echo $dir; ?>images/add-ons/repeater-field-thumb.jpg" /></td>
				<td><img src="<?php echo $dir; ?>images/add-ons/gallery-field-thumb.jpg" /></td>
			</tr>
			<tr>
				<td><img src="<?php echo $dir; ?>images/add-ons/options-page-thumb.jpg" /></td>
				<td><img src="<?php echo $dir; ?>images/add-ons/flexible-content-field-thumb.jpg" /></td>
			</tr>
		</table>
	
		<h3><?php _e("Add-ons",'acf'); ?></h3>
		
		<h4><?php _e("Activation codes have grown into plugins!",'acf'); ?></h4>
		<p><?php _e("Add-ons are now activated by downloading and installing individual plugins. Although these plugins will not be hosted on the wordpress.org repository, each Add-on will continue to receive updates in the usual way.",'acf'); ?></p>
		
		<h4><?php _e("Where can I find my Add-on plugins?",'acf'); ?></h4>
		<p><?php _e("Download links can be found alongside the activation code in your ACF receipt.",'acf'); ?> <a href="http://www.advancedcustomfields.com/store/account/" target="_blank"><?php _e("Visit your account",'acf'); ?></a>.<br />
		<?php _e("For faster access, this",'acf'); ?> <a href="http://www.advancedcustomfields.com/add-ons-download/" target="_blank"><?php _e("download page",'acf'); ?></a> <?php _e("has also been created.",'acf'); ?></p>
		
		<hr />
		
		<h3><?php _e("Easier Development",'acf'); ?></h3>
		
		<h4><?php _e("New Field Types",'acf'); ?></h4>
		<ul>
			<li><?php _e("Taxonomy Field",'acf'); ?></li>
			<li><?php _e("User Field",'acf'); ?></li>
			<li><?php _e("Email Field",'acf'); ?></li>
			<li><?php _e("Password Field",'acf'); ?></li>
		</ul>
		<h4><?php _e("Custom Field Types",'acf'); ?></h4>
		<p><?php _e("Creating your own field type has never been easier! Unfortunately, version 3 field types are not compatible with version 4.",'acf'); ?><br />
		<?php _e("Migrating your field types is easy, please",'acf'); ?> <a href="http://www.advancedcustomfields.com/docs/tutorials/creating-a-new-field-type/" target="_blank"><?php _e("follow this tutorial",'acf'); ?></a> <?php _e("to learn more.",'acf'); ?></p>
		
		<h4><?php _e("Actions &amp; Filters",'acf'); ?></h4>
		<p><?php _e("All actions & filters have recieved a major facelift to make customizing ACF even easier! Please",'acf'); ?> <a href="http://www.advancedcustomfields.com/resources/getting-started/migrating-from-v3-to-v4/" target="_blank"><?php _e("read this guide",'acf'); ?></a> <?php _e("to find the updated naming convention.",'acf'); ?></p>
		
		<h4><?php _e("Preview draft is now working!",'acf'); ?></h4>
		<p><?php _e("This bug has been squashed along with many other little critters!",'acf'); ?> <a class="acf-tab-toggle" href="#" data-tab="2"><?php _e("See the full changelog",'acf'); ?></a></p>
		
		<hr />
		
		<h3><?php _e("Important",'acf'); ?></h3>
		
		<h4><?php _e("Database Changes",'acf'); ?></h4>
		<p><?php _e("Absolutely <strong>no</strong> changes have been made to the database between versions 3 and 4. This means you can roll back to version 3 without any issues.",'acf'); ?></p>
		
		<h4><?php _e("Potential Issues",'acf'); ?></h4>
		<p><?php _e("Do to the sizable changes surounding Add-ons, field types and action/filters, your website may not operate correctly. It is important that you read the full",'acf'); ?> <a href="http://www.advancedcustomfields.com/resources/getting-started/migrating-from-v3-to-v4/" target="_blank"><?php _e("Migrating from v3 to v4",'acf'); ?></a> <?php _e("guide to view the full list of changes.",'acf'); ?></p>
		
		<div class="acf-alert acf-alert-error">
			<p><strong><?php _e("Really Important!",'acf'); ?></strong> <?php _e("If you updated the ACF plugin without prior knowledge of such changes, Please roll back to the latest",'acf'); ?> <a href="http://wordpress.org/extend/plugins/advanced-custom-fields/developers/"><?php _e("version 3",'acf'); ?></a> <?php _e("of this plugin.",'acf'); ?></p>
		</div>
		
		<hr />
		
		<h3><?php _e("Thank You",'acf'); ?></h3>
		<p><?php _e("A <strong>BIG</strong> thank you to everyone who has helped test the version 4 beta and for all the support I have received.",'acf'); ?></p>
		<p><?php _e("Without you all, this release would not have been possible!",'acf'); ?></p>
		
<?php elseif( $tab == 'changelog' ): ?>
		
		<h3><?php _e("Changelog for",'acf'); ?> <?php echo $version; ?></h3>
		<?php
		
		$items = file_get_contents( $path . 'readme.txt' );
		
		$items = end( explode('= ' . $version . ' =', $items) );
		$items = current( explode("\n\n", $items) );
		$items = array_filter( array_map('trim', explode("*", $items)) );
		
		?>
		<ul class="acf-changelog">
		<?php foreach( $items as $item ): 
			
			$item = explode('http', $item);
			
				
		?>
			<li><?php echo $item[0]; ?><?php if( isset($item[1]) ): ?><a href="http<?php echo $item[1]; ?>" target="_blank"><?php _e("Learn more",'acf'); ?></a><?php endif; ?></li>
		<?php endforeach; ?>
		</ul>
		
<?php endif; ?>

		
	</div>
	<!-- / acf-content-body -->
	
	
	<!-- acf-content-footer -->
	<div class="acf-content-footer">
		<ul class="hl clearfix">
			<li><a class="acf-button acf-button-big" href="<?php echo admin_url('edit.php?post_type=acf'); ?>"><?php _e("Awesome. Let's get to work",'acf'); ?></a></li>
		</ul>
	</div>
	<!-- / acf-content-footer -->
	
	
	
</div>
<!-- / acf-about -->
</script>
<script type="text/javascript">
(function($){
	
	// wrap
	$('#wpbody .wrap').attr('id', 'acf-field_groups');
	$('#acf-field_groups').wrapInner('<div id="acf-col-left" />');
	$('#acf-field_groups').wrapInner('<div id="acf-cols" />');
	
	
	// add sidebar
	$('#acf-cols').prepend( $('#tmpl-acf-col-right').html() );
	
	
	// take out h2 + icon
	$('#acf-col-left > .icon32').insertBefore('#acf-cols');
	$('#acf-col-left > h2').insertBefore('#acf-cols');
	
	
	<?php if( $show_tab ): ?>
	// add about copy
	$('#wpbody-content').prepend( $('#tmpl-acf-about').html() );
	$('#acf-field_groups').hide();
	$('#screen-meta-links').hide();
	<?php endif; ?>
	
})(jQuery);
</script>
		<?php
	}
			
}

new acf_field_groups();

?>