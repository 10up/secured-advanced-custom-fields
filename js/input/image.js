(function($){
	
	/*
	*  Image
	*
	*  static model for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	
	// reference
	var _media = acf.media;
	
	
	acf.fields.image = {
		
		$el : null,
		$input : null,
		
		o : {},
		
		set : function( o ){
			
			// merge in new option
			$.extend( this, o );
			
			
			// find input
			this.$input = this.$el.find('input[type="hidden"]');
			
			
			// get options
			this.o = acf.helpers.get_atts( this.$el );
			
			
			// multiple?
			this.o.multiple = this.$el.closest('.repeater').exists() ? true : false;
			
			
			// wp library query
			this.o.query = {
				type : 'image'
			};
			
			
			// library
			if( this.o.library == 'uploadedTo' )
			{
				this.o.query.uploadedTo = acf.o.post_id;
			}
			
			
			// return this for chaining
			return this;
			
		},
		init : function(){

			// is clone field?
			if( acf.helpers.is_clone_field(this.$input) )
			{
				return;
			}
					
		},
		add : function( image ){
			
			// this function must reference a global div variable due to the pre WP 3.5 uploader
			// vars
			var div = _media.div;
			
			
			// set atts
			div.find('.acf-image-image').attr( 'src', image.url );
			div.find('.acf-image-value').val( image.id ).trigger('change');
		 	
			
		 	// set div class
		 	div.addClass('active');
		 	
		 	
		 	// validation
			div.closest('.field').removeClass('error');
	
		},
		edit : function(){
			
			// set global var
			_media.div = this.$el;
			
			
			// show tb - to be removed in 4.2.0
			tb_show( acf.l10n.image.edit, acf.o.admin_url + 'media.php?attachment_id=' + this.$input.val() + '&action=edit&acf_action=edit_attachment&acf_field=image&TB_iframe=1');
			
		},
		remove : function()
		{
			
			// set atts
		 	this.$el.find('.acf-image-image').attr( 'src', '' );
			this.$el.find('.acf-image-value').val( '' ).trigger('change');
			
			
			// remove class
			this.$el.removeClass('active');
			
		},
		popup : function()
		{
			// reference
			var t = this;
			
			
			// set global var
			_media.div = this.$el;
			

			// show the uploader
			if( _media.type() == 'backbone' )
			{
				// clear the frame
				_media.clear_frame();
				
				
				 // Create the media frame
				 _media.frame = wp.media({
					states : [
						new wp.media.controller.Library({
							library		:	wp.media.query( t.o.query ),
							multiple	:	t.o.multiple,
							title		:	acf.l10n.image.select,
							priority	:	20,
							filterable	:	'all'
						})
					]
				});
				
				
				/*acf.media.frame.on('all', function(e){
					
					console.log( e );
					
				});*/
				
				
				// customize model / view
				acf.media.frame.on('content:activate', function(){

					// vars
					var toolbar = null,
						filters = null;
						
					
					// populate above vars making sure to allow for failure
					try
					{
						toolbar = acf.media.frame.content.get().toolbar;
						filters = toolbar.get('filters');
					} 
					catch(e)
					{
						// one of the objects was 'undefined'... perhaps the frame open is Upload Files
						//console.log( e );
					}
					
					
					// validate
					if( !filters )
					{
						return false;
					}
					
					
					// filter only images
					$.each( filters.filters, function( k, v ){
					
						v.props.type = 'image';
						
					});
					
					
					// no need for 'uploaded' filter
					if( t.o.library == 'uploadedTo' )
					{
						filters.$el.find('option[value="uploaded"]').remove();
						filters.$el.after('<span>' + acf.l10n.image.uploadedTo + '</span>')
						
						$.each( filters.filters, function( k, v ){
							
							v.props.uploadedTo = acf.o.post_id;
							
						});
					}
					
					
					// remove non image options from filter list
					filters.$el.find('option').each(function(){
						
						// vars
						var v = $(this).attr('value');
						
						
						// don't remove the 'uploadedTo' if the library option is 'all'
						if( v == 'uploaded' && t.o.library == 'all' )
						{
							return;
						}
						
						if( v.indexOf('image') === -1 )
						{
							$(this).remove();
						}
						
					});
					
					
					// set default filter
					filters.$el.val('image').trigger('change');
					
				});
				
				
				// When an image is selected, run a callback.
				acf.media.frame.on( 'select', function() {
					
					// get selected images
					selection = _media.frame.state().get('selection');
					
					if( selection )
					{
						var i = 0;
						
						selection.each(function(attachment){
		
					    	// counter
					    	i++;
					    	
					    	
					    	// select / add another image field?
					    	if( i > 1 )
							{
								var key = _media.div.closest('td').attr('data-field_key'),
									tr = _media.div.closest('tr'),
									repeater = tr.closest('.repeater');
								
								
								if( tr.next('.row').exists() )
								{
									_media.div = tr.next('.row').find('td[data-field_key="' + key + '"] .acf-image-uploader');
								}
								else
								{
									// add row 
					 				repeater.find('.add-row-end').trigger('click'); 
					 			 
					 				// set acf_div to new row image 
					 				_media.div = repeater.find('> table > tbody > tr.row:last td[data-field_key="' + key + '"] .acf-image-uploader');
								}
							}
							
							
					    	// vars
					    	var image = {
						    	id		:	attachment.id,
						    	url		:	attachment.attributes.url
					    	};
					    	
					    	// is preview size available?
					    	if( attachment.attributes.sizes && attachment.attributes.sizes[ t.o.preview_size ] )
					    	{
						    	image.url = attachment.attributes.sizes[ t.o.preview_size ].url;
					    	}
					    	
					    	// add image to field
					        acf.fields.image.add( image );
					        
							
					    });
					    // selection.each(function(attachment){
					}
					// if( selection )
					
				});
				// acf.media.frame.on( 'select', function() {
						 
					
				// Finally, open the modal
				acf.media.frame.open();
				
			}
			else
			{	
				tb_show( acf.l10n.image.select , acf.admin_url + 'media-upload.php?post_id=' + acf.o.post_id + '&post_ID=' + acf.o.post_id + '&type=image&acf_type=image&acf_preview_size=' + t.o.preview_size + '&TB_iframe=1');
			}
			
			return false;
		},
		
		// temporary gallery fix		
		text : {
			title_add : "Select Image",
			title_edit : "Edit Image"
		}
		
	};
	
	
	/*
	*  acf/setup_fields
	*
	*  run init function for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*  @note	Currenlty no need for init
	
	$(document).live('acf/setup_fields', function(e, postbox){
		
		$(postbox).find('.acf-image-uploader').each(function(){
			
			acf.fields.image.set({ $el : $(this) }).init();
			
		});
		
	});
	
	*/
	
	
	/*
	*  Events
	*
	*  live events for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	
	$('.acf-image-uploader .acf-button-edit').live('click', function(){
		
		acf.fields.image.set({ $el : $(this).closest('.acf-image-uploader') }).edit();
		
		return false;
			
	});
	
	$('.acf-image-uploader .acf-button-delete').live('click', function(){
		
		acf.fields.image.set({ $el : $(this).closest('.acf-image-uploader') }).remove();
		
		return false;
			
	});
	
	
	$('.acf-image-uploader .add-image').live('click', function(){
				
		acf.fields.image.set({ $el : $(this).closest('.acf-image-uploader') }).popup();
		
		return false;
		
	});
	

})(jQuery);