/*
*  Input Actions
*
*  @description: javascript for fields functionality		
*  @author: Elliot Condon
*  @since: 3.1.4
*/

var acf = {
	admin_url : '',
	wp_version : '0',
	post_id : 0,
	validation : false,
	text : {
		'validation_error' : "Validation Failed. One or more fields below are required.",
		'relationship_max_alert' : "Maximum values reached ( {max} values )",
		'repeater_min_alert' : "Minimum rows reached ( {min} rows )",
		'repeater_max_alert' : "Maximum rows reached ( {max} rows )"
	},
	conditional_logic : {},
	sortable_helper : null,
	media : {
		frame : null,
		div : null,
		set_multiple : function(){},
		set_title : function(){}
	},
	fields : {
		image : {
			div : null,
			add : function(){},
			edit : function(){},
			remove : function(){},
			text : {
				title_add : "Select Image",
				title_edit : "Edit Image"
			}
		},
		file : {
			div : null,
			add : function(){},
			edit : function(){},
			remove : function(){},
			text : {
				title_add : "Select File",
				title_edit : "Edit File"
			}
		},
		wysiwyg : {
			toolbars : {}
		},
		gallery : {
			div : null,
			add : function(){},
			update_count : function(){},
			hide_selected_items : function(){},
			text : {
				title_add : "Select Images"
			}
		}
	}
};

