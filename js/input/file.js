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
			
			// vars
			var id = this.$input.val();
			
			
			// set global var
			_media.div = this.$el;
			

			// clear the frame
			_media.clear_frame();
			
			
			// create the media frame
			_media.frame = wp.media({
				title		:	acf.l10n.file.edit,
				multiple	:	false,
				button		:	{ text : acf.l10n.file.update }
			});
			
			
			// log events
			/*
			acf.media.frame.on('all', function(e){
				
				console.log( e );
				
			});
			*/
			
			
			// open
			_media.frame.on('open',function() {
				
				// set to browse
				if( _media.frame.content._mode != 'browse' )
				{
					_media.frame.content.mode('browse');
				}
				
				
				// add class
				_media.frame.$el.closest('.media-modal').addClass('acf-media-modal acf-expanded');
					
				
				// set selection
				var selection	=	_media.frame.state().get('selection'),
					attachment	=	wp.media.attachment( id );
				
				
				// to fetch or not to fetch
				if( $.isEmptyObject(attachment.changed) )
				{
					attachment.fetch();
				}
				

				selection.add( attachment );
						
			});
			
			
			// close
			_media.frame.on('close',function(){
			
				// remove class
				_media.frame.$el.closest('.media-modal').removeClass('acf-media-modal');
				
			});
			
							
			// Finally, open the modal
			acf.media.frame.open();
			
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
							// vars
							var $td			=	_media.div.closest('td'),
								$tr 		=	$td.closest('.row'),
								$repeater 	=	$tr.closest('.repeater'),
								key 		=	$td.attr('data-field_key'),
								selector	=	'td .acf-file-uploader:first';
								
							
							// key only exists for repeater v1.0.1 +
							if( key )
							{
								selector = 'td[data-field_key="' + key + '"] .acf-file-uploader';
							}
							
							
							// add row?
							if( ! $tr.next('.row').exists() )
							{
								$repeater.find('.add-row-end').trigger('click');
								
							}
							
							
							// update current div
							_media.div = $tr.next('.row').find( selector );
							
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
				
			
			return false;
		}
		
	};
	
	
	/*
	*  Events
	*
	*  jQuery events for this field
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).on('click', '.acf-file-uploader .acf-button-edit', function( e ){
		
		e.preventDefault();
		
		acf.fields.file.set({ $el : $(this).closest('.acf-file-uploader') }).edit();
			
	});
	
	$(document).on('click', '.acf-file-uploader .acf-button-delete', function( e ){
		
		e.preventDefault();
		
		acf.fields.file.set({ $el : $(this).closest('.acf-file-uploader') }).remove();
			
	});
	
	
	$(document).on('click', '.acf-file-uploader .add-file', function( e ){
		
		e.preventDefault();
		
		acf.fields.file.set({ $el : $(this).closest('.acf-file-uploader') }).popup();
		
	});
	

})(jQuery);