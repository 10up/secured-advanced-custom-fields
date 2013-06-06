(function($){
	
	/*
	*  File
	*
	*  static model for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	
	// reference
	var _media = acf.media;
	
	
	acf.fields.file = {
		
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
			this.o.query = {};
			
			
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
		add : function( file ){
			
			// this function must reference a global div variable due to the pre WP 3.5 uploader
			// vars
			var div = _media.div;
			
			
			// set atts
			div.find('.acf-file-icon').attr( 'src', file.icon );
		 	div.find('.acf-file-title').text( file.title );
		 	div.find('.acf-file-name').text( file.name ).attr( 'href', file.url );
		 	div.find('.acf-file-size').text( file.size );
			div.find('.acf-file-value').val( file.id ).trigger('change');
		 	
		 	
		 	// set div class
		 	div.addClass('active');
		 	
		 	
		 	// validation
			div.closest('.field').removeClass('error');
	
		},
		edit : function(){
			
			// set global var
			_media.div = this.$el;
			
			
			// show tb - to be removed in 4.2.0
			tb_show( acf.l10n.file.edit, acf.o.admin_url + 'media.php?attachment_id=' + this.$input.val() + '&action=edit&acf_action=edit_attachment&acf_field=file&TB_iframe=1');
			
		},
		remove : function()
		{
			
			// set atts
			this.$el.find('.acf-file-icon').attr( 'src', '' );
			this.$el.find('.acf-file-title').text( '' );
		 	this.$el.find('.acf-file-name').text( '' ).attr( 'href', '' );
		 	this.$el.find('.acf-file-size').text( '' );
			this.$el.find('.acf-file-value').val( '' ).trigger('change');
			
			
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
							title		:	acf.l10n.file.select,
							priority	:	20,
							filterable	:	'all'
						})
					]
				});
				
				
				// customize model / view
				acf.media.frame.on('open', function(){
					
					var content = acf.media.frame.content.get(),
						filters = content.toolbar._views.filters;
					
					
					// no need for 'uploaded' filter
					if( t.o.library == 'uploadedTo' )
					{
						filters.$el.find('option[value="uploaded"]').remove();
						filters.$el.after('<span>' + acf.l10n.file.uploadedTo + '</span>')
						
						$.each( filters.filters, function( k, v ){
							
							v.props.uploadedTo = acf.o.post_id;
							
						});
					}
									
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
						    	id		:	attachment.id,
						    	title	:	attachment.attributes.title,
						    	name	:	attachment.attributes.filename,
						    	url		:	attachment.attributes.url,
						    	icon	:	attachment.attributes.icon,
						    	size	:	attachment.attributes.filesize
					    	};
					    	
					    	
					    	// add file to field
					        acf.fields.file.add( file );
					        
							
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
				tb_show( acf.l10n.file.select , acf.admin_url + 'media-upload.php?post_id=' + acf.o.post_id + '&post_ID=' + acf.post_id + '&type=file&acf_type=file&TB_iframe=1');
			}
			
			return false;
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
		
		$(postbox).find('.acf-file-uploader').each(function(){
			
			acf.fields.file.set({ $el : $(this) }).init();
			
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
	
	
	$('.acf-file-uploader .acf-button-edit').live('click', function(){
		
		acf.fields.file.set({ $el : $(this).closest('.acf-file-uploader') }).edit();
		
		return false;
			
	});
	
	$('.acf-file-uploader .acf-button-delete').live('click', function(){
		
		acf.fields.file.set({ $el : $(this).closest('.acf-file-uploader') }).remove();
		
		return false;
			
	});
	
	
	$('.acf-file-uploader .add-file').live('click', function(){
				
		acf.fields.file.set({ $el : $(this).closest('.acf-file-uploader') }).popup();
		
		return false;
		
	});
	

})(jQuery);