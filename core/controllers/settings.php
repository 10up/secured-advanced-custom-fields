<?php 

/*
*  Settings
*
*  @description: All the functionality for ACF Settings
*  @since 3.2.6
*  @created: 23/06/12
*/

 
class acf_settings 
{

	var $parent;
		
	
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
		add_action('admin_menu', array($this,'admin_menu'), 11);
		
	}
	
	
	/*
	*  admin_menu
	*
	*  @description: 
	*  @since 3.1.8
	*  @created: 23/06/12
	*/
	
	function admin_menu()
	{
		$page = add_submenu_page('edit.php?post_type=acf', __('Settings','acf'), __('Settings','acf'), 'manage_options','acf-settings',array($this,'html'));
		
		add_action('load-' . $page, array($this,'load'));
		
		add_action('admin_print_scripts-' . $page, array($this, 'admin_print_scripts'));
		add_action('admin_print_styles-' . $page, array($this, 'admin_print_styles'));
		
		add_action('admin_head-' . $page, array($this,'admin_head'));
		
	}
	
	
	/*
	*  load
	*
	*  @description: 
	*  @since 3.5.2
	*  @created: 16/11/12
	*  @thanks: Kevin Biloski and Charlie Eriksen via Secunia SVCRP
	*/
	
	function load()
	{
		// vars
		$defaults = array(
			'action' => ''
		);
		$options = array_merge($defaults, $_POST);
		

		if( $options['action'] == "export_xml" )
		{
			include_once($this->parent->path . 'core/actions/export.php');
			die;
		}
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
		wp_enqueue_script( 'wp-pointer' );
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
			'wp-pointer',
			'acf-global',
			'acf',
		));
	}
	
	
	/*
	*  admin_head
	*
	*  @description: 
	*  @since 3.1.8
	*  @created: 23/06/12
	*/
	
	function admin_head()
	{
		
		// Activate / Deactivate Add-ons
		if( isset($_POST['acf_field_deactivate']) )
		{
			// vars
			$message = "";
			$field = $_POST['acf_field_deactivate'];
			
			// delete field
			delete_option('acf_'.$field.'_ac');
			
			//set message
			if($field == "repeater")
			{
				$message = '<p>' . __("Repeater field deactivated",'acf') . '</p>';
			}
			elseif($field == "options_page")
			{
				$message = '<p>' . __("Options page deactivated",'acf') . '</p>';
			}
			elseif($field == "flexible_content")
			{
				$message = '<p>' . __("Flexible Content field deactivated",'acf') . '</p>';
			}
			elseif($field == "gallery")
			{
				$message = '<p>' . __("Gallery field deactivated",'acf') . '</p>';
			}
			
			// show message on page
			$this->parent->admin_message($message);
		}
		
		
		if( isset($_POST['acf_field_activate']) && isset($_POST['key']) )
		{
			// vars
			$message = "";
			$field = $_POST['acf_field_activate'];
			$key = trim($_POST['key']);
		
			// update option
			update_option('acf_'.$field.'_ac', $key);
			
			// did it unlock?
			if($this->parent->is_field_unlocked($field))
			{
				//set message
				if($field == "repeater")
				{
					$message = '<p>' . __("Repeater field activated",'acf') . '</p>';
				}
				elseif($field == "options_page")
				{
					$message = '<p>' . __("Options page activated",'acf') . '</p>';
				}
				elseif($field == "flexible_content")
				{
					$message = '<p>' . __("Flexible Content field activated",'acf') . '</p>';
				}
				elseif($field == "gallery")
				{
					$message = '<p>' . __("Gallery field activated",'acf') . '</p>';
				}
			}
			else
			{
				$message = '<p>' . __("License key unrecognised",'acf') . '</p>';
			}
			
			$this->parent->admin_message($message);
		}
	}
	
	
	/*
	*  html_index
	*
	*  @description: 
	*  @created: 9/08/12
	*/
	
	function html_index()
	{
		// vars
		$acfs = get_posts(array(
			'numberposts' 	=> -1,
			'post_type' 	=> 'acf',
			'orderby' 		=> 'menu_order title',
			'order' 		=> 'asc',
		));

		// blank array to hold acfs
		$choices = array();
		
		if($acfs)
		{
			foreach($acfs as $acf)
			{
				// find title. Could use get_the_title, but that uses get_post(), so I think this uses less Memory
				$title = apply_filters( 'the_title', $acf->post_title, $acf->ID );
				
				$choices[$acf->ID] = $title;
			}
		}
		
		?>
<table class="form-table acf-form-table">
	<tbody>
		<tr>
			<th scope="row">
				<h3><?php _e("Activate Add-ons.",'acf'); ?></h3>
				<p><?php _e("Add-ons can be unlocked by purchasing a license key. Each key can be used on multiple sites.",'acf'); ?></p>
				<p><a target="_blank" href="http://www.advancedcustomfields.com/add-ons/"><?php _e("Find Add-ons",'acf'); ?></a></p>
			</th>
			<td>
				<div class="wp-box">
				<table class="acf_activate widefat">
					<thead>
						<tr>
							<th><?php _e("Field Type",'acf'); ?></th>
							<th><?php _e("Status",'acf'); ?></th>
							<th style="width:50%;"><?php _e("Activation Code",'acf'); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?php _e("Repeater Field",'acf'); ?></td>
							<td><?php echo $this->parent->is_field_unlocked('repeater') ? __("Active",'acf') : __("Inactive",'acf'); ?></td>
							<td>
								<form action="" method="post">
									<?php if($this->parent->is_field_unlocked('repeater')){
										echo '<span class="activation_code">XXXX-XXXX-XXXX-'.substr($this->parent->get_license_key('repeater'),-4) .'</span>';
										echo '<input type="hidden" name="acf_field_deactivate" value="repeater" />';
										echo '<input type="submit" class="button" value="' . __("Deactivate",'acf') . '" />';
									}
									else
									{
										echo '<input type="text" name="key" value="" />';
										echo '<input type="hidden" name="acf_field_activate" value="repeater" />';
										echo '<input type="submit" class="button" value="' . __("Activate",'acf') . '" />';
									} ?>
								</form>
							</td>
						</tr>
						<tr>
							<td><?php _e("Flexible Content Field",'acf'); ?></td>
							<td><?php echo $this->parent->is_field_unlocked('flexible_content') ? __("Active",'acf') : __("Inactive",'acf'); ?></td>
							<td>
								<form action="" method="post">
									<?php if($this->parent->is_field_unlocked('flexible_content')){
										echo '<span class="activation_code">XXXX-XXXX-XXXX-'.substr($this->parent->get_license_key('flexible_content'),-4) .'</span>';
										echo '<input type="hidden" name="acf_field_deactivate" value="flexible_content" />';
										echo '<input type="submit" class="button" value="' . __("Deactivate",'acf') . '" />';
									}
									else
									{
										echo '<input type="text" name="key" value="" />';
										echo '<input type="hidden" name="acf_field_activate" value="flexible_content" />';
										echo '<input type="submit" class="button" value="' . __("Activate",'acf') . '" />';
									} ?>
								</form>
							</td>
						</tr>
						<tr>
							<td><?php _e("Gallery Field",'acf'); ?></td>
							<td><?php echo $this->parent->is_field_unlocked('gallery') ? __("Active",'acf') : __("Inactive",'acf'); ?></td>
							<td>
								<form action="" method="post">
									<?php if($this->parent->is_field_unlocked('gallery')){
										echo '<span class="activation_code">XXXX-XXXX-XXXX-'.substr($this->parent->get_license_key('gallery'),-4) .'</span>';
										echo '<input type="hidden" name="acf_field_deactivate" value="gallery" />';
										echo '<input type="submit" class="button" value="' . __("Deactivate",'acf') . '" />';
									}
									else
									{
										echo '<input type="text" name="key" value="" />';
										echo '<input type="hidden" name="acf_field_activate" value="gallery" />';
										echo '<input type="submit" class="button" value="' . __("Activate",'acf') . '" />';
									} ?>
								</form>
							</td>
						</tr>
						<tr>
							<td><?php _e("Options Page",'acf'); ?></td>
							<td><?php echo $this->parent->is_field_unlocked('options_page') ? __("Active",'acf') : __("Inactive",'acf'); ?></td>
							<td>
								<form action="" method="post">
									<?php if($this->parent->is_field_unlocked('options_page')){
										echo '<span class="activation_code">XXXX-XXXX-XXXX-'.substr($this->parent->get_license_key('options_page'),-4) .'</span>';
										echo '<input type="hidden" name="acf_field_deactivate" value="options_page" />';
										echo '<input type="submit" class="button" value="' . __("Deactivate",'acf') . '" />';
									}
									else
									{
										echo '<input type="text" name="key" value="" />';
										echo '<input type="hidden" name="acf_field_activate" value="options_page" />';
										echo '<input type="submit" class="button" value="' . __("Activate",'acf') . '" />';
									} ?>
								</form>
							</td>
						</tr>
					</tbody>
				</table>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<h3><?php _e("Export Field Groups to XML",'acf'); ?></h3>
				<p><?php _e("ACF will create a .xml export file which is compatible with the native WP import plugin.",'acf'); ?></p>
				<p><a href="#" class="show-pointer" rel="xml-import-instructions-html"><?php _e("Instructions",'acf'); ?></a></p>
				<div id="xml-import-instructions-html" style="display:none;">
					<h3><?php _e("Import Field Groups",'acf'); ?></h3>
					<p><?php _e("Imported field groups <b>will</b> appear in the list of editable field groups. This is useful for migrating fields groups between Wp websites.",'acf'); ?></p>
					<ol>
						<li><?php _e("Select field group(s) from the list and click \"Export XML\"",'acf'); ?></li>
						<li><?php _e("Save the .xml file when prompted",'acf'); ?></li>
						<li><?php _e("Navigate to Tools &raquo; Import and select WordPress",'acf'); ?></li>
						<li><?php _e("Install WP import plugin if prompted",'acf'); ?></li>
						<li><?php _e("Upload and import your exported .xml file",'acf'); ?></li>
						<li><?php _e("Select your user and ignore Import Attachments",'acf'); ?></li>
						<li><?php _e("That's it! Happy WordPressing",'acf'); ?></li>
					</ol>
				</div>
			</th>
			<td>
				<form class="acf-export-form" method="post">
					<input type="hidden" name="action" value="export_xml" />
					<?php

					$this->parent->create_field(array(
						'type'	=>	'select',
						'name'	=>	'acf_posts',
						'value'	=>	'',
						'choices'	=>	$choices,
						'multiple'	=>	1,
					));
					
					?>
					<ul class="hl clearfix">
						<li class="right"><input type="submit" class="acf-button" value="<?php _e("Export XML",'acf'); ?>" /></li>
					</ul>
				</form>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<h3><?php _e("Export Field Groups to PHP",'acf'); ?></h3>
				<p><?php _e("ACF will create the PHP code to include in your theme.",'acf'); ?></p>
				<p><a href="#" class="show-pointer" rel="php-import-instructions-html"><?php _e("Instructions",'acf'); ?></a></p>
				<div id="php-import-instructions-html" style="display:none;">
					<h3><?php _e("Register Field Groups",'acf'); ?></h3>
					<p><?php _e("Registered field groups <b>will not</b> appear in the list of editable field groups. This is useful for including fields in themes.",'acf'); ?></p>
					<p><?php _e("Please note that if you export and register field groups within the same WP, you will see duplicate fields on your edit screens. To fix this, please move the origional field group to the trash or remove the code from your functions.php file.",'acf'); ?></p>
					<ol>
						<li><?php _e("Select field group(s) from the list and click \"Create PHP\"",'acf'); ?></li>
						<li><?php _e("Copy the PHP code generated",'acf'); ?></li>
						<li><?php _e("Paste into your functions.php file",'acf'); ?></li>
						<li><?php _e("To activate any Add-ons, edit and use the code in the first few lines.",'acf'); ?></li>
					</ol>
				</div>
			</th>
			<td>
				<form class="acf-export-form" method="post">
					<input type="hidden" name="action" value="export_php" />
					<?php
					
					$this->parent->create_field(array(
						'type'	=>	'select',
						'name'	=>	'acf_posts',
						'value'	=>	'',
						'choices'	=>	$choices,
						'multiple'	=>	1,
					));
					
					?>
					<ul class="hl clearfix">
						<li class="right"><input type="submit" class="acf-button" value="<?php esc_attr_e("Create PHP",'acf'); ?>" /></li>
					</ul>
				</form>
			</td>
		</tr>
	</tbody>
</table>
<script type="text/javascript">
(function($){
	
	$(document).ready(function(){
		
		$('a.show-pointer').each(function(){
			
			// vars
			var a = $(this),
				html = a.attr('rel');
			
			
			// create pointer
			a.pointer({
		        content: $('#' + html).html(),
		        position: {
		            my: 'left bottom',
		            at: 'left top',
		            edge: 'bottom',
		        },
		        close: function() {
		        	
		        	a.removeClass('open');
		        	
		        }
		    });
		    
		    
		    // click
		    a.click(function(){
		    
			    if( a.hasClass('open') )
			    {
				    a.removeClass('open');
			    }
			    else
			    {
				    a.addClass('open');
			    }
			    
		    });
		    
		    
		    // show on hover
		    a.hover(function(){
		    
			    $(this).pointer('open');
			    
		    }, function(){
		    	
		    	if( ! a.hasClass('open') )
		    	{
			    	$(this).pointer('close');
		    	}
			    
		    });
			
		});
		
	});
	
})(jQuery);
</script>
<?php

	}
	
	
	/*
	*  html_php
	*
	*  @description: 
	*  @created: 9/08/12
	*/
	
	function html_php()
	{
		
		?>
<p><a href="">&laquo; <?php _e("Back to settings",'acf'); ?></a></p>
<table class="form-table acf-form-table">
	<tbody>
		<tr>
			<th scope="row">
				<h3><?php _e("Register Field Groups",'acf'); ?></h3>
				<p><?php _e("Registered field groups <b>will not</b> appear in the list of editable field groups. This is useful for including fields in themes.",'acf'); ?></p>
				<p><?php _e("Please note that if you export and register field groups within the same WP, you will see duplicate fields on your edit screens. To fix this, please move the origional field group to the trash or remove the code from your functions.php file.",'acf'); ?></p>
				<ol>
					<li><?php _e("Copy the PHP code generated",'acf'); ?></li>
					<li><?php _e("Paste into your functions.php file",'acf'); ?></li>
					<li><?php _e("To activate any Add-ons, edit and use the code in the first few lines.",'acf'); ?></li>
				</ol>
				
				<br />
				
				<h3><?php _e("ACF Lite",'acf'); ?></h3>
				<p><?php _e("Advanced Custom Fields has a lite version to be included in premium themes. You can find out more on github",'acf'); ?> <a href="https://github.com/elliotcondon/acf/" target="_blank"><?php _e("here",'acf'); ?></a>.</p>
				
			</th>
			<td valign="top">
				<div class="wp-box">
					<div class="inner">
						<textarea class="pre" readonly="true"><?php
		
		$acfs = array();
		
		if(isset($_POST['acf_posts']))
		{
			$acfs = get_posts(array(
				'numberposts' 	=> -1,
				'post_type' 	=> 'acf',
				'orderby' 		=> 'menu_order title',
				'order' 		=> 'asc',
				'include'		=>	$_POST['acf_posts'],
			));
		}
		if($acfs)
		{
			?>
<?php _e("/**
 * Activate Add-ons
 * Here you can enter your activation codes to unlock Add-ons to use in your theme. 
 * Since all activation codes are multi-site licenses, you are allowed to include your key in premium themes.
 */",'acf'); ?>
 

function my_acf_settings( $options )
{
    // activate add-ons
    $options['activation_codes']['repeater'] = 'XXXX-XXXX-XXXX-XXXX';
    $options['activation_codes']['options_page'] = 'XXXX-XXXX-XXXX-XXXX';
    $options['activation_codes']['flexible_content'] = 'XXXX-XXXX-XXXX-XXXX';
    $options['activation_codes']['gallery'] = 'XXXX-XXXX-XXXX-XXXX';
    
    // setup other options (http://www.advancedcustomfields.com/docs/filters/acf_settings/)
    
    return $options;
    
}
add_filter('acf_settings', 'my_acf_settings');


<?php _e("/**
 * Register field groups
 * The register_field_group function accepts 1 array which holds the relevant data to register a field group
 * You may edit the array as you see fit. However, this may result in errors if the array is not compatible with ACF
 * This code must run every time the functions.php file is read
 */",'acf'); ?>


if(function_exists("register_field_group"))
{
<?php
			foreach($acfs as $acf)
			{
				$var = array(
					'id' => uniqid(),
					'title' => get_the_title($acf->ID),
					'fields' => $this->parent->get_acf_fields($acf->ID),
					'location' => $this->parent->get_acf_location($acf->ID),
					'options' => $this->parent->get_acf_options($acf->ID),
					'menu_order' => $acf->menu_order,
				);
				
				$html = var_export($var, true);
				
				// change double spaces to tabs
				$html = str_replace("  ", "\t", $html);
				
				// add extra tab at start of each line
				$html = str_replace("\n", "\n\t", $html);
				
?>	register_field_group(<?php echo $html ?>);
<?php
			}
?>
}
<?php
		}
		else
		{
			_e("No field groups were selected",'acf');
		}
						?></textarea>
					</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>
<script type="text/javascript">
(function($){
	
	var i = 0;
	
	$('textarea.pre').live( 'mousedown', function (){
		
		if( i == 0 )
		{
			i++;
			
			$(this).focus().select();
			
			return false;
		}
				
	});
	
	
	$('textarea.pre').live( 'keyup', function (){
	    $(this).height( 0 );
	    $(this).height( this.scrollHeight );
	});

	
	$(document).ready(function(){
		
		$('textarea.pre').trigger('keyup');

	});

})(jQuery);
</script>
	<?php
	}
	
	
	/*
	*  html
	*
	*  @description: 
	*  @since 3.1.8
	*  @created: 23/06/12
	*/
	
	function html()
	{
		// vars
		$defaults = array(
			'action' => ''
		);
		$options = array_merge($defaults, $_POST);
				
		?>
<div class="wrap">

	<div class="icon32" id="icon-acf"><br></div>
	<h2 style="margin: 4px 0 25px;"><?php _e("Advanced Custom Fields Settings",'acf'); ?></h2>
		<?php
		
		if( $options['action'] == "export_php" )
		{
			$this->html_php();
		}
		else
		{
			$this->html_index();
		}
		
		?>
</div>
		<?php
		
		return;
		
	}
	
			
}

?>