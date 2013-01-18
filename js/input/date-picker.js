/*
*  Date Picker
*
*  @description: 
*  @since: 3.5.8
*  @created: 17/01/13
*/

(function($){

	
	/*
	*  acf/setup_fields
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	$(document).live('acf/setup_fields', function(e, postbox){
		
		$(postbox).find('input.acf_datepicker').each(function(){
			
			// vars
			var input = $(this),
				alt_field = input.siblings('.acf-hidden-datepicker'),
				save_format = input.attr('data-save_format'),
				display_format = input.attr('data-display_format');
			
			
			// is clone field?
			if( acf.helpers.is_clone_field(alt_field) )
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
	

})(jQuery);