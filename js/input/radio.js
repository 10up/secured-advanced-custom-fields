(function($){
	
	/*
	*  Radio
	*
	*  static model and events for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	_radio = acf.fields.radio = {
		
		$el : null,
		$input : null,
		$other : null,
		farbtastic : null,
		
		set : function( o ){
			
			// merge in new option
			$.extend( this, o );
			
			
			// find input
			this.$input = this.$el.find('input[type="radio"]:checked');
			this.$other = this.$el.find('input[type="text"]');
			
			
			// return this for chaining
			return this;
			
		},
		change : function(){

			if( this.$input.val() == 'other' )
			{
				this.$other.attr('name', this.$input.attr('name'));
				this.$other.show();
			}
			else
			{
				this.$other.attr('name', '');
				this.$other.hide();
			}
		}
	};
	
	
	/*
	*  Events
	*
	*  live events for this field
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).on('change', '.acf-radio-list input[type="radio"]', function( e ){
		
		_radio.set({ $el : $(this).closest('.acf-radio-list') }).change();
		
	});
	

})(jQuery);