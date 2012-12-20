<?php

class acf_Image extends acf_Field
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
    	
    	$this->name = 'image';
		$this->title = __('Image','acf');
		
		add_action('admin_head-media-upload-popup', array($this, 'popup_head'));
		add_filter('get_media_item_args', array($this, 'allow_img_insertion'));
		add_action('wp_ajax_acf_get_preview_image', array($this, 'acf_get_preview_image'));
		add_action('acf_head-update_attachment-image', array($this, 'acf_head_update_attachment'));
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
	self.parent.acf.add_message("<?php _e("Image Updated.",'acf'); ?>", div);
	

})(jQuery);
</script>
		<?php
	}
	
   	/*--------------------------------------------------------------------------------------
	*
	*	acf_get_preview_image
	*
	*	@description 		Returns a json array of preview sized urls
	*	@author 			Elliot Condon
	*	@since 				3.1.7
	* 
	*-------------------------------------------------------------------------------------*/
	
   	function acf_get_preview_image()
   	{
   		$options = array(
   			'id' => false,
   			'preview_size' => 'thumbnail'
   		);
   		$options = array_merge($options, $_GET);
   		
   		
   		
   		// vars
		$return = array();
		
		
		// validate
		if( ! $options['id'] )
		{
			die( 0 );
		}
		
		
		// convert id_string into an array
		$ids = explode(',' , $options['id']);
		if( ! is_array($ids) )
		{
			$ids = array( $options['id'] );
		}
		
		
		// find image preview url for each image
		foreach( $ids as $k => $v )
		{
			$url = wp_get_attachment_image_src( $v, $options['preview_size'] );
			$return[] = array(
				'id' => $v,
				'url' => $url[0],
			);
		}
   		

		// return json
		echo json_encode( $return );
		die();
   	}
   	
   	
   	/*--------------------------------------------------------------------------------------
	*
	*	admin_print_scripts / admin_print_styles
	*
	*	@author Elliot Condon
	*	@since 3.0.1
	* 
	*-------------------------------------------------------------------------------------*/
	
	function allow_img_insertion($vars)
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
		$class = "";
		$file_src = "";
		$preview_size = isset($field['preview_size']) ? $field['preview_size'] : 'thumbnail';
		
		// get image url
		if($field['value'] != '' && is_numeric($field['value']))
		{
			$file_src = wp_get_attachment_image_src($field['value'], $preview_size);
			$file_src = $file_src[0];
			
			if($file_src)
			{
				$class = "active";
			}
		}
		
		?>
