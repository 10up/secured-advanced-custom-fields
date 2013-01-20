/*
*  File
*
*  @description: 
*  @since: 3.5.8
*  @created: 17/01/13
*/

(function($){
	
	var _file = acf.fields.file,
		_media = acf.media;
	
	
	/*
	*  Add
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	_file.add = function( file )
	{

		// vars
		var div = _media.div;
		
		// set atts
		div.find('.acf-file-value').val( file.id ).trigger('change');
	 	div.find('.acf-file-icon').attr( 'src', file.icon );
	 	div.find('.acf-file-name').text( file.name );
	 	
	 	
	 	// set div class
	 	div.addClass('active');
	 	
	 	
	 	// validation
		div.closest('.field').removeClass('error');

	};
		
	
	/*
	*  Edit
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	_file.edit = function(){
		
		// vars
		var div = _media.div,
			id = div.find('.acf-file-value').val();
		
		
		// show edit attachment
		tb_show( _file.text.title_edit , acf.admin_url + 'media.php?attachment_id=' + id + '&action=edit&acf_action=edit_attachment&acf_field=file&TB_iframe=1');
		
	};
	
	
	/*
	*  Remove
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	_file.remove = function()
	{
		// vars
		var div = _media.div;
		
		
		// remove atts
		div.find('.acf-file-value').val( '' ).trigger('change');
	 	div.find('.acf-file-icon').attr( 'src', '' );
	 	div.find('.acf-file-name').text( '' );
		
		
		// remove class
		div.removeClass('active');
		
	};
	
	
	/*
	*  Add Button
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	$('.acf-file-uploader .add-file').live('click', function(){
				
		// vars
		var div = _media.div = $(this).closest('.acf-file-uploader'),
			multiple = div.closest('.repeater').exists() ? true : false;
			
			
		// show the thickbox
		if( _media.type() == 'backbone' )
		{
			// clear the frame
			_media.clear_frame();
			
			
		    // Create the media frame. Leave options blank for defaults
			_media.frame = wp.media({
				title : _file.text.title_add,
				multiple : multiple
			});
			
			
			/*
			_media.frame.on('all', function( e ){
				console.log( e );
			});
			*/
			
			
			// add filter by overriding the option when the title is being created. This is an evet fired before the rendering / creating of the library content so it works but is a bit of a hack. In the future, this should be changed to an init / options event
			_media.frame.on('title:create', function(){
				var state = _media.frame.state();
				state.set('filterable', 'uploaded');
			});

			
			// When an image is selected, run a callback.
			_media.frame.on( 'select', function() {
				
				// get selected images
				selection = _media.frame.state().get('selection');
				
				if( selection )
				{
					var i = 0;
					
					selection.each(function(attachment){
	
				    	// counter
				    	i++;
				    	
				    	
				    	// select / add another file field?
				    	if( i > 1 )
						{
							var tr = _media.div.closest('tr'),
								repeater = tr.closest('.repeater');
							
							
							if( tr.next('.row').exists() )
							{
								_media.div = tr.next('.row').find('.acf-file-uploader');
							}
							else
							{
								// add row 
				 				repeater.find('.add-row-end').trigger('click'); 
				 			 
				 				// set acf_div to new row file 
				 				_media.div = repeater.find('> table > tbody > tr.row:last .acf-file-uploader');
							}
						}
						
						
				    	// vars
				    	var file = {
					    	id : attachment.id,
					    	name : attachment.attributes.filename,
					    	icon : attachment.attributes.icon
				    	};
				    	
				    	
				    	// add file to field
				        _file.add( file );
				        
						
				    });
				    // selection.each(function(attachment){
				}
				// if( selection )
			});
			// _media.frame.on( 'select', function() {
					 
				
			// Finally, open the modal
			_media.frame.open();
			
			var state = _media.frame.state();
			
		}
		else
		{	
			tb_show( _file.text.title_add , acf.admin_url + 'media-upload.php?post_id=' + acf.post_id + '&post_ID=' + acf.post_id + '&type=file&acf_type=file&TB_iframe=1');
		}
		
		return false;
		
	});
		
	
	/*
	*  Edit Button
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	$('.acf-file-uploader .edit-file').live('click', function(){
		
		// vars
		_media.div = $(this).closest('.acf-file-uploader');
		
		_file.edit();
		
		return false;
			
	});
	
	
	/*
	*  Remove Button
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	$('.acf-file-uploader .remove-file').live('click', function(){
		
		// vars
		_media.div = $(this).closest('.acf-file-uploader');
				
		_file.remove();
		
		return false;
			
	});
		

})(jQuery);