<?php

class acf_Date_picker extends acf_Field
{

	/*--------------------------------------------------------------------------------------
	*
	*	Constructor
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	*	@updated 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function __construct($parent)
	{
    	parent::__construct($parent);
    	
    	$this->name = 'date_picker';
		$this->title = __("Date Picker",'acf');
		
   	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	create_field
	*
	*	@author Elliot Condon
	*	@since 2.0.5
	*	@updated 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function create_field($field)
	{
		// defaults
		$defaults = array(
			'date_format' 		=>	'yymmdd',
			'display_format'	=>	'dd/mm/yy',
		);
		
		$field = array_merge($defaults, $field);
		
		
		// make sure it's not blank
		if( !$field['date_format'] )
		{
			$field['date_format'] = 'yymmdd';
		}
		if( !$field['display_format'] )
		{
			$field['display_format'] = 'dd/mm/yy';
		}
		

		// html
		echo '<input type="hidden" value="' . $field['value'] . '" name="' . $field['name'] . '" class="acf-hidden-datepicker" />';
		echo '<input type="text" value="" class="acf_datepicker" data-save_format="' . $field['date_format'] . '" data-display_format="' . $field['display_format'] . '" />';

	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	create_options
	*
	*	@author Elliot Condon
	*	@since 2.0.6
	*	@updated 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function create_options($key, $field)
	{
		// defaults
		$defaults = array(
			'date_format' 		=>	'yymmdd',
			'display_format'	=>	'dd/mm/yy',
		);
		
		$field = array_merge($defaults, $field);


		?>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Save format",'acf'); ?></label>
				<p class="description"><?php _e("This format will determin the value saved to the database and returned via the API",'acf'); ?></p>
				<p><?php _e("\"yymmdd\" is the most versatile save format. Read more about",'acf'); ?> <a href="http://docs.jquery.com/UI/Datepicker/formatDate"><?php _e("jQuery date formats",'acf'); ?></a></p>
			</td>
			<td>
				<input type="text" name="fields[<?php echo $key; ?>][date_format]" value="<?php echo $field['date_format']; ?>" />
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Display format",'acf'); ?></label>
				<p class="description"><?php _e("This format will be seen by the user when entering a value",'acf'); ?></p>
				<p><?php _e("\"dd/mm/yy\" or \"mm/dd/yy\" are the most used display formats. Read more about",'acf'); ?> <a href="http://docs.jquery.com/UI/Datepicker/formatDate" target="_blank"><?php _e("jQuery date formats",'acf'); ?></a></p>
			</td>
			<td>
				<input type="text" name="fields[<?php echo $key; ?>][display_format]" value="<?php echo $field['display_format']; ?>" />
			</td>
		</tr>
		<?php
	}
		
	
	
}

?>