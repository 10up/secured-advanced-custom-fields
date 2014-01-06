(function($){

	acf.fields.tab = {
		
		add_group : function( $wrap ){
			
			// vars
			var html = '';
			
			
			// generate html
			if( $wrap.is('tbody') )
			{
				html = '<tr class="acf-tab-wrap"><td colspan="2"><ul class="hl clearfix acf-tab-group"></ul></td></tr>';
			}
			else
			{
				html = '<div class="acf-tab-wrap"><ul class="hl clearfix acf-tab-group"></ul></div>';
			}
			
			
			// append html
			$wrap.children('.field_type-tab:first').before( html );
			
		},
		
		add_tab : function( $tab ){
			
			// vars
			var $field	= $tab.closest('.field'),
				$wrap	= $field.parent(),
				
				key		= $field.attr('data-field_key'),
				label 	= $tab.text();
				
				
			// create tab group if it doesnt exist
			if( ! $wrap.children('.acf-tab-wrap').exists() )
			{
				this.add_group( $wrap );
			}
			
			// add tab
			$wrap.children('.acf-tab-wrap').find('.acf-tab-group').append('<li class="field_key-' + key + '" data-field_key="' + key + '"><a class="acf-tab-button" href="#" data-key="' + key + '">' + label + '</a></li>');
			
		},
		
		toggle : function( $a ){
			
			// vars
			var $wrap	= $a.closest('.acf-tab-wrap').parent(),
				key		= $a.attr('data-key');
			
			
			// classes
			$a.parent('li').addClass('active').siblings('li').removeClass('active');
			
			
			// hide / show
			$wrap.children('.field_type-tab').each(function(){
				
				// vars
				var $tab = $(this),
					show =  false;
					
				
				if( $tab.hasClass('field_key-' + key) )
				{
					show = true;
				}
				
				
				$tab.nextUntil('.field_type-tab').each(function(){
					
					if( show )
					{
						$(this).removeClass('acf-tab_group-hide').addClass('acf-tab_group-show');
						$(document).trigger('acf/fields/tab/show', [ $(this) ]);
					}
					else
					{
						$(this).removeClass('acf-tab_group-show').addClass('acf-tab_group-hide');
						$(document).trigger('acf/fields/tab/hide', [ $(this) ]);
					}
					
				});
				
			});
	
			
			// blur to remove dotted lines around button
			$a.trigger('blur');
			
		},
		
		refresh : function( $el ){
			
			// reference
			var _this = this;
			
			
			// trigger
			$el.find('.acf-tab-group').each(function(){
				
				$(this).find('.acf-tab-button:first').each(function(){
					
					_this.toggle( $(this) );
					
				});
				
			});
			
			
			// trigger conditional logic
			// this code ( acf/setup_fields ) is run after the main acf.conditional_logic.init();
			acf.conditional_logic.change();
			
		}
		
	};
	
	
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
		
		// add tabs
		$(el).find('.acf-tab').each(function(){
			
			acf.fields.tab.add_tab( $(this) );
			
		});
		
		
		// activate first tab
		acf.fields.tab.refresh( $(el) );
		
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
		
		acf.fields.tab.toggle( $(this) );
		
	});
	
	
	$(document).on('acf/conditional_logic/hide', function( e, $target, item ){
		
		// validate
		if( ! $target.parent().hasClass('acf-tab-group') )
		{
			return;
		}
		
		
		var key = $target.attr('data-field_key');
		
		
		if( $target.siblings(':visible').exists() )
		{
			// if the $target to be hidden is a tab button, lets toggle a sibling tab button
			$target.siblings(':visible').first().children('a').trigger('click');
		}
		else
		{
			// no onther tabs
			$('.field_type-tab[data-field_key="' + key + '"]').nextUntil('.field_type-tab').removeClass('acf-tab_group-show').addClass('acf-tab_group-hide');
		}
		
	});
	
	
	$(document).on('acf/conditional_logic/show', function( e, $target, item ){
		
		// validate
		if( ! $target.parent().hasClass('acf-tab-group') )
		{
			return;
		}
		
		
		// if this is the active tab
		if( $target.hasClass('active') )
		{
			$target.children('a').trigger('click');
			return;
		}
		
		
		// if the sibling active tab is actually hidden by conditional logic, take ownership of tabs
		if( $target.siblings('.active').hasClass('acf-conditional_logic-hide') )
		{
			// show this tab group
			$target.children('a').trigger('click');
			return;
		}
		

	});
	
	

})(jQuery);