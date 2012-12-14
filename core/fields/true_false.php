<?php

class acf_True_false extends acf_Field
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
    	
    	$this->name = 'true_false';
		$this->title = __("True / False",'acf');
		
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
		// vars
		$field['message'] = isset($field['message']) ? $field['message'] : '';
		
		// html
		echo '<ul class="checkbox_list ' . $field['class'] . '">';
			echo '<input type="hidden" name="'.$field['name'].'" value="0" />';
			$selected = ($field['value'] == 1) ? 'checked="yes"' : '';
			echo '<li><label><input id="' . $field['id'] . '-1"  type="checkbox" name="'.$field['name'].'" value="1" ' . $selected . ' />' . $field['message'] . '</label></li>';
		
		echo '</ul>';

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
		$field['message'] = isset($field['message']) ? $field['message'] : '';
		?>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Message",'acf'); ?></label>
				<p class="description"><?php _e("eg. Show extra content",'acf'); ?></a></p>
			</td>
			<td>
				<?php 
				$this->parent->create_field(array(
					'type'	=>	'text',
					'name'	=>	'fields['.$key.'][message]',
					'value'	=>	$field['message'],
				));
				?>
			</td>
		</tr>

		<?php
	}


	/*--------------------------------------------------------------------------------------
	*
	*	get_value_for_api
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_value_for_api($post_id, $field)
	{
		// get value
		$value = parent::get_value($post_id, $field);
		
		if($value == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
		
}

?>