(function($){
	
	
	/**
	 * Simply compares two string version values.
	 * 
	 * Example:
	 * versionCompare('1.1', '1.2') => -1
	 * versionCompare('1.1', '1.1') =>  0
	 * versionCompare('1.2', '1.1') =>  1
	 * versionCompare('2.23.3', '2.22.3') => 1
	 * 
	 * Returns:
	 * -1 = left is LOWER than right
	 *  0 = they are equal
	 *  1 = left is GREATER = right is LOWER
	 *  And FALSE if one of input versions are not valid
	 *
	 * @function
	 * @param {String} left  Version #1
	 * @param {String} right Version #2
	 * @return {Integer|Boolean}
	 * @author Alexey Bass (albass)
	 * @since 2011-07-14
	 */
	 
	acf.version_compare = function(left, right)
	{
	    if (typeof left + typeof right != 'stringstring')
	        return false;
	    
	    var a = left.split('.')
	    ,   b = right.split('.')
	    ,   i = 0, len = Math.max(a.length, b.length);
	        
	    for (; i < len; i++) {
	        if ((a[i] && !b[i] && parseInt(a[i]) > 0) || (parseInt(a[i]) > parseInt(b[i]))) {
	            return 1;
	        } else if ((b[i] && !a[i] && parseInt(b[i]) > 0) || (parseInt(a[i]) < parseInt(b[i]))) {
	            return -1;
	        }
	    }
	    
	    return 0;
	};
	
	
	/*
	*  Exists
	*
	*  @description: returns true / false		
	*  @created: 1/03/2011
	*/
	
	$.fn.exists = function()
	{
		return $(this).length>0;
	};
	
	
	/*
	*  Document Ready
	*
	*  @description: adds ajax data
	*  @created: 1/03/2011
	*/
	
	$(document).ready(function(){
		
		
		/*
		*  Media
		*/
		
		if( typeof(wp) == "object" )
		{
			// Create the media frame. Leave options blank for defaults
			acf.media.frame = wp.media();
			
	
			// event fired when viewing "Media Library"
			acf.media.frame.on('all', function( e ){
				
				console.log( e );
			});
			
			acf.media.frame.on('content:activate:browse', function(){
				
				$(document).trigger('acf/media/browse');
				
				// event fired after a search
				acf.media.frame.content.get().collection.on( 'add', function( item ){
				    $(document).trigger('acf/media/add' );
			    });
			    
			    // event fired after a search
				acf.media.frame.content.get().collection.on( 'reset', function( item ){
				
					$(document).trigger('acf/media/reset');
					/*
setTimeout(function(){
						$(document).trigger('acf/media/browse', [acf.media.frame.content.get().collection] );
					}, 10);
*/
				    
			    });
			});
			
			
			// event fired when viewing "Upload Files"
			acf.media.frame.on('content:activate:upload', function(){
				$(document).trigger('acf/media/upload');
			});
	
			// event fired when opening the media popup
			acf.media.frame.on('open', function( e ){
				
				acf.media.frame.content.get().attachments.render();
				acf.media.frame.reset();
				$(document).trigger('acf/media/open');
				
				
		    });
		    
		    acf.media.frame.on('reset', function( e ){
				$(document).trigger('acf/media/reset');
				
				console.log('main reset');
		    });
		    
		    
		    // event fired when closing the media popup
			acf.media.frame.on('close', function( e ){
				
				// clear the div
				$(document).trigger('acf/media/close');
		    });
		    
		    
			// When an image is selected, run a callback.
		    acf.media.frame.on('select', function()
		    {
		    	$(document).trigger('acf/media/select', [acf.media.frame.state().get('selection')]);
		    });
		    
		}
		
		
		
		/*
		*  Color Picker
		*/

		if( $.farbtastic )
		{
			if( !acf.farbtastic )
			{
				$('body').append('<div id="acf_color_picker" />');
		
				acf.farbtastic = $.farbtastic('#acf_color_picker');
			}
		}
		
		
		/*
		*  Show / Hide Metaboxes
		*/
		
		// add classes
		$('#poststuff .postbox[id*="acf_"]').addClass('acf_postbox');
		$('#adv-settings label[for*="acf_"]').addClass('acf_hide_label');
		
		// hide acf stuff
		$('#poststuff .acf_postbox').addClass('acf-hidden');
		$('#adv-settings .acf_hide_label').hide();
		
		// loop through acf metaboxes
		$('#poststuff .postbox.acf_postbox').each(function(){
			
			// vars
			var options = $(this).find('> .inside > .options'),
				show = options.attr('data-show'),
				layout = options.attr('data-layout'),
				id = $(this).attr('id').replace('acf_', '');
			
			// layout
			$(this).addClass(layout);
			
			// show / hide
			if( show == "1" )
			{
				$(this).removeClass('acf-hidden');
				$('#adv-settings .acf_hide_label[for="acf_' + id + '-hide"]').show();
			}
			
		});
	
	});
	
	
	/*
	*  3.5 Media
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 16/01/13
	*/
	
	acf.media.set_multiple = function( val )
	{
		acf.media.frame.content.get().options.selection.multiple = val;
	};
	
	acf.media.set_title = function( val )
	{
		acf.media.frame.title.get().$el.text( val );
	};

	
	/*
	*  Save Draft
	*
	*  @description: 
	*  @created: 18/09/12
	*/
	var save_post = false;
	$('#save-post').live('click', function(){
		
		save_post = true;
		
	});
	
	
	/*
	*  Submit form
	*
	*  @description: does validation, deletes all hidden metaboxes (otherwise, post data will be overriden by hidden inputs)
	*  @created: 1/03/2011
	*/
	
	$('form#post').live("submit", function(){
		
		if( ! save_post )
		{
			// do validation
			do_validation();
			
			
			if( ! acf.validation )
			{
				// show message
				$(this).siblings('#message').remove();
				$(this).before('<div id="message" class="error"><p>' + acf.text.validation_error + '</p></div>');
				
				
				// hide ajax stuff on submit button
				$('#publish').removeClass('button-primary-disabled');
				$('#ajax-loading').attr('style','');
				$('#publishing-action .spinner').hide();
				
				return false;
			}
		}

		
		// remove hidden postboxes
		$('.acf_postbox.acf-hidden').remove();
		
		
		// submit the form
		return true;
		
	});
	
	
	/*
	*  do_validation
	*
	*  @description: checks fields for required input
	*  @created: 1/03/2011
	*/
	
	function do_validation(){
		
		acf.validation = true;
		
		$('.postbox:not(.acf-hidden) .field.required, .form-field.required').each(function(){
			
			// vars
			var div = $(this);
			
			
			// set validation data
			div.data('validation', true);
			

			// if is hidden by conditional logic, ignore
			if( div.hasClass('acf-conditional_logic-hide') )
			{
				return;
			}
			
			
			// text / textarea
			if( div.find('input[type="text"], input[type="number"], input[type="hidden"], textarea').val() == "" )
			{
				div.data('validation', false);
			}
			
			
			// wysiwyg
			if( div.find('.acf_wysiwyg').exists() && typeof(tinyMCE) == "object")
			{
				div.data('validation', true);
				
				var id = div.find('.wp-editor-area').attr('id'),
					editor = tinyMCE.get( id );

				if( ! editor.getContent() )
				{
					div.data('validation', false);
				}
			}
			
			
			// select
			if( div.find('select').exists() )
			{
				div.data('validation', true);

				if( div.find('select').val() == "null" || ! div.find('select').val() )
				{
					div.data('validation', false);
				}
			}

			
			// checkbox
			if( div.find('input[type="checkbox"]:checked').exists() )
			{
				div.data('validation', true);
			}
			
			
			// relationship
			if( div.find('.acf_relationship').exists() )
			{
				div.data('validation', false);
				
				if( div.find('.acf_relationship .relationship_right input').exists() )
				{
					div.data('validation', true);
				}
			}
			
			
			// repeater
			if( div.find('.repeater').exists() )
			{
				div.data('validation', false);
				
				if( div.find('.repeater tr.row').exists() )
				{
					div.data('validation', true);
				}			
			}
			
			
			// flexible content
			if( div.find('.acf_flexible_content').exists() )
			{
				div.data('validation', false);
				if( div.find('.acf_flexible_content .values table').exists() )
				{
					div.data('validation', true);
				}	
			}
			
			
			// gallery
			if( div.find('.acf-gallery').exists() )
			{
				div.data('validation', false);
				
				if( div.find('.acf-gallery .thumbnail').exists())
				{
					div.data('validation', true);
				}
			}
			
			
			// hook for custom validation
			$(document).trigger('acf/validate_field', div );
			
			
			// set validation
			if( ! div.data('validation') )
			{
				acf.validation = false;
				div.closest('.field').addClass('error');
			}
			
		});
		
		
	}
	
	
	/*
	*  Remove error class on focus
	*
	*  @description: 
	*  @created: 1/03/2011
	*/

	// inputs / textareas
	$('.field.required input, .field.required textarea, .field.required select').live('focus', function(){
		$(this).closest('.field').removeClass('error');
	});
	
	// checkbox
	$('.field.required input:checkbox').live('click', function(){
		$(this).closest('.field').removeClass('error');
	});
	
	
	/*
	*  Field: Color Picker
	*
	*  @description: 
	*  @created: 1/03/2011
	*/
	
	
	// update colors
	$(document).live('acf/setup_fields', function(e, postbox){
		
		// validate
		if( ! $.farbtastic )
		{
			return;
		}
		

		$(postbox).find('input.acf_color_picker').each(function(){
			
			// vars
			var input = $(this);

			
			// is clone field?
			if( acf.is_clone_field(input) )
			{
				return;
			}
			

			if( input.val() )
			{
				$.farbtastic( input ).setColor( input.val() ).hsl[2] > 0.5 ? color = '#000' : color = '#fff';
				
				input.css({ 
					backgroundColor : input.val(),
					color : color
				});
			}
			
		});
		
	});
	
				
	$('input.acf_color_picker').live('focus', function(){
		
		var input = $(this);
		
		if( ! input.val() )
		{
			input.val( '#FFFFFF' );
		}
		
		$('#acf_color_picker').css({
			left: input.offset().left,
			top: input.offset().top - $('#acf_color_picker').height(),
			display: 'block'
		});
		
		acf.farbtastic.linkTo(this);
		
	}).live('blur', function(){
		
		var input = $(this);
		
		
		// reset the css
		if( ! input.val() )
		{
			input.css({ 
				backgroundColor : '#fff',
				color : '#000'
			});
			
		}
		
		
		$('#acf_color_picker').css({
			display: 'none'
		});
						
	});
	
	
	/*
	*  Field: File
	*
	*  @description: 
	*  @created: 1/03/2011
	*/
	
	// add
	acf.fields.file.add = function( file )
	{

		// vars
		var div = acf.media.div;
		
		// set atts
		div.find('.acf-file-value').val( file.id ).trigger('change');
	 	div.find('.acf-file-icon').attr( 'src', file.icon );
	 	div.find('.acf-file-name').text( file.name );
	 	
	 	
	 	// set div class
	 	div.addClass('active');
	 	
	 	
	 	// validation
		div.closest('.field').removeClass('error');

	};
	
	
	// edit
	acf.fields.file.edit = function(){
		
		// vars
		var div = acf.media.div,
			id = div.find('.acf-file-value').val();
		
		
		// show edit attachment
		tb_show( acf.fields.file.text.title_edit , acf.admin_url + 'media.php?attachment_id=' + id + '&action=edit&acf_action=edit_attachment&acf_field=image&TB_iframe=1');
		
	};
	
	
	// remove 
	acf.fields.file.remove = function()
	{
		// vars
		var div = acf.media.div;
		
		
		// remove atts
		div.find('.acf-file-value').val( '' ).trigger('change');
	 	div.find('.acf-file-icon').attr( 'src', '' );
	 	div.find('.acf-file-name').text( '' );
		
		
		// remove class
		div.removeClass('active');
		
	};
	
	
	// Media title / Button
	$(document).live('acf/media/open', function(e, selection){
		
		// validate
		if( !acf.media.div.hasClass('acf-file-uploader') )
		{
			return;
		}
		
		
		// vars
		var div = acf.media.div,
			multiple = div.closest('.repeater').exists() ? true : false;
		
		
		// set media atts
		acf.media.set_multiple( multiple );
		acf.media.set_title( acf.fields.file.text.title_add );
		
	});
	
	
	$(document).live('acf/media/select', function(e, selection){
		
		// validate
		if( !acf.media.div.hasClass('acf-file-uploader') )
		{
			return;
		}
		
		
		// vars
		var div = acf.media.div,
			i = 0;
		
		    		
	    selection.each(function(attachment){

	    	// counter
	    	i++;
	    	
	    	
	    	// select / add another file field?
	    	if( i > 1 )
			{
				var tr = acf.media.div.closest('tr'),
					repeater = tr.closest('.repeater');
				
				
				if( tr.next('.row').exists() )
				{
					acf.media.div = tr.next('.row').find('.acf-file-uploader');
				}
				else
				{
					// add row 
	 				repeater.find('.add-row-end').trigger('click'); 
	 			 
	 				// set acf_div to new row file 
	 				acf.media.div = repeater.find('> table > tbody > tr.row:last .acf-file-uploader');
				}
			}
			
			
	    	// vars
	    	var file = {
		    	id : attachment.id,
		    	name : attachment.attributes.filename,
		    	icon : attachment.attributes.icon
	    	};
	    	
	    	
	    	// add file to field
	        acf.fields.file.add( file );
	        
	    });
	    
	});
	
	
	
	
	// add file
	$('.acf-file-uploader .add-file').live('click', function(){
				
		// vars
		var div = acf.media.div = $(this).closest('.acf-file-uploader');
		

		// show the thickbox
		if( acf.media.frame )
		{
		    acf.media.frame.open();
		}
		else
		{	
			tb_show( acf.fields.file.text.title_add , acf.admin_url + 'media-upload.php?post_id=' + acf.post_id + '&post_ID=' + acf.post_id + '&type=file&acf_type=file&TB_iframe=1');
		}
		
		return false;
		
	});
	
	
	// remove file
	$('.acf-file-uploader .remove-file').live('click', function(){
		
		// vars
		acf.media.div = $(this).closest('.acf-file-uploader');
				

		acf.fields.file.remove();
		
		
		return false;
			
	});
	
	
	// edit image
	$('.acf-file-uploader .edit-file').live('click', function(){
		
		// vars
		acf.media.div = $(this).closest('.acf-file-uploader');
				

		acf.fields.file.edit();
		
		
		return false;
			
	});
	
	
	/*
	*  Image
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 13/01/13
	*/
	
	acf.fields.image.add = function( image )
	{
		// vars
		var div = acf.media.div;
		
		// set atts
		div.find('.acf-image-value').val( image.id ).trigger('change');
	 	div.find('img').attr( 'src', image.src );
	 	
	 	
	 	// set div class
	 	div.addClass('active');
	 	
	 	
	 	// validation
		div.closest('.field').removeClass('error');

	};
	
	
	// edit image
	acf.fields.image.edit = function(){
		
		// vars
		var div = acf.media.div,
			id = div.find('.acf-image-value').val();
		
		
		// show edit attachment
		tb_show( acf.fields.image.text.title_edit , acf.admin_url + 'media.php?attachment_id=' + id + '&action=edit&acf_action=edit_attachment&acf_field=image&TB_iframe=1');
		
	};
	
	
	// remove Image
	acf.fields.image.remove = function()
	{
		// vars
		var div = acf.media.div;
		
		
		// remove atts
		div.find('.acf-image-value').val('').trigger('change');
		div.find('img').attr('src', '');
		
		
		// remove class
		div.removeClass('active');
		
	};
	
	
	// Media title / Button
	$(document).live('acf/media/open', function(e, selection){
		
		// validate
		if( !acf.media.div.hasClass('acf-image-uploader') )
		{
			return;
		}
		
		
		// vars
		var div = acf.media.div,
			multiple = div.closest('.repeater').exists() ? true : false;
		
		
		// set media atts
		acf.media.set_multiple( multiple );
		acf.media.set_title( acf.fields.image.text.title_add );
		
	});
	
	
	$(document).live('acf/media/select', function(e, selection){
		
		// validate
		if( !acf.media.div.hasClass('acf-image-uploader') )
		{
			return;
		}
		
		
		// vars
		var div = acf.media.div,
			preview_size = div.attr('data-preview_size'),
			i = 0;
		
		    		
	    selection.each(function(attachment){

	    	// counter
	    	i++;
	    	
	    	
	    	// select / add another file field?
	    	if( i > 1 )
			{
				var tr = acf.media.div.closest('tr'),
					repeater = tr.closest('.repeater');
				
				
				if( tr.next('.row').exists() )
				{
					acf.media.div = tr.next('.row').find('.acf-image-uploader');
				}
				else
				{
					// add row 
	 				repeater.find('.add-row-end').trigger('click'); 
	 			 
	 				// set acf_div to new row file 
	 				acf.media.div = repeater.find('> table > tbody > tr.row:last .acf-image-uploader');
				}
			}
			
			
	    	// vars
	    	var image = {
		    	id : attachment.id,
		    	src : attachment.attributes.url
	    	};
	    	

	    	// is preview size available?
	    	if( attachment.attributes.sizes[ preview_size ] )
	    	{
		    	image.src = attachment.attributes.sizes[ preview_size ].url;
	    	}
	    	
	    	
	    	// add image to field
	        acf.fields.image.add( image );
	        
			
	    });
		
		
		
	});
	
	
	// add image
	$('.acf-image-uploader .add-image').live('click', function(){
				
		// vars
		var div = acf.media.div = $(this).closest('.acf-image-uploader'),
			preview_size = div.attr('data-preview_size');
		

		// show the thickbox
		if( acf.media.frame )
		{
		    acf.media.frame.open();
		}
		else
		{
			tb_show( acf.fields.image.text.title_add , acf.admin_url + 'media-upload.php?post_id=' + acf.post_id + '&post_ID=' + acf.post_id + '&type=image&acf_type=image&acf_preview_size=' + preview_size + 'TB_iframe=1');
		}
		
	
		return false;
	});
	
	
	// remove image
	$('.acf-image-uploader .acf-button-delete').live('click', function(){
		
		// vars
		acf.media.file = $(this).closest('.acf-image-uploader');
				

		acf.fields.image.remove();
		
		
		return false;
			
	});
	
	
	// edit image
	$('.acf-image-uploader .acf-button-edit').live('click', function(){
		
		// vars
		acf.media.div = $(this).closest('.acf-image-uploader');
				

		acf.fields.image.edit();
		
		
		return false;
			
	});
		
	
	/*
	*  Field: Relationship
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 11/12/12
	*/
	
	// add sortable
	$(document).live('acf/setup_fields', function(e, postbox){
		
		$(postbox).find('.acf_relationship').each(function(){
			
			// is clone field?
			if( acf.is_clone_field($(this).children('input[type="hidden"]')) )
			{
				return;
			}
			
			
			$(this).find('.relationship_right .relationship_list').sortable({
				axis: "y", // limit the dragging to up/down only
				items: '> li',
				forceHelperSize: true,
				forcePlaceholderSize: true,
				scroll: true
			});
			
			
			// load more
			$(this).find('.relationship_left .relationship_list').scrollTop(0).scroll( function(){
				
				// vars
				var div = $(this).closest('.acf_relationship');
				
				
				// validate
				if( div.hasClass('loading') )
				{
					return;
				}
				
				
				// Scrolled to bottom
				if( $(this).scrollTop() + $(this).innerHeight() >= $(this).get(0).scrollHeight )
				{
					var paged = parseInt( div.attr('data-paged') );
					
					div.attr('data-paged', (paged + 1) );
					
					acf.relationship_update_results( div );
				}

			});
			
			
			// ajax fetch values for left side
			acf.relationship_update_results( $(this) );
			
		});
		
	});
	
	
	// add from left to right
	$('.acf_relationship .relationship_left .relationship_list a').live('click', function(){
		
		// vars
		var id = $(this).attr('data-post_id'),
			title = $(this).html(),
			div = $(this).closest('.acf_relationship'),
			max = parseInt(div.attr('data-max')),
			right = div.find('.relationship_right .relationship_list');
		
		
		// max posts
		if( right.find('a').length >= max )
		{
			alert( acf.text.relationship_max_alert.replace('{max}', max) );
			return false;
		}
		
		
		// can be added?
		if( $(this).parent().hasClass('hide') )
		{
			return false;
		}
		
		
		// hide / show
		$(this).parent().addClass('hide');
		
		
		// create new li for right side
		var new_li = div.children('.tmpl-li').html()
			.replace( /\{post_id}/gi, id )
			.replace( /\{title}/gi, title );
			


		// add new li
		right.append( new_li );
		
		
		// validation
		div.closest('.field').removeClass('error');
		
		return false;
		
	});
	
	
	// remove from right to left
	$('.acf_relationship .relationship_right .relationship_list a').live('click', function(){
		
		// vars
		var id = $(this).attr('data-post_id'),
			div = $(this).closest('.acf_relationship'),
			left = div.find('.relationship_left .relationship_list');
		
		
		// hide
		$(this).parent().remove();
		
		
		// show
		left.find('a[data-post_id="' + id + '"]').parent('li').removeClass('hide');
		
		
		return false;
		
	});
	
	
	// search
	$('.acf_relationship input.relationship_search').live('keyup', function()
	{	
		// vars
		var val = $(this).val(),
			div = $(this).closest('.acf_relationship');
			
		
		// update data-s
	    div.attr('data-s', val);
	    
	    
	    // new search, reset paged
	    div.attr('data-paged', 1);
	    
	    
	    // ajax
	    clearTimeout( acf.relationship_timeout );
	    acf.relationship_timeout = setTimeout(function(){
	    	acf.relationship_update_results( div );
	    }, 250);
	    
	    return false;
	    
	})
	.live('focus', function(){
		$(this).siblings('label').hide();
	})
	.live('blur', function(){
		if($(this).val() == "")
		{
			$(this).siblings('label').show();
		}
	});
	
	
	// hide results
	acf.relationship_hide_results = function( div ){
		
		// vars
		var left = div.find('.relationship_left .relationship_list'),
			right = div.find('.relationship_right .relationship_list');
			
			
		// apply .hide to left li's
		left.find('a').each(function(){
			
			var id = $(this).attr('data-post_id');
			
			if( right.find('a[data-post_id="' + id + '"]').exists() )
			{
				$(this).parent().addClass('hide');
			}
			
		});
		
	}
	
	
	// update results
	acf.relationship_update_results = function( div ){
		
		
		// add loading class, stops scroll loading
		div.addClass('loading');
		
		
		// vars
		var s = div.attr('data-s'),
			paged = parseInt( div.attr('data-paged') ),
			taxonomy = div.attr('data-taxonomy'),
			post_type = div.attr('data-post_type'),
			lang = div.attr('data-lang'),
			left = div.find('.relationship_left .relationship_list'),
			right = div.find('.relationship_right .relationship_list');
		
		
		// get results
	    $.ajax({
			url: ajaxurl,
			type: 'post',
			dataType: 'html',
			data: { 
				'action' : 'acf_get_relationship_results', 
				's' : s,
				'paged' : paged,
				'taxonomy' : taxonomy,
				'post_type' : post_type,
				'lang' : lang,
				'field_name' : div.parent().attr('data-field_name'),
				'field_key' : div.parent().attr('data-field_key')
			},
			success: function( html ){
				
				div.removeClass('no-results').removeClass('loading');
				
				// new search?
				if( paged == 1 )
				{
					left.find('li:not(.load-more)').remove();
				}
				
				
				// no results?
				if( !html )
				{
					div.addClass('no-results');
					return;
				}
				
				
				// append new results
				left.find('.load-more').before( html );
				
				
				// less than 10 results?
				var ul = $('<ul>' + html + '</ul>');
				if( ul.find('li').length < 10 )
				{
					div.addClass('no-results');
				}
				
				
				// hide values
				acf.relationship_hide_results( div );
				
			}
		});
	};
	
	
	/*
	*  acf/wysiwyg_activate
	*
	*  @description: 
	*  @created: 3/03/2011
	*/
	
	$(document).live('acf/wysiwyg_activate', function(e, div){
		
		// validate tinymce
		if( typeof(tinyMCE) != "object" )
		{
			return;
		}
		
		

		// activate
		$(div).find('.acf_wysiwyg textarea').each(function(){

			// vars
			var textarea = $(this),
				id = textarea.attr('id'),
				toolbar = textarea.closest('.acf_wysiwyg').attr('data-toolbar');
			
			
			// is clone field?
			if( acf.is_clone_field(textarea) )
			{
				return;
			}
			
			
			// reset tinyMCE settings
			tinyMCE.settings.theme_advanced_buttons1 = '';
			tinyMCE.settings.theme_advanced_buttons2 = '';
			tinyMCE.settings.theme_advanced_buttons3 = '';
			tinyMCE.settings.theme_advanced_buttons4 = '';
			
			if( acf.fields.wysiwyg.toolbars[ toolbar ] )
			{
				$.each( acf.fields.wysiwyg.toolbars[ toolbar ], function( k, v ){
					tinyMCE.settings[ k ] = v;
				})
			}
			
			
			// add functionality back in
			tinyMCE.execCommand("mceAddControl", false, id);
			
			
			// events - load
			$(document).trigger('acf/wysiwyg/load', id);
			
			
			// add events (click, focus, blur) for inserting image into correct editor
			acf.add_wysiwyg_events( id );
			
		});
		
		
		wpActiveEditor = null;

	});
	
	
	/*
	*  add_wysiwyg_events
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 16/12/12
	*/
	
	acf.add_wysiwyg_events = function( id ){
		
		// validate tinymce
		if( typeof(tinyMCE) != "object" )
		{
			return;
		}
		
		
		var editor = tinyMCE.get( id );
		
		if( !editor )
		{
			return;
		}
		
		
		var	container = $('#wp-' + id + '-wrap'),
			body = $( editor.getBody() );


		container.click(function(){
			$(document).trigger('acf/wysiwyg/click', id);
		});
		
		body.focus(function(){
			$(document).trigger('acf/wysiwyg/focus', id);
		}).blur(function(){
			$(document).trigger('acf/wysiwyg/blur', id);
		});
		
	};
	
	
	/*
	*  acf/wysiwyg_deactivate
	*
	*  @description: 
	*  @created: 3/03/2011
	*/
	
	$(document).live('acf/wysiwyg_deactivate', function(e, div){
		
		// validate tinymce
		if( typeof(tinyMCE) != "object" )
		{
			return;
		}
		
		
		$(div).find('.acf_wysiwyg textarea').each(function(){
			
			// vars
			var textarea = $(this),
				id = textarea.attr('id'),
				wysiwyg = tinyMCE.get( id );
			
			
			// if wysiwyg was found (should be always...), remove its functionality and set the value (to keep line breaks)
			if( wysiwyg )
			{
				var val = wysiwyg.getContent();
				
				tinyMCE.execCommand("mceRemoveControl", false, id);
			
				textarea.val( val );
			}
			
		});
		
		
		wpActiveEditor = null;

	});
	
	
	// set active wysiwyg
	$(document).live('acf/wysiwyg/click', function(e, id){
		
		wpActiveEditor = id;
		
		container = $('#wp-' + id + '-wrap').closest('.field').removeClass('error');
		
	}).live('acf/wysiwyg/focus', function(e, id){
		
		wpActiveEditor = id;
		
		container = $('#wp-' + id + '-wrap').closest('.field').removeClass('error');
		
	}).live('acf/wysiwyg/blur', function(e, id){
		
		wpActiveEditor = null;
		
	});
	
	
	// create wysiwygs
	$(document).live('acf/setup_fields', function(e, div){
		
		$(document).trigger('acf/wysiwyg_activate', div);

	});

	
	/*
	*  window load
	*
	*  @description: 
	*  @since: 3.5.5
	*  @created: 22/12/12
	*/
	
	$(window).load(function(){
		
		// vars
		var wp_content = $('#wp-content-wrap').exists(),
			wp_acf_settings = $('#wp-acf_settings-wrap').exists()
			mode = 'tmce';
		
		
		// has_editor
		if( wp_acf_settings )
		{
			// html_mode
			if( $('#wp-acf_settings-wrap').hasClass('html-active') )
			{
				mode = 'html';
			}
		}
		
		
		setTimeout(function(){
			
			// trigger click on hidden wysiwyg (to get in HTML mode)
			if( wp_acf_settings && mode == 'html' )
			{
				$('#acf_settings-tmce').trigger('click');
			}
			
		}, 1);
		
		
		setTimeout(function(){

			// setup fields
			$(document).trigger('acf/setup_fields', $('#poststuff'));
			
			
			// trigger html mode for people who want to stay in HTML mode
			if( wp_acf_settings && mode == 'html' )
			{
				$('#acf_settings-html').trigger('click');
			}
			
			// Add events to content editor
			if( wp_content )
			{
				acf.add_wysiwyg_events( 'content' );
			}
			
			
		}, 10);
		
	});
	
	
	/*
	*  Sortable Helper
	*
	*  @description: keeps widths of td's inside a tr
	*  @since 3.5.1
	*  @created: 10/11/12
	*/
	
	acf.sortable_helper = function(e, ui)
	{
		ui.children().each(function(){
			$(this).width($(this).width());
		});
		return ui;
	};


	/*
	*  acf/sortable_start
	*
	*  @description:
	*  @since 3.5.1
	*  @created: 10/11/12
	*/
	
	$(document).live('acf/sortable_start', function(e, div) {
		
		$(document).trigger('acf/wysiwyg_deactivate', div);
		
	});
	
	
	/*
	*  acf/sortable_stop
	*
	*  @description:
	*  @since 3.5.1
	*  @created: 10/11/12
	*/
	
	$(document).live('acf/sortable_stop', function(e, div) {
		
		$(document).trigger('acf/wysiwyg_activate', div);
		
	});
	
	
	/*
	*  Field: Repeater
	*
	*  @description: 
	*  @created: 3/03/2011
	*/
	
	// create a unique id
	function uniqid()
    {
    	var newDate = new Date;
    	return newDate.getTime();
    }
    
	
	// update order
	function repeater_update_order( repeater )
	{
		repeater.find('> table > tbody > tr.row').each(function(i){
			$(this).children('td.order').html( i+1 );
		});
	
	};
	
	
	// setup repeater fields
	$(document).live('acf/setup_fields', function(e, postbox){
		
		$(postbox).find('.repeater').each(function(){
			
			var repeater = $(this)
			
			
			// set column widths
			repeater_set_column_widths( repeater );
			
			
			// update classes based on row count
			repeater_update_classes( repeater );
			
			
			// add sortable
			repeater_add_sortable( repeater );
						
		});
			
	});
	
	
	/*
	*  repeater_set_column_widths
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 11/11/12
	*/
	
	function repeater_set_column_widths( repeater )
	{
		// validate
		if( repeater.children('.acf-input-table').hasClass('row_layout') )
		{
			return;
		}
		

		// accomodate for order / remove
		var column_width = 100;
		if( repeater.find('> .acf-input-table > thead > tr > th.order').exists() )
		{
			column_width = 93;
		}
		
		
		// find columns that already have a width and remove these amounts from the column_width var
		repeater.find('> .acf-input-table  > thead > tr > th[width]').each(function( i ){
			
			column_width -= parseInt( $(this).attr('width') );
		});

		
		var ths = repeater.find('> .acf-input-table > thead > tr > th').not('[width]').has('span');
		if( ths.length > 1 )
		{
			column_width = column_width / ths.length;
			
			ths.each(function( i ){
				
				// dont add width to last th
				if( (i+1) == ths.length  )
				{
					return;
				}
				
				$(this).attr('width', column_width + '%');
				
			});
		}
				
	}
	
	
	/*
	*  repeater_update_classes
	*
	*  @description: 
	*  @since 3.5.2
	*  @created: 11/11/12
	*/
	
	function repeater_update_classes( repeater )
	{
		// vars
		var max_rows = parseFloat( repeater.attr('data-max_rows') ),
			row_count = repeater.find('> table > tbody > tr.row').length;	

		
		// empty?
		if( row_count == 0 )
		{
			repeater.addClass('empty');
		}
		else
		{
			repeater.removeClass('empty');
		}
		
		
		// row limit reached
		if( row_count >= max_rows )
		{
			repeater.addClass('disabled');
			repeater.find('> .repeater-footer .acf-button').addClass('disabled');
		}
		else
		{
			repeater.removeClass('disabled');
			repeater.find('> .repeater-footer .acf-button').removeClass('disabled');
		}
		
	}
	
	
	/*
	*  repeater_add_sortable
	*
	*  @description: 
	*  @since 3.5.2
	*  @created: 11/11/12
	*/
	
	function repeater_add_sortable( repeater ){
		
		// vars
		var max_rows = parseFloat( repeater.attr('data-max_rows') );
		
		
		// validate
		if( max_rows <= 1 )
		{
			return;
		}
			
		repeater.find('> table > tbody').unbind('sortable').sortable({
			items : '> tr.row',
			handle : '> td.order',
			helper : acf.sortable_helper,
			forceHelperSize : true,
			forcePlaceholderSize : true,
			scroll : true,
			start : function (event, ui) {
			
				$(document).trigger('acf/sortable_start', ui.item);
				$(document).trigger('acf/sortable_start_repeater', ui.item);

				// add markup to the placeholder
				var td_count = ui.item.children('td').length;
        		ui.placeholder.html('<td colspan="' + td_count + '"></td>');
        		
   			},
   			stop : function (event, ui) {
			
				$(document).trigger('acf/sortable_stop', ui.item);
				$(document).trigger('acf/sortable_stop_repeater', ui.item);
				
				// update order numbers	
				repeater_update_order( repeater );		
				
   			}
		});
	};
	
	
	// add field
	function repeater_add_field( repeater, before )
	{
		// vars
		var max_rows = parseInt( repeater.attr('data-max_rows') ),
			row_count = repeater.find('> table > tbody > tr.row').length;	
			
			
		// validate
		if( row_count >= max_rows )
		{
			alert( acf.text.repeater_max_alert.replace('{max}', max_rows) );
			return false;
		}
		
	
		// create and add the new field
		var new_id = uniqid(),
			new_field_html = repeater.find('> table > tbody > tr.row-clone').html().replace(/(=["]*[\w-\[\]]*?)(acfcloneindex)/g, '$1' + new_id),
			new_field = $('<tr class="row"></tr>').append( new_field_html );
		
		
		// add row
		if( !before )
		{
			before = repeater.find('> table > tbody > .row-clone');
		}
		
		before.before( new_field );
		
		
		// trigger mouseenter on parent repeater to work out css margin on add-row button
		repeater.closest('tr').trigger('mouseenter');
		
		
		// update order
		repeater_update_order( repeater );
		
		
		// update classes based on row count
		repeater_update_classes( repeater );
		
		
		// setup fields
		$(document).trigger('acf/setup_fields', new_field);

		
		// validation
		repeater.closest('.field').removeClass('error');
	}
	
	
	// add row - end
	$('.repeater .repeater-footer .add-row-end').live('click', function(){
		
		var repeater = $(this).closest('.repeater');
		
		
		repeater_add_field( repeater, false );
		
		
		return false;
	});
	
	
	// add row - before
	$('.repeater td.remove .add-row-before').live('click', function(){
		
		var repeater = $(this).closest('.repeater'),
			before = $(this).closest('tr');
			
			
		repeater_add_field( repeater, before );
		
		
		return false;
	});
	
	
	function repeater_remove_row( tr )
	{	
		// vars
		var repeater =  tr.closest('.repeater'),
			min_rows = parseInt( repeater.attr('data-min_rows') ),
			row_count = repeater.find('> table > tbody > tr.row').length,
			column_count = tr.children('tr.row').length,
			row_height = tr.height();
			
			
		// validate
		if( row_count <= min_rows )
		{
			alert( acf.text.repeater_min_alert.replace('{min}', row_count) );
			return false;
		}
		
		
		// animate out tr
		tr.addClass('acf-remove-item');
		setTimeout(function(){
			
			tr.remove();
			
			
			// trigger mouseenter on parent repeater to work out css margin on add-row button
			repeater.closest('tr').trigger('mouseenter');
		
		
			// update order
			repeater_update_order( repeater );
			
			
			// update classes based on row count
			repeater_update_classes( repeater );
			
		}, 400);
		
	}
	
	
	// remove field
	$('.repeater td.remove .acf-button-remove').live('click', function(){
		var tr = $(this).closest('tr');
		repeater_remove_row( tr );
		return false;
	});
	
	
	// hover over tr, align add-row button to top
	$('.repeater tr').live('mouseenter', function(){
		
		var button = $(this).find('> td.remove > a.acf-button-add');
		var margin = ( button.parent().height() / 2 ) + 9; // 9 = padding + border
		
		button.css('margin-top', '-' + margin + 'px' );
		
	});
	
	
	
	/*-----------------------------------------------------------------------------
	*
	*	Flexible Content
	*
	*----------------------------------------------------------------------------*/
	
	
	/*
	*  flexible_content_add_sortable
	*
	*  @description: 
	*  @created: 25/05/12
	*/
	
	function flexible_content_add_sortable( div )
	{
		
		// remove (if clone) and add sortable
		div.children('.values').unbind('sortable').sortable({
			items : '> .layout',
			handle : '> .menu-item-handle',
			forceHelperSize : true,
			forcePlaceholderSize : true,
			scroll : true,
			start : function (event, ui) {
			
				$(document).trigger('acf/sortable_start', ui.item);
				$(document).trigger('acf/sortable_start_flexible_content', ui.item);
        		
   			},
   			stop : function (event, ui) {
			
				$(document).trigger('acf/sortable_stop', ui.item);
				$(document).trigger('acf/sortable_stop_flexible_content', ui.item);
				
				// update order numbers				
				flexible_content_update_order( div );
   			}
		});
		
	};
	
	
	/*
	*  Show Popup
	*
	*  @description: 
	*  @created: 25/05/12
	*/
	
	$('.acf_flexible_content .flexible-footer .add-row-end').live('click', function()
	{
		$(this).trigger('focus');
		
	}).live('focus', function()
	{
		$(this).siblings('.acf-popup').addClass('active');
		
	}).live('blur', function()
	{
		var button = $(this);
		setTimeout(function(){
			button.siblings('.acf-popup').removeClass('active');
		}, 250);
		
	});
	
	
	/*
	*  flexible_content_remove_row
	*
	*  @description: 
	*  @created: 25/05/12
	*/
	
	function flexible_content_remove_layout( layout )
	{
		// vars
		var div = layout.closest('.acf_flexible_content');
		var temp = $('<div style="height:' + layout.height() + 'px"></div>');
		
		
		// animate out tr
		layout.addClass('acf-remove-item');
		setTimeout(function(){
			
			layout.before(temp).remove();
			
			temp.animate({'height' : 0 }, 250, function(){
				temp.remove();
			});
		
			if(!div.children('.values').children('.layout').exists())
			{
				div.children('.no_value_message').show();
			}
			
		}, 400);
		
	}
	
	
	$('.acf_flexible_content .fc-delete-layout').live('click', function(){
		var layout = $(this).closest('.layout');
		flexible_content_remove_layout( layout );
		return false;
	});
		
	
	
	// update order
	function flexible_content_update_order( div )
	{
		div.find('> .values .layout').each(function(i){
			$(this).find('> .menu-item-handle .fc-layout-order').html(i+1);
		});
	
	};
	
	
	// add layout
	$('.acf_flexible_content .acf-popup ul li a').live('click', function(){

		// vars
		var layout = $(this).attr('data-layout');
		var div = $(this).closest('.acf_flexible_content');
		
		
		// create new field
		var new_id = uniqid(),
		
			new_field_html = div.find('> .clones > .layout[data-layout="' + layout + '"]').html().replace(/(=["]*[\w-\[\]]*?)(acfcloneindex)/g, '$1' + new_id),
			new_field = $('<div class="layout" data-layout="' + layout + '"></div>').append( new_field_html );
			
			
		// hide no values message
		div.children('.no_value_message').hide();
		
		
		// add row
		div.children('.values').append(new_field); 
		
		
		// acf/setup_fields
		$(document).trigger('acf/setup_fields',new_field);
		
		
		// update order numbers
		flexible_content_update_order( div );
		
		
		// validation
		div.closest('.field').removeClass('error');
		
		return false;
		
	});
	
	
	$(document).live('acf/setup_fields', function(e, postbox){
		
		$(postbox).find('.acf_flexible_content').each(function(){
			
			var div =  $(this);

			// sortable
			flexible_content_add_sortable( div );
			
			
			// set column widths
			$(div).find('.layout').each(function(){
				repeater_set_column_widths( $(this) );
			});
			
			
		});
		
	});

	
	/*
	*  Hide Show Flexible Content
	*
	*  @description: 
	*  @since 3.5.2
	*  @created: 11/11/12
	*/
	
	$('.acf_flexible_content .layout .menu-item-handle').live('click', function(){
		
		// vars
		var layout = $(this).closest('.layout');
		
		
		if( layout.attr('data-toggle') == 'closed' )
		{
			layout.attr('data-toggle', 'open');
			layout.children('.acf-input-table').show();
		}
		else
		{
			layout.attr('data-toggle', 'closed');
			layout.children('.acf-input-table').hide();
		}
			
	});
	
	
	/*
	*  is_clone_field
	*
	*  @description: returns true|false for an input element
	*  @created: 19/08/12
	*/
	
	acf.is_clone_field = function( input )
	{
		if( input.attr('name') && input.attr('name').indexOf('[acfcloneindex]') != -1 )
		{
			return true;
		}
		
		return false;
	}
	
	
	/*
	*  Field: Datepicker
	*
	*  @description: 
	*  @created: 4/03/2011
	*/
	
	$(document).live('acf/setup_fields', function(e, postbox){
		
		$(postbox).find('input.acf_datepicker').each(function(){
			
			// vars
			var input = $(this),
				alt_field = input.siblings('.acf-hidden-datepicker'),
				save_format = input.attr('data-save_format'),
				display_format = input.attr('data-display_format');
			
			
			// is clone field?
			if( acf.is_clone_field(alt_field) )
			{
				return;
			}
			
			
			// get and set value from alt field
			input.val( alt_field.val() );
			
			
			// add date picker and refocus
			input.addClass('active').datepicker({ 
				dateFormat : save_format,
				altField : alt_field,
				altFormat :  save_format,
				changeYear: true,
				yearRange: "-100:+100",
				changeMonth: true,
				showButtonPanel : true,
				firstDay: 1
			});
			
			
			// now change the format back to how it should be.
			input.datepicker( "option", "dateFormat", display_format );
			
			
			// wrap the datepicker (only if it hasn't already been wrapped)
			if($('body > #ui-datepicker-div').length > 0)
			{
				$('#ui-datepicker-div').wrap('<div class="ui-acf" />');
			}
			
			
			// allow null
			input.blur(function(){
				
				if( !input.val() )
				{
					alt_field.val('');
				}
				
			});
			
		});
		
	});
	
	
	/*
	*  acf.add_message
	*
	*  @description: 
	*  @since: 3.2.7
	*  @created: 10/07/2012
	*/
	
	acf.add_message = function( message, div ){
		
		var message = $('<div class="acf-message-wrapper"><div class="message updated"><p>' + message + '</p></div></div>');
		
		div.prepend( message );
		
		setTimeout(function(){
			
			message.animate({
				opacity : 0
			}, 250, function(){
				message.remove();
			});
			
		}, 1500);
			
	};
	
	
	/*
	*  Field: Gallery
	*
	*  @description: 
	*  @since: 3.2.7
	*  @created: 10/07/2012
	*/
	
	acf.fields.gallery.add = function( image ){
	
	
		// vars
		var gallery = acf.media.div,
			tpml = gallery.find('.tmpl-thumbnail').html();
		
		
		
		// update vars on tmpl
		$.each(image, function( k, v ){
			var regex = new RegExp('{' + k + '}', 'g');
			tpml = tpml.replace(regex, v);
		});
		
	    
	    // add div
	    gallery.find('.thumbnails > .inner').append( tpml );
		
		
		// update gallery count
		acf.fields.gallery.update_count( gallery );
		
			 	
	 	// validation
		gallery.closest('.field').removeClass('error');
		
	};
	
	
	acf.fields.gallery.update_count = function( div )
	{
		// vars
		var count = div.find('.thumbnails .thumbnail').length,
			max_count = ( count > 2 ) ? 2 : count,
			span = div.find('.toolbar .count');
		
		
		span.html( span.attr('data-' + max_count).replace('{count}', count) );
		
	}
	
	
	// view: Grid
	$('.acf-gallery .toolbar .view-grid').live('click', function(){
		
		// vars
		var gallery = $(this).closest('.acf-gallery');
		
		
		// active class
		$(this).parent().addClass('active').siblings('.view-list-li').removeClass('active');
		
		
		// gallery class
		gallery.removeClass('view-list');
		
		
		return false;
			
	});
	
	
	// view: Grid
	$('.acf-gallery .toolbar .view-list').live('click', function(){
		
		// vars
		var gallery = $(this).closest('.acf-gallery');
		
		
		// active class
		$(this).parent().addClass('active').siblings('.view-grid-li').removeClass('active');
		
		
		// gallery class
		gallery.addClass('view-list');
		
		
		return false;
			
	});
	
	
	// remove image
	$('.acf-gallery .thumbnail .acf-button-delete').live('click', function(){
		
		// vars
		var thumbnail = $(this).closest('.thumbnail'),
			gallery = thumbnail.closest('.acf-gallery');
		
		
		thumbnail.animate({
			opacity : 0
		}, 250, function(){
			
			thumbnail.remove();
			
			acf.fields.gallery.update_count( gallery );
			
		});
		
		return false;
			
	});
	
	
	// remove image
	$('.acf-gallery .thumbnail .acf-button-edit').live('click', function(){
		
		// vars
		acf.media.div = $(this).closest('.thumbnail');
		
		
		acf.fields.image.edit();
		
		
		return false;
			
	});
	
	
	// Media title / Button
	$(document).live('acf/media/open', function(e, selection){
		
		// validate
		if( !acf.media.div.hasClass('acf-gallery') )
		{
			return;
		}
		
		
		// vars
		var multiple = true;
		
		
		// set media atts
		acf.media.set_multiple( multiple );
		acf.media.set_title( acf.fields.gallery.text.title_add );
		
	});
	
	
	acf.fields.gallery.hide_selected_items = function(){
		
		// vars
		var gallery = acf.media.div,
			div = acf.media.frame.content.get().$el;
			collection = acf.media.frame.content.get().collection;
			
		
		collection.each(function( item ){
			
			var src = item.attributes.url.substr(0, item.attributes.url.lastIndexOf('.')) || item.attributes.url;
			
			// if image is already inside the gallery, disable it!
			if( gallery.find('.thumbnails .thumbnail[data-id="' + item.attributes.id + '"]').exists() )
			{
				var attachment = div.find('.attachments .attachment img[src^="' + src + '"]').closest('.attachment');
				
				item.off('selection:single');
				attachment.css({
					opacity : 0.5
				});
			}
			
		});
	
	}
	
	
	$(document).live('acf/media/open acf/media/add acf/media/reset acf/media/browse', function(e ){
		
		// validate
		if( !acf.media.div.hasClass('acf-gallery') )
		{
			return;
		}
		
		
		acf.fields.gallery.hide_selected_items();
	    
	});
	
	
	$(document).live('acf/media/select', function(e, selection){
		
		// validate
		if( !acf.media.div.hasClass('acf-gallery') )
		{
			return;
		}
		
		
		// vars
		var div = acf.media.div,
			preview_size = div.attr('data-preview_size');
		
		    		
	    selection.each(function(attachment){

	    	var image = {
			    id : attachment.id,
			    src : attachment.attributes.url,
			    title : attachment.attributes.title,
			    caption : attachment.attributes.caption,
			    alt : attachment.attributes.alt,
			    description : attachment.attributes.description
		    };
	    	

	    	// is preview size available?
	    	if( attachment.attributes.sizes[ preview_size ] )
	    	{
		    	image.src = attachment.attributes.sizes[ preview_size ].url;
	    	}
	    	
	    	
	    	// add image to field
	        acf.fields.gallery.add( image );
	        
	    });
	    
	});
	
	
	
	// add image
	$('.acf-gallery .toolbar .add-image').live('click', function(){
		
		// vars
		var div = acf.media.div = $(this).closest('.acf-gallery'),
			preview_size = div.attr('data-preview_size');
		

		// show the thickbox
		if( acf.media.frame )
		{
		    acf.media.frame.open();
		}
		else
		{
			tb_show( acf.fields.gallery.text.title_add , acf.admin_url + 'media-upload.php?post_id=' + acf.post_id + '&post_ID=' + acf.post_id + '&type=image&acf_type=image&acf_preview_size=' + preview_size + 'TB_iframe=1');
		}
		
	
		return false;
					
	});
	
	
	$(document).live('acf/setup_fields', function(e, postbox){
		
		$(postbox).find('.acf-gallery').each(function(i){
			
			// is clone field?
			if( acf.is_clone_field($(this).children('input[type="hidden"]')) )
			{
				return;
			}
			
			
			// vars
			var div = $(this),
				thumbnails = div.find('.thumbnails');
				
			
			// update count
			acf.fields.gallery.update_count( div );

			
			// sortable
			thumbnails.find('> .inner').sortable({
				items : '> .thumbnail',
				/* handle: '> td.order', */
				forceHelperSize: true,
				forcePlaceholderSize: true,
				scroll: true,
				start: function (event, ui) {
				
					// alter width / height to allow for 2px border
					ui.placeholder.width( ui.placeholder.width() - 4 );
					ui.placeholder.height( ui.placeholder.height() - 4 );
	   			}
			});

			
		});
	
	});
	
	
	/*
	*  Conditional Logic Calculate
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 15/10/12
	*/
	
	acf.conditional_logic.calculate = function( options )
	{
		// vars
		var field = $('.field-' + options.field),
			toggle = $('.field-' + options.toggle),
			r = false;
		
		
		// compare values
		if( toggle.hasClass('field-true_false') || toggle.hasClass('field-checkbox') || toggle.hasClass('field-radio') )
		{
			if( options.operator == "==" )
			{
				if( toggle.find('input[value="' + options.value + '"]:checked').exists() )
				{
					r = true;
				}
			}
			else
			{
				if( !toggle.find('input[value="' + options.value + '"]:checked').exists() )
				{
					r = true;
				}
			}
			
		}
		else
		{
			if( options.operator == "==" )
			{
				if( toggle.find('*[name]').val() == options.value )
				{
					r = true;
				}
			}
			else
			{
				if( toggle.find('*[name]').val() != options.value )
				{
					r = true;
				}
			}
			
		}
		
		return r;
	}
	
	
	/*
	*  Field: Tab
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 14/12/12
	*/
	
	$(document).live('acf/setup_fields', function(e, postbox){
		
		$(postbox).find('.acf-tab').each(function(){
			
			// vars
			var tab = $(this),
				id = tab.attr('data-id'),
				label = tab.html(),
				postbox = tab.closest('.acf_postbox'),
				inside = postbox.children('.inside');
			

			
			// only run once for each tab
			if( tab.hasClass('acf-tab-added') )
			{
				return;
			}
			tab.addClass('acf-tab-added');
			
			
			// create tab group if it doesnt exist
			if( ! inside.children('.acf-tab-group').exists() )
			{
				inside.children('.field-tab:first').before('<ul class="hl clearfix acf-tab-group"></ul>');
			}
			
			
			// add tab
			inside.children('.acf-tab-group').append('<li><a class="acf-tab-button" href="#" data-id="' + id + '">' + label + '</a></li>');
			
			
		});
		
		
		// trigger
		$(postbox).find('.acf-tab-group').each(function(){
			
			$(this).find('li:first a').trigger('click');
			
		});

	
	});
	
	
	/*
	*  Tab group click
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 14/12/12
	*/
	
	$('.acf-tab-button').live('click', function(){
		
		// vars
		var a = $(this),
			id = a.attr('data-id'),
			ul = a.closest('ul'),
			inside = ul.closest('.acf_postbox').children('.inside');
		
		
		// classes
		ul.find('li').removeClass('active');
		a.parent('li').addClass('active');
		
		
		// hide / show
		inside.children('.field-tab').each(function(){
			
			var tab = $(this);
			
			if( tab.hasClass('field-' + id) )
			{
				tab.nextUntil('.field-tab').removeClass('acf-tab_group-hide').addClass('acf-tab_group-show');
			}
			else
			{
				tab.nextUntil('.field-tab').removeClass('acf-tab_group-show').addClass('acf-tab_group-hide');
			}
			
		});

		$(this).trigger('blur');
		
		return false;
		
	});
	
	
})(jQuery);