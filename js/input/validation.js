(function($){
	
	
	/*
	*  Validation
	*
	*  JS model
	*
	*  @type	object 
	*  @date	1/06/13
	*
	*/
	
	acf.validation = {
	
		status : true,
		disabled : false,
		
		run : function(){
			
			// reference
			var _this = this;
			
			
			// reset
			_this.status = true;
			
			
			// loop through all fields
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
				
				
				// if is hidden by conditional logic on a parent tab, ignore
				if( div.hasClass('acf-tab_group-hide') )
				{
					if( div.prevAll('.field_type-tab:first').hasClass('acf-conditional_logic-hide') )
					{
						return;
					}
				}
				
				
				// text / textarea
				if( div.find('input[type="text"], input[type="email"], input[type="number"], input[type="hidden"], textarea').val() == "" )
				{
					div.data('validation', false);
				}
				
				
				// wysiwyg
				if( div.find('.acf_wysiwyg').exists() && typeof(tinyMCE) == "object")
				{
					div.data('validation', true);
					
					var id = div.find('.wp-editor-area').attr('id'),
						editor = tinyMCE.get( id );
	
	
					if( editor && !editor.getContent() )
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
	
				
				// radio
				if( div.find('input[type="radio"]').exists() )
				{
					div.data('validation', false);
	
					if( div.find('input[type="radio"]:checked').exists() )
					{
						div.data('validation', true);
					}
				}
				
				
				// checkbox
				if( div.find('input[type="checkbox"]').exists() )
				{
					div.data('validation', false);
	
					if( div.find('input[type="checkbox"]:checked').exists() )
					{
						div.data('validation', true);
					}
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
					_this.status = false;
					div.closest('.field').addClass('error');
				}
				
	
			});
			// end loop through all fields
		}
		
	};
	
	
	/*
	*  Events
	*
	*  Remove error class on focus
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).on('focus click', '.field.required input, .field.required textarea, .field.required select', function( e ){
	
		$(this).closest('.field').removeClass('error');
		
	});
	
	
	/*
	*  Save Post
	*
	*  If user is saving a draft, allow them to bypass the validation
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).on('click', '#save-post', function(){
		
		acf.validation.disabled = true;
		
	});
	
	
	/*
	*  Submit Post
	*
	*  Run validation and return true|false accordingly
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).on('submit', '#post', function(){
		
		// If disabled, bail early on the validation check
		if( acf.validation.disabled )
		{
			return true;
		}
		
		
		// do validation
		acf.validation.run();
			
			
		if( ! acf.validation.status )
		{
			// vars
			var $form = $(this);
			
			
			// show message
			$form.siblings('#message').remove();
			$form.before('<div id="message" class="error"><p>' + acf.l10n.validation.error + '</p></div>');
			
			
			// hide ajax stuff on submit button
			$('#publish').removeClass('button-primary-disabled');
			$('#ajax-loading').attr('style','');
			$('#publishing-action .spinner').hide();
			
			return false;
		}

		
		// remove hidden postboxes
		// + this will stop them from being posted to save
		$('.acf_postbox.acf-hidden').remove();
		

		// submit the form
		return true;
		
	});
	

})(jQuery);