(function($){
	
	/*
	*  Date Picker
	*
	*  static model for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	acf.fields.date_picker = {
		
		$el : null,
		$input : null,
		$hidden : null,
		
		o : {},
		
		set : function( o ){
			
			// merge in new option
			$.extend( this, o );
			
			
			// find input
			this.$input = this.$el.find('input[type="text"]');
			this.$hidden = this.$el.find('input[type="hidden"]');
			
			
			// get options
			this.o = acf.helpers.get_atts( this.$el );
			
			
			// return this for chaining
			return this;
			
		},
		init : function(){

			// is clone field?
			if( acf.helpers.is_clone_field(this.$hidden) )
			{
				return;
			}
			
			
			// get and set value from alt field
			this.$input.val( this.$hidden.val() );
			
			
			// create options
			var options = $.extend( {}, acf.l10n.date_picker, { 
				dateFormat		:	this.o.save_format,
				altField		:	this.$hidden,
				altFormat		:	this.o.save_format,
				changeYear		:	true,
				yearRange		:	"-100:+100",
				changeMonth		:	true,
				showButtonPanel	:	true,
				firstDay		:	this.o.first_day
			});
			
			
			// add date picker
			this.$input.addClass('active').datepicker( options );
			
			
			// now change the format back to how it should be.
			this.$input.datepicker( "option", "dateFormat", this.o.display_format );
			
			
			// wrap the datepicker (only if it hasn't already been wrapped)
			if( $('body > #ui-datepicker-div').length > 0 )
			{
				$('#ui-datepicker-div').wrap('<div class="ui-acf" />');
			}
			
		},
		focus : function(){

			
			
		},
		blur : function(){
			
			if( !this.$input.val() )
			{
				this.$hidden.val('');
			}
			
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
	*/
		
	$(document).live('acf/setup_fields', function(e, postbox){
		
		$(postbox).find('.acf-date_picker').each(function(){
			
			acf.fields.date_picker.set({ $el : $(this) }).init();
			
		});
		
	});
	
	
	/*
	*  Events
	*
	*  live events for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	$('.acf-date_picker input[type="text"]').live('blur', function(){
		
		acf.fields.date_picker.set({ $el : $(this).parent() }).blur();
					
	});
	

})(jQuery);