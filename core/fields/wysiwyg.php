<?php

class acf_Wysiwyg extends acf_Field
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
    	
    	$this->name = 'wysiwyg';
		$this->title = __("Wysiwyg Editor",'acf');
		
		add_action( 'acf_head-input', array( $this, 'acf_head') );
		
		add_filter( 'acf/fields/wysiwyg/toolbars', array( $this, 'toolbars'), 1, 1 );

   	}
   	
   	
   	/*
   	*  get_toolbars
   	*
   	*  @description: 
   	*  @since: 3.5.7
   	*  @created: 10/01/13
   	*/
   	
   	function toolbars( $toolbars )
   	{
   		$editor_id = 'acf_settings';
   		
   		// Full
   		$toolbars['Full'] = array();
   		$toolbars['Full'][1] = apply_filters('mce_buttons', array('bold', 'italic', 'strikethrough', 'bullist', 'numlist', 'blockquote', 'justifyleft', 'justifycenter', 'justifyright', 'link', 'unlink', 'wp_more', 'spellchecker', 'fullscreen', 'wp_adv' ), $editor_id);
   		$toolbars['Full'][2] = apply_filters('mce_buttons_2', array( 'formatselect', 'underline', 'justifyfull', 'forecolor', 'pastetext', 'pasteword', 'removeformat', 'charmap', 'outdent', 'indent', 'undo', 'redo', 'wp_help', 'code' ), $editor_id);
   		$toolbars['Full'][3] = apply_filters('mce_buttons_3', array(), $editor_id);
   		$toolbars['Full'][4] = apply_filters('mce_buttons_4', array(), $editor_id);
   		
   		
   		// Basic
   		$toolbars['Basic'] = array();
   		$toolbars['Basic'][1] = apply_filters( 'teeny_mce_buttons', array('bold', 'italic', 'underline', 'blockquote', 'strikethrough', 'bullist', 'numlist', 'justifyleft', 'justifycenter', 'justifyright', 'undo', 'redo', 'link', 'unlink', 'fullscreen'), $editor_id );
   		
   		
   		// Custom - can be added with acf/fields/wysiwyg/toolbars filter
   	
	   	return $toolbars;
   	}
   	
	
	
   	/*--------------------------------------------------------------------------------------
	*
	*	admin_head
	*	- Add the settings for a WYSIWYG editor (as used in wp_editor / wp_tiny_mce)
	*
	*	@author Elliot Condon
	*	@since 3.2.3
	* 
	*-------------------------------------------------------------------------------------*/
	
   	function acf_head()
   	{
   		add_action( 'admin_footer', array( $this, 'admin_footer') );
   	}
   	
   	
   	function admin_footer()
   	{
	   	?>
	   	<div style="display:none;">
	   	<?php wp_editor( '', 'acf_settings' ); ?>
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
			'toolbar'		=>	'full',
			'media_upload' 	=>	'yes',
			'the_content' 	=>	'yes',
			'default_value'	=>	'',
		);
		
		$field = array_merge($defaults, $field);
		
		?>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Default Value",'acf'); ?></label>
			</td>
			<td>
				<?php 
				do_action('acf/create_field', array(
					'type'	=>	'textarea',
					'name'	=>	'fields['.$key.'][default_value]',
					'value'	=>	$field['default_value'],
				));
				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Toolbar",'acf'); ?></label>
			</td>
			<td>
				<?php
				
				$toolbars = apply_filters( 'acf/fields/wysiwyg/toolbars', array() );
				$choices = array();
				
				if( is_array($toolbars) )
				{
					foreach( $toolbars as $k => $v )
					{
						$label = $k;
						$name = sanitize_title( $label );
						$name = str_replace('-', '_', $name);
						
						$choices[ $name ] = $label;
					}
				}
				
				do_action('acf/create_field', array(
					'type'	=>	'radio',
					'name'	=>	'fields['.$key.'][toolbar]',
					'value'	=>	$field['toolbar'],
					'layout'	=>	'horizontal',
					'choices' => $choices
				));
				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Show Media Upload Buttons?",'acf'); ?></label>
			</td>
			<td>
				<?php 
				do_action('acf/create_field', array(
					'type'	=>	'radio',
					'name'	=>	'fields['.$key.'][media_upload]',
					'value'	=>	$field['media_upload'],
					'layout'	=>	'horizontal',
					'choices' => array(
						'yes'	=>	__("Yes",'acf'),
						'no'	=>	__("No",'acf'),
					)
				));
				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Run filter \"the_content\"?",'acf'); ?></label>
				<p class="description"><?php _e("Enable this filter to use shortcodes within the WYSIWYG field",'acf'); ?></p>
				<p class="description"><?php _e("Disable this filter if you encounter recursive template problems with plugins / themes",'acf'); ?></p>
			</td>
			<td>
				<?php 
				do_action('acf/create_field', array(
					'type'	=>	'radio',
					'name'	=>	'fields['.$key.'][the_content]',
					'value'	=>	$field['the_content'],
					'layout'	=>	'horizontal',
					'choices' => array(
						'yes'	=>	__("Yes",'acf'),
						'no'	=>	__("No",'acf'),
					)
				));
				?>
			</td>
		</tr>
		<?php
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
		global $wp_version;
		
		
		// vars
		$defaults = array(
			'toolbar'		=>	'full',
			'media_upload' 	=>	'yes',
		);
		$field = array_merge($defaults, $field);
		
		$id = 'wysiwyg-' . $field['id'];
		
		
		?>
		<div id="wp-<?php echo $id; ?>-wrap" class="acf_wysiwyg wp-editor-wrap" data-toolbar="<?php echo $field['toolbar']; ?>">
			<?php if($field['media_upload'] == 'yes'): ?>
				<?php if( version_compare($wp_version, '3.3', '<') ): ?>
					<div id="editor-toolbar">
						<div id="media-buttons" class="hide-if-no-js">
							<?php do_action( 'media_buttons' ); ?>
						</div>
					</div>
				<?php else: ?>
					<div id="wp-<?php echo $id; ?>-editor-tools" class="wp-editor-tools">
						<div id="wp-<?php echo $id; ?>-media-buttons" class="hide-if-no-js wp-media-buttons">
							<?php do_action( 'media_buttons' ); ?>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>
			<div id="wp-<?php echo $id; ?>-editor-container" class="wp-editor-container">
				<textarea id="<?php echo $id; ?>" class="wp-editor-area" name="<?php echo $field['name']; ?>" ><?php echo wp_richedit_pre($field['value']); ?></textarea>
			</div>
		</div>
		
		<?php

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
		// vars
		$defaults = array(
			'the_content' 	=>	'yes',
		);
		$field = array_merge($defaults, $field);
		$value = parent::get_value($post_id, $field);
		
		
		// filter
		if( $field['the_content'] == 'yes' )
		{
			$value = apply_filters('the_content',$value); 
		}
		else
		{
			$value = wpautop( $value );
		}

		
		return $value;
	}
	

}

?>