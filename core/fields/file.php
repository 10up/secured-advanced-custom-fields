<?php

class acf_File extends acf_Field
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
    	
    	$this->name = 'file';
		$this->title = __('File','acf');
		
		add_action('admin_head-media-upload-popup', array($this, 'popup_head'));
		add_filter('get_media_item_args', array($this, 'allow_file_insertion'));
		add_action('wp_ajax_acf_select_file', array($this, 'ajax_select_file'));
		add_action('acf_head-update_attachment-file', array($this, 'acf_head_update_attachment'));
   	}
   	
   	
   	/*
   	*  acf_head_update_attachment
   	*
   	*  @description: 
   	*  @since: 3.2.7
   	*  @created: 4/07/12
   	*/
   	
   	function acf_head_update_attachment()
	{
		?>
<script type="text/javascript">
(function($){
	
	// vars
	var div = self.parent.acf_edit_attachment;
	
	
	// add message
	self.parent.acf.add_message("<?php _e("File Updated.",'acf'); ?>", div);
	

})(jQuery);
</script>
		<?php
	}
   	
   	
   	/*--------------------------------------------------------------------------------------
	*
	*	render_file
	*
	*	@description : Renders the file html from an ID
	*	@author Elliot Condon
	*	@since 3.1.6
	* 
	*-------------------------------------------------------------------------------------*/
	
   	function render_file($id = null)
   	{
   		if(!$id)
   		{
   			echo "";
   			return;
   		}
   		
   		
   		// vars
		$file_src = wp_get_attachment_url($id);
		preg_match("~[^/]*$~", $file_src, $file_name);
		$class = "active";
   		
   		
   		?>
		<ul class="hl clearfix">
			<li data-mime="<?php echo get_post_mime_type( $id ) ; ?>">
				<img class="acf-file-icon" src="<?php echo wp_mime_type_icon( $id ); ?>" alt=""/>
			</li>
			<li>
				<span class="acf-file-name"><?php echo $file_name[0]; ?></span><br />
				<a href="#" class="edit-file"><?php _e('Edit','acf'); ?></a> 
				<a href="#" class="remove-file"><?php _e('Remove','acf'); ?></a>
			</li>
		</ul>
		<?php
   		
   	}
   	
   	/*--------------------------------------------------------------------------------------
	*
	*	ajax_select_file
	*
	*	@description ajax function to provide url of selected file
	*	@author Elliot Condon
	*	@since 3.1.5
	* 
	*-------------------------------------------------------------------------------------*/
	
   	function ajax_select_file()
   	{
   		$id = isset($_POST['id']) ? $_POST['id'] : false;
   				
		
		// attachment ID is required
   		if(!$id)
   		{
   			echo "";
   			die();
   		}
   		
   		$this->render_file($id);
   		
		die();
   	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	allow_file_insertion
	*
	*	@author Elliot Condon
	*	@since 3.0.1
	* 
	*-------------------------------------------------------------------------------------*/
	
	function allow_file_insertion($vars)
	{
	    $vars['send'] = true;
	    return($vars);
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
		$class = $field['value'] ? "active" : "";
		
		?>
		<div class="acf-file-uploader <?php echo $class; ?>">
			<input class="value" type="hidden" name="<?php echo $field['name']; ?>" value="<?php echo $field['value']; ?>" />
			<div class="has-file">
				<?php $this->render_file( $field['value'] ); ?>
			</div>
			<div class="no-file">
				<ul class="hl clearfix">
					<li>
						<span class="acf-file-name"><?php _e('No File Selected','acf'); ?></span>. <a href="#" class="button add-file"><?php _e('Add File','acf'); ?></a>
					</li>
				</ul>
			</div>
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
			'save_format'	=>	'object',
		);
		
		$field = array_merge($defaults, $field);

		?>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Return Value",'acf'); ?></label>
			</td>
			<td>
				<?php 
				$this->parent->create_field(array(
					'type'	=>	'radio',
					'name'	=>	'fields['.$key.'][save_format]',
					'value'	=>	$field['save_format'],
					'layout'	=>	'horizontal',
					'choices' => array(
						'object'	=>	__("File Object",'acf'),
						'url'		=>	__("File URL",'acf'),
						'id'		=>	__("File ID",'acf')
					)
				));
				?>
			</td>
		</tr>
		<?php
	}
	
	
	/*
	*  popup_head
	*
	*  @description: css + js for thickbox
	*  @since: 1.1.4
	*  @created: 7/12/12
	*/
	
	function popup_head()
	{
		// options
		$defaults = array(
			'acf_type' => '',
			'tab'	=>	'type',	
		);
		
		$options = array_merge($defaults, $_GET);
		
		
		// validate
		if( $options['acf_type'] != 'file' )
		{
			return;
		}
		
		
		// update attachment
		if( isset($_POST["attachments"]) )
		{
			echo '<div class="updated"><p>' . __("Media attachment updated.") . '</p></div>';
		}
		
					
?><style type="text/css">
	#media-upload-header #sidemenu li#tab-type_url,
	#media-items .media-item a.toggle,
	#media-items .media-item tr.image-size,
	#media-items .media-item tr.align,
	#media-items .media-item tr.url,
	#media-items .media-item .slidetoggle {
		display: none !important;
	}
	
	#media-items .media-item {
		position: relative;
		overflow: hidden;
	}
	
	#media-items .media-item .acf-checkbox {
		float: left;
		margin: 28px 10px 0;
	}
	
	#media-items .media-item .pinkynail {
		max-width: 64px;
		max-height: 64px;
		display: block !important;
		margin: 2px;
	}
	
	#media-items .media-item .filename.new {
		min-height: 0;
		padding: 10px;
		line-height: 15px;
	}
	
	#media-items .media-item .title {
		line-height: 14px;
	}
	
	#media-items .media-item .acf-select {
		float: right;
		margin: 22px 12px 0 10px;
	}
	
	#media-upload .ml-submit {
		display: none !important;
	}

	#media-upload .acf-submit {
		margin: 1em 0;
		padding: 1em 0;
		position: relative;
		overflow: hidden;
		display: none; /* default is hidden */
		clear: both;
	}
	
	#media-upload .acf-submit a {
		float: left;
		margin: 0 10px 0 0;
	}
	
	#media-items .media-item .acf-filename {
		color: #999;
		font-size: 11px;
		margin: 0 0 3px;
		display: block;
	}
	
	
