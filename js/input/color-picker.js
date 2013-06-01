(function($){
	
	/*
	*  Color Picker
	*
	*  static model and events for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	_cp = acf.fields.color_picker = {
		
		$el : null,
		$input : null,
		farbtastic : null,
		
		set : function( o ){
			
			// merge in new option
			$.extend( this, o );
			
			
			// find input
			this.$input = this.$el.find('input[type="text"]');
			
			
			// return this for chaining
			return this;
			
		},
		init : function(){
			
			// instantiate farbtastic
			if( ! this.farbtastic )
			{
				$('body').append('<div id="acf-farbtastic" />');
		
				this.farbtastic = $.farbtastic('#acf-farbtastic');
			}
			
			
			// is clone field?
			if( acf.helpers.is_clone_field(this.$input) )
			{
				return;
			}
			

			if( this.$input.val() )
			{
				$.farbtastic( this.$input ).setColor( this.$input.val() ).hsl[2] > 0.5 ? color = '#000' : color = '#fff';
				
				this.$input.css({ 
					backgroundColor : this.$input.val(),
					color : color
				});
			}
			
		},
		focus : function(){

			if( ! this.$input.val() )
			{
				this.$input.val( '#FFFFFF' );
			}
			
			$('#acf-farbtastic').css({
				left : this.$input.offset().left,
				top : this.$input.offset().top - $('#acf-farbtastic').height(),
				display : 'block'
			});
			
			this.farbtastic.linkTo( this.$input );
			
		},
		blur : function(){
			
			// reset the css
			if( ! this.$input.val() )
			{
				this.$input.css({ 
					backgroundColor : '#fff',
					color : '#000'
				});
				
			}
			
			
			$('#acf-farbtastic').css({
				display: 'none'
			});
			
		}
		
	};
	
	/*
	*  acf/setup_fields
	*
	*  run init function on all elements for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
		
	$(document).live('acf/setup_fields', function(e, postbox){
		
		// validate
		if( ! $.farbtastic )
		{
			return;
		}
		

		$(postbox).find('.acf-color_picker').each(function(){
			
			_cp.set({ $el : $(this) }).init();
			
		});
		
	});
	
	
	/*
	*  Events
	*
	*  live events for the color picker field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	$('.acf-color_picker input[type="text"]').live('focus', function(){
		
		_cp.set({ $el : $(this).parent() }).focus();
		
	}).live('blur', function(){
		
		_cp.set({ $el : $(this).parent() }).blur();
					
	});
	

})(jQuery);