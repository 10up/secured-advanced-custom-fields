var acf = {
	post_id : 0,
	text : {
		'move_to_trash' : "Move to trash. Are you sure?",
		'checked' : 'checked',
		'conditional_no_fields' : 'No "toggle" fields available',
		'flexible_content_no_fields' : 'Flexible Content requires at least 1 layout'
	},
	fields : [],
	sortable_helper : null,
	nonce : ''
};

(function($){

	/*
	*  Exists
	*  
	*  @since			3.1.6
	*  @description		returns true or false on a element's existance
	*/
	
	$.fn.exists = function()
	{
		return $(this).length>0;
	};
	
	
	/*
	*  Sortable Helper
	*
	*  @description: keeps widths of td's inside a tr
	*  @since 3.5.1
	*  @created: 10/11/12
	*/
	
	acf.sortable_helper = function(e, ui)
	{
		ui.children().each(function(){
			$(this).width($(this).width());
		});
		return ui;
	};
	
	
	/*
	*  uniqid
	*  
	*  @since			3.1.6
	*  @description		Returns a unique ID (secconds of time)
	*/
	
	function uniqid()
    {
    	var newDate = new Date;
    	return newDate.getTime();
    }
    
    
	/*
	*  Place Confirm message on Publish trash button
	*  
	*  @since			3.1.6
	*  @description		
	*/
	
	$('#submit-delete').live('click', function(){
			
		var response = confirm(acf.text.move_to_trash);
		if(!response)
		{
			return false;
		}
		
	});
	
	
	/*
	*  acf/update_field_options
	*  
	*  @since			3.1.6
	*  @description		Load in the opions html
	*/
	
	$('#acf_fields tr.field_type select').live('change', function(){
		
		// vars
		var select = $(this),
			tbody = select.closest('tbody'),
			field = tbody.closest('.field'),
			field_type = field.attr('data-type'),
			field_key = field.attr('data-id'),
			val = select.val();
			
		
		
		// update data atts
		field.removeClass('field-' + field_type).addClass('field-' + val);
		field.attr('data-type', val);
		
		
		// show field options if they already exist
		if( tbody.children( 'tr.field_option_' + val ).exists() )
		{
			// hide + disable options
			tbody.children('tr.field_option').hide().find('[name]').attr('disabled', 'true');
			
			// show and enable options
			tbody.children( 'tr.field_option_' + val ).show().find('[name]').removeAttr('disabled');
		}
		else
		{
			// add loading gif
			var tr = $('<tr"><td class="label"></td><td><div class="acf-loading"></div></td></tr>');
			
			// hide current options
			tbody.children('tr.field_option').hide().find('[name]').attr('disabled', 'true');
			
			
			// append tr
			if( tbody.children('tr.conditional-logic').exists() )
			{
				tbody.children('tr.conditional-logic').before(tr);
			}
			else
			{
				tbody.children('tr.field_save').before(tr);
			}
			
			
			var ajax_data = {
				'action' : 'acf_field_options',
				'post_id' : acf.post_id,
				'field_key' : select.attr('name'),
				'field_type' : val,
				'nonce' : acf.nonce
			};
			
			$.ajax({
				url: ajaxurl,
				data: ajax_data,
				type: 'post',
				dataType: 'html',
				success: function(html){
					
					if( ! html )
					{
						alert('Error: Could not load field options');
					}
					
					tr.replaceWith(html);
					
				}
			});
		}
		
	});
	
	
	/*
	*  Update Names
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 15/10/12
	*/
	
	$.fn.update_names = function()
	{
		var field = $(this),
			old_id = field.attr('data-id');
		
		
		// load location html
		$.ajax({
			url: ajaxurl,
			data: {
				'action' : 'acf_next_field_id',
				'nonce' : acf.nonce
			},
			type: 'post',
			dataType: 'html',
			success: function( new_id ){
				
				// remove phantom new lines...
				new_id = new_id.replace(/(\r\n|\n|\r)/gm,"");
				
				
				// give field a new id
				field.attr('data-id', new_id);
				
				
				// update class
				var new_class = field.attr('class');
				new_class = new_class.replace(old_id, new_id);
				field.attr('class', new_class);
				
				
				// update field key column
				field.find('.field_meta td.field_key').text( new_id );
				
				
				// update inputs
				field.find('[name]').each(function()
				{	
					
					var name = $(this).attr('name');
					var id = $(this).attr('id');
		
					if(name && name.indexOf('[' + old_id + ']') != -1)
					{
						name = name.replace('[' + old_id + ']','[' + new_id + ']');
					}
					if(id && id.indexOf('[' + old_id + ']') != -1)
					{
						id = id.replace('[' + old_id + ']','[' + new_id + ']');
					}
					
					$(this).attr('name', name);
					$(this).attr('id', id);
					
				});

			}
		});
		
	}
	
	
	/*
	*  update_order_numbers
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 15/10/12
	*/
	
	function update_order_numbers(){
		
		$('#acf_fields .fields').each(function(){
			$(this).children('.field').each(function(i){
				$(this).find('td.field_order .circle').first().html(i+1);
			});
		});

	}
	
	
	/*
	*  Edit Field
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 13/10/12
	*/
	
	$('#acf_fields a.acf_edit_field').live('click', function(){

		var field = $(this).closest('.field');
		
		if( field.hasClass('form_open') )
		{
			field.removeClass('form_open');
			$(document).trigger('acf/field_form-close', field);
		}
		else
		{
			field.addClass('form_open');
			$(document).trigger('acf/field_form-open', field);
		}
		
		field.children('.field_form_mask').animate({'height':'toggle'}, 500);
		
		
	});
	
	
	/*
	*  Delete Field
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 13/10/12
	*/
	
	$('#acf_fields a.acf_delete_field').live('click', function(){
		
		// vars
		var a = $(this),
			field = a.closest('.field'),
			fields = field.closest('.fields'),
			temp = $('<div style="height:' + field.height() + 'px"></div>');
			
			
		// fade away
		field.animate({'left' : '50px', 'opacity' : 0}, 250, function(){
			
			field.before(temp);
			field.remove();
			

			// no more fields, show the message
			if( fields.children('.field').length <= 1 )
			{
				temp.remove();
				fields.children('.no_fields_message').show();
			}
			else
			{
				temp.animate({'height' : 0 }, 250, function(){
					temp.remove();
				})
			}
			
			update_order_numbers();
			
		});
		
		
	});
	
	
	/*
	*  Duplicate Field
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 13/10/12
	*/
	
	$('#acf_fields a.acf_duplicate_field').live('click', function(){
			
		// vars
		var a = $(this),
			field = a.closest('.field'),
			orig_type = field.find('tr.field_type select').val(),
			new_field = field.clone();
			
			
		
		// update names
		new_field.update_names();

		
		// add new field
		field.after( new_field );
		
		
		// open up form
		new_field.find('a.acf_edit_field').first().trigger('click');
		new_field.find('tr.field_type select').first().val( orig_type ).trigger('change');
		
		
		// update order numbers
		update_order_numbers();
		
	});
	
	
	/*
	*  Add Field
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 13/10/12
	*/
	
	$('#acf_fields #add_field').live('click',function(){
		
		var table_footer = $(this).closest('.table_footer');
		var fields = table_footer.siblings('.fields');
		
		
		// clone last tr
		var new_field = fields.children('.field-field_clone').clone();
		
		
		// update names
		new_field.update_names();
		
		
		// show (update_names will remove the field_clone field, but not for a few seconds)
		new_field.show();
		
		
		// append to table
		fields.children('.field-field_clone').before(new_field);
		
		
		// remove no fields message
		if(fields.children('.no_fields_message').exists())
		{
			fields.children('.no_fields_message').hide();
		}
		
		
		// clear name
		new_field.find('.field_form input[type="text"]').val('');
		new_field.find('.field_form input[type="text"]').first().focus();
		new_field.find('tr.field_type select').trigger('change');	
		
		
		// open up form
		new_field.find('a.acf_edit_field').first().trigger('click');

		
		// update order numbers
		update_order_numbers();
		
		return false;
		
		
	});
	
	
	/*
	*  Auto Complete Field Name
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 15/10/12
	*/

	$('#acf_fields tr.field_label input.label').live('blur', function()
	{
		var label = $(this);
		var name = $(this).closest('tr').siblings('tr.field_name').find('input.name');

		if(name.val() == '')
		{
			var val = label.val().toLowerCase().split(' ').join('_').split('\'').join('');
			name.val(val);
			name.trigger('keyup');
		}
	});
	
	
	/*
	*  Update field meta
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 15/10/12
	*/
	
	$('#acf_fields .field_form tr.field_label input.label').live('keyup', function()
	{
		var val = $(this).val();
		var name = $(this).closest('.field').find('td.field_label strong a').first().html(val);
	});
	$('#acf_fields .field_form tr.field_name input.name').live('keyup', function()
	{
		var val = $(this).val();
		var name = $(this).closest('.field').find('td.field_name').first().html(val);
	});
	$('#acf_fields .field_form tr.field_type select').live('change', function()
	{
		var val = $(this).val();
		var label = $(this).find('option[value="' + val + '"]').html();
		
		// update field type (if not a clone field)
		if($(this).closest('.field_clone').length == 0)
		{
			$(this).closest('.field').find('td.field_type').first().html(label);
		}
		
	});
	
	
	// sortable
	$('#acf_fields td.field_order').live('mouseover', function(){
		
		var fields = $(this).closest('.fields');
		
		if(fields.hasClass('sortable')) return false;
		
		fields.addClass('sortable').sortable({
			update: function(event, ui){
				update_order_numbers();
			},
			handle: 'td.field_order'
		});
	});
	
	
	/*
	*  Setup Location Rules
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 15/10/12
	*/
	
	$(document).ready(function(){
		
		// vars
		var location_rules = $('#location_rules');
		
		
		// does it have options?
		if( !location_rules.find('td.param select option[value="options_page"]').exists() )
		{
			var html = $('#acf_location_options_deactivated').html();
			location_rules.find('td.param select').append( html );
				
		}
		
	});
	
	
	/*
	*  Location Rules Change
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 15/10/12
	*/

	$('#location_rules .param select').live('change', function(){
		
		// vars
		var tr = $(this).closest('tr'),
			i = tr.attr('data-i'),
			ajax_data = {
				'action' : "acf_location",
				'key' : i,
				'value' : '',
				'param' : $(this).val()
			};
		
		
		// add loading gif
		var div = $('<div class="acf-loading"></div>');
		tr.find('td.value').html(div);
		
		
		// load location html
		$.ajax({
			url: ajaxurl,
			data: ajax_data,
			type: 'post',
			dataType: 'html',
			success: function(html){

				div.replaceWith(html);

			}
		});
		
		
	});
	
	
	/*
	*  Location Rules add
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 15/10/12
	*/
	
	$('#location_rules a.acf-button-add').live('click',function(){
			
		// vars
		var old_tr = $(this).closest('tr'),
			new_tr = old_tr.clone(),
			old_i = parseFloat( new_tr.attr('data-i') ),
			new_i = old_i + 1;
		
		
		// update names
		new_tr.find('[name]').each(function(){
			
			$(this).attr('name', $(this).attr('name').replace('[' + old_i + ']', '[' + new_i + ']') );
			$(this).attr('id', $(this).attr('id').replace('[' + old_i + ']', '[' + new_i + ']') );
			
		});
			
			
		// update data-i
		new_tr.attr('data-i', new_i);
		
		
		// add tr
		old_tr.after( new_tr );
		
		
		// remove disabled
		old_tr.closest('table').removeClass('remove-disabled');
				
		
		return false;
		
	});
	
	
	/*
	*  Location Rules remove
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 15/10/12
	*/
	
	$('#location_rules a.acf-button-remove').live('click',function(){
			
		var table = $(this).closest('table');
		
		// validate
		if( table.hasClass('remove-disabled') )
		{
			return false;
		}
		
		
		// remove tr
		$(this).closest('tr').remove();
		
		
		// add clas to table
		if( table.find('tr').length <= 1 )
		{
			table.addClass('remove-disabled');
		}
		
		
		return false;
		
	});
	

	/*----------------------------------------------------------------------
	*
	*	Document Ready
	*
	*---------------------------------------------------------------------*/
	
	$(document).ready(function(){
		
		// custom Publish metabox
		$('#submitdiv #publish').attr('class', 'acf-button');
		$('#submitdiv a.submitdelete').attr('class', 'delete-field-group').attr('id', 'submit-delete');
		
	});
	
	
	/*
	*  Flexible Content
	*
	*  @description: extra javascript for the flexible content field
	*  @created: 3/03/2011
	*/
	
	/*----------------------------------------------------------------------
	*
	*	Add Layout Option
	*
	*---------------------------------------------------------------------*/
	
	$('#acf_fields .acf_fc_add').live('click', function(){
		
		// vars
		var tr = $(this).closest('tr.field_option_flexible_content'),
			new_tr = tr.clone(false),
			id = new_tr.attr('data-id'),
			new_id = uniqid();
		
		
		// remove sub fields
		new_tr.find('.field:not(.field-field_clone)').remove();
		
		// show add new message
		new_tr.find('.no_fields_message').show();
		
		// reset layout meta values
		new_tr.find('.acf_cf_meta input[type="text"]').val('');
		
		
		// update id / names
		new_tr.find('[name]').each(function(){
		
			var name = $(this).attr('name').replace('[layouts]['+id+']','[layouts]['+new_id+']');
			$(this).attr('name', name);
			$(this).attr('id', name);
			
		});
		
		// update data-id
		new_tr.attr('data-id', new_id);
		
		// add new tr
		tr.after(new_tr);
		
		// display
		new_tr.find('.acf_cf_meta select').val('row').trigger('change');
		
		
		return false;
	});
	
	
	/*----------------------------------------------------------------------
	*
	*	Duplicate Layout
	*
	*---------------------------------------------------------------------*/
	
	$('#acf_fields .acf_fc_duplicate').live('click', function(){
		
		// vars
		var tr = $(this).closest('tr.field_option_flexible_content'),
			new_tr = tr.clone(false),
			id = new_tr.attr('data-id'),
			new_id = uniqid();
		
		
		// reset layout meta values
		new_tr.find('.acf_cf_meta input[type="text"]').val('');
		new_tr.find('.acf_cf_meta select').val('row').trigger('change');
		
		
		// update id / names
		new_tr.find('[name]').each(function(){
		
			var name = $(this).attr('name').replace('[layouts]['+id+']','[layouts]['+new_id+']');
			$(this).attr('name', name);
			$(this).attr('id', name);
			
		});
		
		
		// update data-id
		new_tr.attr('data-id', new_id);
		
		
		// add new tr
		tr.after(new_tr);
	
		
		return false;
	});
	
	
	/*----------------------------------------------------------------------
	*
	*	Delete Layout Option
	*
	*---------------------------------------------------------------------*/
	
	$('#acf_fields .acf_fc_delete').live('click', function(){

		var tr = $(this).closest('tr.field_option_flexible_content'),
			tr_count = tr.siblings('tr.field_option.field_option_flexible_content').length;

		if( tr_count <= 1 )
		{
			alert( acf.text.flexible_content_no_fields );
			return false;
		}
		
		tr.animate({'left' : '50px', 'opacity' : 0}, 250, function(){
			tr.remove();
		});
		
	});
	
	
	/*----------------------------------------------------------------------
	*
	*	Sortable Layout Option
	*
	*---------------------------------------------------------------------*/
	
	$('#acf_fields .acf_fc_reorder').live('mouseover', function(){
		
		var table = $(this).closest('table.acf_field_form_table');
		
		if(table.hasClass('sortable')) return false;
		
		table.addClass('sortable').children('tbody').sortable({
			items: ".field_option_flexible_content",
			handle: 'a.acf_fc_reorder',
			helper: acf.sortable_helper,
			forceHelperSize : true,
			forcePlaceholderSize : true,
			scroll : true,
			start : function (event, ui) {

				// add markup to the placeholder
				var td_count = ui.item.children('td').length;
        		ui.placeholder.html('<td colspan="' + td_count + '"></td>');
        		
   			}
		});
		
	});
	
	
	/*----------------------------------------------------------------------
	*
	*	Label update name
	*
	*---------------------------------------------------------------------*/
	
	$('#acf_fields .acf_fc_label input[type="text"]').live('blur', function(){
		
		var label = $(this);
		var name = $(this).parents('td').siblings('td.acf_fc_name').find('input[type="text"]');

		if(name.val() == '')
		{
			var val = label.val().toLowerCase().split(' ').join('_').split('\'').join('');
			name.val(val);
			name.trigger('keyup');
		}

	});
	
	
	/*
	*  Repeater CHange layout display (Row | Table)
	*
	*  @description: 
	*  @since 3.5.2
	*  @created: 18/11/12
	*/
	
	$('#acf_fields .field_option_repeater_layout input[type="radio"]').live('click', function(){
		
		// vars
		var radio = $(this);
		
		
		// Set class
		radio.closest('.field_option_repeater').siblings('.field_option_repeater_fields').find('.repeater:first').removeClass('layout-row').removeClass('layout-table').addClass( 'layout-' + radio.val() );
		
	});
	
	$(document).live('acf/field_form-open', function(e, field){
		
		$(field).find('.field_option_repeater_layout input[type="radio"]:checked').each(function(){
			$(this).trigger('click');
		});
		
	});
	
	
	
	/*
	*  Flexible Content CHange layout display (Row | Table)
	*
	*  @description: 
	*  @since 3.5.2
	*  @created: 18/11/12
	*/
	
	$('#acf_fields .acf_fc_display select').live('change', function(){
		
		// vars
		var select = $(this);
		
		
		// Set class
		select.closest('.repeater').removeClass('layout-row').removeClass('layout-table').addClass( 'layout-' + select.val() );
		
	});
	
	$(document).live('acf/field_form-open', function(e, field){
		
		$(field).find('.acf_fc_display select').each(function(){
			$(this).trigger('change');
		});
		
	});

	
	
	/*
	*  Screen Options
	*
	*  @description: 
	*  @created: 4/09/12
	*/
	
	$('#adv-settings input[name="show-field_key"]').live('change', function(){
		
		if( $(this).val() == "1" )
		{
			$('#acf_fields table.acf').addClass('show-field_key');
		}
		else
		{
			$('#acf_fields table.acf').removeClass('show-field_key');
		}
		
	});
	
	
	/*
	*  Conditional Logic
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 11/10/12
	*/
	
	acf.create_field = function( options ){
		
		// dafaults
		var defaults = {
			'type' : 'text',
			'class' : '',
			'name' : '',
			'value' : ''
		};
		options = $.extend(true, defaults, options);
		
		
		// vars
		var html = "";
		
		if( options.type == "text" )
		{
			html += '<input class="text ' + options.class + '" type="text" id="' + options.name + '" name="' + options.name + '" value="' + options.value + '" />';
		}
		else if( options.type == "select" )
		{
			html += '<select class="select ' + options.class + '" id="' + options.name + '" name="' + options.name + '">';
			if( options.choices )
			{
				for( i = 0; i < options.choices.length; i++ )
				{
					var attr = '';
					if( options.choices[i].value == options.value )
					{
						attr = 'selected="selected"';
					}
					html += '<option ' + attr + ' value="' + options.choices[i].value + '">' + options.choices[i].label + '</option>';
				}
			}
			html += '</select>';
		}
		
		html = $(html);
		
		return html;
			
	};
	
	$(document).live('acf/field_form-open', function(e, field){
		
		// populate fields
		acf.setup_conditional_fields();
		
		
		$(field).find('.conditional-logic-field').each(function(){
			
			var val = $(this).val(),
				name = $(this).attr('name'),
				choices = [];
			
			
			// populate choices
			if( acf.fields )
			{
				for( i = 0; i < acf.fields.length; i++ )
				{
					choices.push({
						value : acf.fields[i].id,
						label : acf.fields[i].label
					});
				}
			}
			
			
			// empty?
			if( choices.length == 0 )
			{
				choices.push({
					'value' : 'null',
					'label' : acf.text.conditional_no_fields
				})
			}
	
			
			// create select
			select = acf.create_field({
				'type' : 'select',
				'class' : 'conditional-logic-field',
				'name' : name,
				'value' : val,
				'choices' : choices
			});
			

			$(this).replaceWith( select );
			
			select.trigger('change');
				
		});
		
	});
	
	
	
	/*
	*  Toggle Conditional Logic
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 14/10/12
	*/
	
	$('tr.conditional-logic input[type="radio"]').live('change', function(){
		
		if( $(this).val() == "1" )
		{
			$(this).closest('tr.conditional-logic').find('.contional-logic-rules-wrapper').show();
		}
		else
		{
			$(this).closest('tr.conditional-logic').find('.contional-logic-rules-wrapper').hide();
		}
		
	});
	
	
	/*
	*  Conditional logic: Change field
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 14/10/12
	*/
	
	$('select.conditional-logic-field').live('change', function(){
		
		// vars
		var id = $(this).val(),
			field = $('#acf_fields .field-' + id),
			type = field.find('tr.field_type select').val(),
			conditional_function = $(this).closest('tr').find('.conditional-logic-value');
			
		
		// true / false
		choices = [];
		
		if( type == "true_false" )
		{
			choices = [
				{ value : 1, label : acf.text.checked }
			];
						
		}
		else if( type == "select" || type == "checkbox" || type == "radio" )
		{
			field_choices = field.find('.field_option-choices').val();
			field_choices = field_choices.split("\n");
						
			if( field_choices )
			{
				for( i = 0; i < field_choices.length; i++ )
				{
					var choice = field_choices[i].split(':');
					
					var label = choice[0];
					if( choice[1] )
					{
						label = choice[1];
					}
					
					choices.push({
						'value' : $.trim( choice[0] ),
						'label' : $.trim( label )
					})
					
				}
			}
			
		}
		
		
		// create select
		select = acf.create_field({
			'type' : 'select',
			'class' : 'conditional-logic-value',
			'name' : conditional_function.attr('name'),
			'value' : conditional_function.val(),
			'choices' : choices
		});
		
		conditional_function.replaceWith( select );
		
		
	});

	
	
	/*
	*  setup_conditional_fields
	*
	*  @description: populates the acf object with all available fields
	*  @since 3.5.1
	*  @created: 15/10/12
	*/
	
	acf.setup_conditional_fields = function()
	{
		acf.fields = [];
		
		$('#acf_fields > .inside > .fields > .field').each(function(){
			
			var field = $(this),
				id = field.attr('data-id');
				type = field.find('tr.field_type select').val(),
				label = field.find('tr.field_label input').val();
			
			if( type == 'select' || type == 'checkbox' || type == 'true_false' || type == 'radio' )
			{
				acf.fields.push({
					id : id,
					type : type,
					label : label
				});
			}
			
			
		});
		
	}
	
	
	/*
	*  Add conditional rule
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 15/10/12
	*/
	
	$('tr.conditional-logic a.add').live('click',function(){
		
		// vars
		var old_tr = $(this).closest('tr'),
			new_tr = old_tr.clone(),
			old_i = parseFloat( new_tr.attr('data-i') ),
			new_i = old_i + 1;
		
		
		// update names
		new_tr.find('[name]').each(function(){
			
			$(this).attr('name', $(this).attr('name').replace('[' + old_i + ']', '[' + new_i + ']') );
			$(this).attr('id', $(this).attr('id').replace('[' + old_i + ']', '[' + new_i + ']') );
			
		});
			
			
		// update data-i
		new_tr.attr('data-i', new_i);
		
		
		// add tr
		old_tr.after( new_tr );
		
		
		// remove disabled
		old_tr.closest('table').removeClass('remove-disabled');
				
		
		return false;
		
	});
	
	
	/*
	*  Remove conditional rule
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 15/10/12
	*/
	
	$('tr.conditional-logic a.remove').live('click',function(){
		
		var table = $(this).closest('table');
		
		// validate
		if( table.hasClass('remove-disabled') )
		{
			return false;
		}
		
		
		// remove tr
		$(this).closest('tr').remove();
		
		
		// add clas to table
		if( table.find('tr').length <= 1 )
		{
			table.addClass('remove-disabled');
		}
		
		
		return false;
		
	});

	

})(jQuery);