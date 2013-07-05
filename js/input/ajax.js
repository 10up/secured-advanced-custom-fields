(function($){
	
	
	/*
	*  acf.screen
	*
	*  description
	*
	*  @type	object
	*  @date	3/09/12
	*/
	
	acf.screen = {
		action 			:	'acf/location/match_field_groups_ajax',
		post_id			:	0,
		page_template	:	0,
		page_parent		:	0,
		page_type		:	0,
		post_category	:	0,
		post_format		:	0,
		taxonomy		:	0,
		lang			:	0,
		nonce			:	0
	};
	
	
	/*
	*  Document Ready
	*
	*  Updates acf.screen with more data
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).ready(function(){
		
		
		// update post_id
		acf.screen.post_id = acf.o.post_id;
		acf.screen.nonce = acf.o.nonce;
		
		
		// MPML
		if( $('#icl-als-first').length > 0 )
		{
			var href = $('#icl-als-first').children('a').attr('href'),
				regex = new RegExp( "lang=([^&#]*)" ),
				results = regex.exec( href );
			
			// lang
			acf.screen.lang = results[1];
			
		}
		
	});
	
	
	/*
	*  update_field_groups
	*
	*  @description: finds the new id's for metaboxes and show's hides metaboxes
	*  @created: 1/03/2011
	*/
	
	$(document).live('acf/update_field_groups', function(){
		
		
		$.ajax({
			url: ajaxurl,
			data: acf.screen,
			type: 'post',
			dataType: 'json',
			success: function(result){
				
				// validate
				if( !result )
				{
					return false;
				}
				
				
				// hide all metaboxes
				$('.acf_postbox').addClass('acf-hidden');
				$('.acf_postbox-toggle').addClass('acf-hidden');
		
				
				// dont bother loading style or html for inputs
				if( result.length == 0 )
				{
					return false;
				}
				
				
				// show the new postboxes
				$.each(result, function(k, v) {
					
					
					// vars
					var $el = $('#acf_' + v),
						$toggle = $('#adv-settings .acf_postbox-toggle[for="acf_' + v + '-hide"]');
					
					
					// classes
					$el.removeClass('acf-hidden hide-if-js');
					$toggle.removeClass('acf-hidden');
					$toggle.find('input[type="checkbox"]').attr('checked', 'checked');
					
					
					// load fields if needed
					$el.find('.acf-replace-with-fields').each(function(){
						
						var $replace = $(this);
						
						$.ajax({
							url			:	ajaxurl,
							data		:	{
								action	:	'acf/input/render_fields',
								acf_id	:	v,
								post_id	:	acf.o.post_id,
								nonce	:	acf.o.nonce
							},
							type		:	'post',
							dataType	:	'html',
							success		:	function( html ){
							
								$replace.replaceWith( html );
								
								$(document).trigger('acf/setup_fields', $el);
								
							}
						});
						
					});
				});
				
				
				// load style
				$.ajax({
					url			:	ajaxurl,
					data		:	{
						action	:	'acf/input/get_style',
						acf_id	:	result[0],
						nonce	:	acf.o.nonce
					},
					type		: 'post',
					dataType	: 'html',
					success		: function( result ){
					
						$('#acf_style').html( result );
						
					}
				});
				
				
				
			}
		});
	});

	
	/*
	*  $(document).trigger('acf/update_field_groups'); (Live change events)
	*
	*  @description: call the $(document).trigger('acf/update_field_groups'); event on live events
	*  @created: 1/03/2011
	*/
		
	$('#page_template').live('change', function(){
		
		acf.screen.page_template = $(this).val();
		
		$(document).trigger('acf/update_field_groups');
	    
	});
	
	
	$('#parent_id').live('change', function(){
		
		var val = $(this).val();
		
		
		// set page_type / page_parent
		if( val != "" )
		{
			acf.screen.page_type = 'child';
			acf.screen.page_parent = val;
		}
		else
		{
			acf.screen.page_type = 'parent';
			acf.screen.page_parent = 0;
		}
		
		
		$(document).trigger('acf/update_field_groups');
	    
	});

	
	$('#post-formats-select input[type="radio"]').live('change', function(){
		
		var val = $(this).val();
		
		if( val == '0' )
		{
			val = 'standard';
		}
		
		acf.screen.post_format = val;
		
		$(document).trigger('acf/update_field_groups');
		
	});	
	
	
	// taxonomy / category
	$('.categorychecklist input[type="checkbox"]').live('change', function(){
		
		// set timeout to fix issue with chrome which does not register the change has yet happened
		setTimeout(function(){
			
			// vars
			var values = [];
			
			
			$('.categorychecklist input[type="checkbox"]:checked').each(function(){
				values.push( $(this).val() );
			});
	
			
			acf.screen.post_category = values;
			acf.screen.taxonomy = values;
	
	
			$(document).trigger('acf/update_field_groups');
		
		}, 1);
		
		
	});
	
	
	
})(jQuery);