<?php if( $options['tab'] == 'gallery' ): ?>
	#sort-buttons,
	#gallery-form > .widefat,
	#media-items .menu_order,
	#gallery-settings {
		display: none !important;
	}
<?php endif; ?>

</style>
<script type="text/javascript">
(function($){
	
	/*
	*  Vars
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 11/12/12
	*/
	
	var options = {
		id : []
	};
	
	
	/*
	*  add_next_file
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 11/12/12
	*/
	
	function add_next_file()
	{
		// vars
		var next_id = options.id[0],
			ajax_data = {
				action	:	'acf_select_file',
				id		:	next_id
			};
		
		
		// ajax
		$.post( ajaxurl, ajax_data, function( html ){
			
			// validate
			if( !html )
			{
				return false;
			}
			
			
			// add file to acf_div
			self.parent.acf_div.find('input.value').val( next_id ).trigger('change');
			self.parent.acf_div.find('.has-file').html(html);
 			self.parent.acf_div.addClass('active');
 			
 			
 			// validation
 			self.parent.acf_div.closest('.field').removeClass('error');
 			
 			
 			// remove first id from array
 			options.id.splice(0, 1);
 			
 			
 			// are there more id's to add? (multiple selection for repeater)
 			if( options.id.length > 0 ) 
 			{ 
 				// add row 
 				self.parent.acf_div.closest('.repeater').find('.add-row-end').trigger('click'); 
 			 
 				// set acf_div to new row file 
 				self.parent.acf_div = self.parent.acf_div.closest('.repeater').find('> table > tbody > tr.row:last .acf-file-uploader');
 				
 				// add the next file
 				add_next_file();
 			} 
 			else 
 			{ 
 				// reset acf_div and return false 
				self.parent.acf_div = null; 
				self.parent.tb_remove(); 
 			} 
 	 
		});
	}
	
	
	/*
	*  Select File
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 11/12/12
	*/
	
	$('#media-items .media-item a.acf-select').live('click', function(){
		
		// vars
		var new_id = $(this).attr('href');
		
		
		// IE7 Fix
		if( new_id.indexOf("/") != -1 )
		{
			var split = new_id.split("/");
			new_id = split[ split.length-1 ];
		}
		
		
		// add to id array
		options.id.push( new_id );
		
		
		// add the next file
 		add_next_file();
 				
 						
		return false;
	});
	
	
	/*
	*  Select Files
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 11/12/12
	*/
	
	$('#acf-add-selected').live('click', function(){ 
		 
		// check total 
		var total = $('#media-items .media-item .acf-checkbox:checked').length;
		if( total == 0 ) 
		{ 
			alert("<?php _e("No files selected",'acf'); ?>"); 
			return false; 
		} 
		
		
		// add to id array
		$('#media-items .media-item .acf-checkbox:checked').each(function(){
		
			options.id.push( $(this).val() );
			
		});
		 
		 
		// add the next file
 		add_next_file();
 		
 		
		return false; 
		 
	}); 
	
	
	/*
	*  Edit Attachment Toggle
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 11/12/12
	*/
	
	$('#media-items .media-item a.acf-toggle-edit').live('click', function(){
		
		// vars
		var a = $(this),
			item = a.closest('.media-item');
		
		
		// toggle
		if( a.hasClass('active') )
		{
			a.removeClass('active');
			item.find('.slidetoggle').attr('style', 'display: none !important');
		}
		else
		{
			a.addClass('active');
			item.find('.slidetoggle').attr('style', 'display: table !important');
		}
		
		
		// return
		return false;
		
	});
	
	
	/*
	*  add_buttons
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 11/12/12
	*/

	function add_buttons()
	{
		// vars
		var is_sub_field = (self.parent.acf_div && self.parent.acf_div.closest('.repeater').length > 0) ? true : false;
		
		
		// add submit after media items (on for sub fields)
		if($('.acf-submit').length == 0 && is_sub_field)
		{
			$('#media-items').after('<div class="acf-submit"><a id="acf-add-selected" class="button"><?php _e("Add Selected Files",'acf'); ?></a></div>');
		}
		
		
		// add buttons to media items
		$('#media-items .media-item:not(.acf-active)').each(function(){
			
			// show the add all button
			$('.acf-submit').show();
			
			// needs attachment ID
			if($(this).children('input[id*="type-of-"]').length == 0){ return false; }
			
			// only once!
			$(this).addClass('acf-active');
			
			// find id
			var id = $(this).children('input[id*="type-of-"]').attr('id').replace('type-of-', '');
			
			// if inside repeater, add checkbox
			if(is_sub_field)
			{
				$(this).prepend('<input type="checkbox" class="acf-checkbox" value="' + id + '" <?php if( $options['tab'] == 'type' ){echo 'checked="checked"';} ?> />');
			}
			
			// find file url
			var file_url = $(this).find('.slidetoggle tr.url .urlfile').attr('data-link-url');
			$(this).find('.filename.new').append('<span class="acf-filename">' + file_url + '</span>');
			
			// Add edit button
			$(this).find('.filename.new').append('<a href="#" class="acf-toggle-edit">Edit</a>');
			
			// Add select button
			$(this).find('.filename.new').before('<a href="' + id + '" class="button acf-select"><?php _e("Select File",'acf'); ?></a>');
			
			// add save changes button
			$(this).find('tr.submit input.button').hide().before('<input type="submit" value="<?php _e("Update File",'acf'); ?>" class="button savebutton" />');
			
		});
	}
	<?php
	
	// run the acf_add_buttons ever 500ms when on the file upload tab
	if( $options['tab'] == 'type' ): ?>
	var acf_t = setInterval(function(){
		add_buttons();
	}, 500);
	<?php endif; ?>
	
	
	// add acf input filters to allow for tab navigation
	$(document).ready(function(){
		
		setTimeout(function(){
			add_buttons();
		}, 1);
		
		
		$('form#filter').each(function(){
			
			$(this).append('<input type="hidden" name="acf_type" value="file" />');
						
		});
		
		$('form#image-form, form#library-form').each(function(){
			
			var action = $(this).attr('action');
			action += "&acf_type=file";
			$(this).attr('action', action);
			
		});
	});
				
})(jQuery);
</script><?php

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
			'save_format'	=>	'object',
		);
		
		$field = array_merge($defaults, $field);
		
		$value = parent::get_value($post_id, $field);
		
		
		// validate
		if( !$value )
		{
			return false;
		}
		
		
		// format
		if( $field['save_format'] == 'url' )
		{
			$value = wp_get_attachment_url($value);
		}
		elseif( $field['save_format'] == 'object' )
		{
			$attachment = get_post( $value );
			
			
			// validate
			if( !$attachment )
			{
				return false;	
			}
			
			
			// create array to hold value data
			$value = array(
				'id' => $attachment->ID,
				'alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
				'title' => $attachment->post_title,
				'caption' => $attachment->post_excerpt,
				'description' => $attachment->post_content,
				'url' => wp_get_attachment_url( $attachment->ID ),
			);
		}
		
		return $value;
	}
	
}

?>