<div class="acf-image-uploader clearfix <?php echo $class; ?>" data-preview_size="<?php echo $preview_size; ?>">
	<input class="value" type="hidden" name="<?php echo $field['name']; ?>" value="<?php echo $field['value']; ?>" />
	<div class="has-image">
		<div class="hover">
			<ul class="bl">
				<li><a class="acf-button-delete ir" href="#"><?php _e("Remove",'acf'); ?></a></li>
				<li><a class="acf-button-edit ir" href="#"><?php _e("Edit",'acf'); ?></a></li>
			</ul>
		</div>
		<img src="<?php echo $file_src; ?>" alt=""/>
	</div>
	<div class="no-image">
		<p><?php _e('No image selected','acf'); ?> <input type="button" class="button add-image" value="<?php _e('Add Image','acf'); ?>" />
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
			'preview_size'	=>	'thumbnail',
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
						'object'	=>	__("Image Object",'acf'),
						'url'		=>	__("Image URL",'acf'),
						'id'		=>	__("Image ID",'acf')
					)
				));
				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Preview Size",'acf'); ?></label>
			</td>
			<td>
				<?php
				
				$image_sizes = $this->parent->get_all_image_sizes();
				
				$this->parent->create_field(array(
					'type'	=>	'radio',
					'name'	=>	'fields['.$key.'][preview_size]',
					'value'	=>	$field['preview_size'],
					'layout'	=>	'horizontal',
					'choices' => $image_sizes
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
			'acf_preview_size' => 'thumbnail',
			'tab'	=>	'type',	
		);
		
		$options = array_merge($defaults, $_GET);
		
		
		// validate
		if( $options['acf_type'] != 'image' )
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
		padding: 20px 10px 10px 10px;
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
	*  Select Image
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 11/12/12
	*/
	
	$('#media-items .media-item a.acf-select').live('click', function(){
		
		var id = $(this).attr('href');
		
		
		// IE7 Fix
		if( id.indexOf("/") != -1 )
		{
			var split = id.split("/");
			id = split[split.length-1];
		}
		
		
		var data = {
			action: 'acf_get_preview_image',
			id: id,
			preview_size : "<?php echo $options['acf_preview_size']; ?>"
		};
	
		
		// ajax
		$.ajax({
			url: ajaxurl,
			data : data,
			cache: false,
			dataType: "json",
			success: function( json ) {
		    	

				// validate
				if(!json)
				{
					return false;
				}
				
				
				// get item
				var item = json[0],
					div = self.parent.acf_div;
				
				
				// update acf_div
				div.find('input.value').val( item.id ).trigger('change');
	 			div.find('img').attr( 'src', item.url );
	 			div.addClass('active');
	 	
	 	
	 			// validation
	 			div.closest('.field').removeClass('error');
	 			
	 			
	 			// reset acf_div and return false
	 			self.parent.acf_div = null;
	 			self.parent.tb_remove();
	 	
	 	
			}
		});
		
		return false;
		
	});
	
	
	/*
	*  Select Images
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
			alert("<?php _e("No images selected",'acf'); ?>"); 
			return false; 
		} 
		
		
		// generate id's
		var attachment_ids = [];
		$('#media-items .media-item .acf-checkbox:checked').each(function(){
			attachment_ids.push( $(this).val() );
		});
		
		
		// creae json data
		var data = {
			action: 'acf_get_preview_image',
			id: attachment_ids.join(','),
			preview_size : "<?php echo $options['acf_preview_size']; ?>"
		};
		
		
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.getJSON(ajaxurl, data, function( json ) {
			
			// validate
			if(!json)
			{
				return false;
			}
			
			$.each(json, function(i ,item){
			
				// update acf_div
				self.parent.acf_div.find('input.value').val( item.id ).trigger('change'); 
	 			self.parent.acf_div.find('img').attr('src', item.url ); 
	 			self.parent.acf_div.addClass('active'); 
	 	 
	 	 
	 			// validation 
	 			self.parent.acf_div.closest('.field').removeClass('error'); 
	
	 			 
	 			if((i+1) < total) 
	 			{ 
	 				// add row 
	 				self.parent.acf_div.closest('.repeater').find('.add-row-end').trigger('click'); 
	 			 
	 				// set acf_div to new row image 
	 				self.parent.acf_div = self.parent.acf_div.closest('.repeater').find('> table > tbody > tr.row:last .acf-image-uploader'); 
	 			} 
	 			else 
	 			{ 
	 				// reset acf_div and return false 
					self.parent.acf_div = null; 
					self.parent.tb_remove(); 
	 			} 
				
    		});

			
		
		});
		
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
			$('#media-items').after('<div class="acf-submit"><a id="acf-add-selected" class="button"><?php _e("Add Selected Images",'acf'); ?></a></div>');
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
				$(this).prepend('<input type="checkbox" class="acf-checkbox" value="' + id + '" <?php if( $options['tab'] == "type" ){echo 'checked="checked"';} ?> />');
			}
			
			// Add edit button
			$(this).find('.filename.new').append('<br /><a href="#" class="acf-toggle-edit">Edit</a>');
			
			// Add select button
			$(this).find('.filename.new').before('<a href="' + id + '" class="button acf-select"><?php _e("Select Image",'acf'); ?></a>');
			
			// add save changes button
			$(this).find('tr.submit input.button').hide().before('<input type="submit" value="<?php _e("Update Image",'acf'); ?>" class="button savebutton" />');

			
		});
	}
	<?php
	
	// run the acf_add_buttons ever 500ms when on the image upload tab
	if( $options['tab'] == "type" ): ?>
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
			
			$(this).append('<input type="hidden" name="acf_preview_size" value="<?php echo $options['acf_preview_size']; ?>" />');
			$(this).append('<input type="hidden" name="acf_type" value="image" />');
						
		});
		
		$('form#image-form, form#library-form').each(function(){
			
			var action = $(this).attr('action');
			action += "&acf_type=image&acf_preview_size=<?php echo $options['acf_preview_size']; ?>";
			$(this).attr('action', action);
			
		});
		
		
		<?php
	
		// add support for media tags
		
		if( $options['tab'] == 'mediatags' ): ?>
		$('#media-items .mediatag-item-count a').each(function(){
			
			var href = $(this).attr('href');
			href += "&acf_type=image&acf_preview_size=<?php echo $options['acf_preview_size']; ?>";
			$(this).attr('href', href);
			
		});
		<?php endif; ?>
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
		$format = isset($field['save_format']) ? $field['save_format'] : 'url';
		$value = parent::get_value($post_id, $field);
		
		
		// validate
		if( !$value )
		{
			return false;
		}
		
		
		// format
		if($format == 'url')
		{
			$value = wp_get_attachment_url($value);
		}
		elseif($format == 'object')
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
				'sizes' => array(),
			);
			
			// find all image sizes
			$image_sizes = get_intermediate_image_sizes();
			
			if( $image_sizes )
			{
				foreach( $image_sizes as $image_size )
				{
					// find src
					$src = wp_get_attachment_image_src( $attachment->ID, $image_size );
					
					// add src
					$value['sizes'][$image_size] = $src[0];
				}
				// foreach( $image_sizes as $image_size )
			}
			// if( $image_sizes )
			
		}
		
		return $value;
	}
	
	
		
}

?>
