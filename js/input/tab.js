(function($){

	
	/*
	*  acf/setup_fields
	*
	*  run init function on all elements for this field
	*
	*  @type	event
	*  @date	20/07/13
	*
	*  @param	{object}	e		event object
	*  @param	{object}	el		DOM object which may contain new ACF elements
	*  @return	N/A
	*/
	
	$(document).on('acf/setup_fields', function(e, el){
		
		// validate
		if( ! $(el).find('.acf-tab').exists() )
		{
			return;
		}
		
		
		// init
		$(el).find('.acf-tab').each(function(){
			
			// vars
			var $el		=	$(this),
				$field	=	$el.parent(),
				$wrap	=	$field.parent(),
				
				id		=	$el.attr('data-id'),
				label 	= 	$el.html();
				


			// only run once for each tab
			if( $el.hasClass('acf-tab-added') )
			{
				return;
			}
			
			$el.addClass('acf-tab-added');
			
			
			// create tab group if it doesnt exist
			if( ! $wrap.children('.acf-tab-group').exists() )
			{
				$wrap.children('.field_type-tab:first').before('<ul class="hl clearfix acf-tab-group"></ul>');
			}
			
			
			// add tab
			$wrap.children('.acf-tab-group').append('<li class="field_key-' + id + '" data-field_key="' + id + '"><a class="acf-tab-button" href="#" data-id="' + id + '">' + label + '</a></li>');
			
			
		});
		
		
		// trigger conditional logic
		// this code ( acf/setup_fields ) is run after the main acf.conditional_logic.init();
		acf.conditional_logic.change();
		
		
		// trigger
		$(el).find('.acf-tab-group').each(function(){
			
			$(this).find('li:first a').trigger('click');
			
		});

	
	});
	
	
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
	
	$(document).on('click', '.acf-tab-button', function( e ){
		
		
		e.preventDefault();
		
		
		// vars
		var $a		=	$(this),
			$ul		=	$a.closest('ul'),
			$wrap	=	$ul.parent(),
			id		=	$a.attr('data-id');
		
		
		// classes
		$ul.find('li').removeClass('active');
		$a.parent('li').addClass('active');
		
		
		// hide / show
		$wrap.children('.field_type-tab').each(function(){
			
			var $tab = $(this);
			
			if( $tab.hasClass('field_key-' + id) )
			{
				$tab.nextUntil('.field_type-tab').removeClass('acf-tab_group-hide').addClass('acf-tab_group-show');
			}
			else
			{
				$tab.nextUntil('.field_type-tab').removeClass('acf-tab_group-show').addClass('acf-tab_group-hide');
			}
			
		});

		
		// blur to remove dotted lines around button
		$a.trigger('blur');

		
	});
	
	
	$(document).on('acf/conditional_logic/hide', function( e, $target, item ){
		
		
		// if the $target to be hidden is a tab button, lets toggle a sibling tab button
		setTimeout(function(){
			
			if( $target.parent().hasClass('acf-tab-group') )
			{
				$target.siblings('.acf-conditional_logic-show').first().children('a').trigger('click');
			}
			
		}, 0);
		
		
	});
	
	

})(jQuery);