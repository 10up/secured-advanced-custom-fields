/*
*  Tab
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