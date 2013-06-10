<?php

class acf_field_number extends acf_field
{
	
	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function __construct()
	{
		// vars
		$this->name = 'number';
		$this->label = __("Number",'acf');
		$this->defaults = array(
			'default_value'	=>	'',
			'min' => '',
			'max' => '',
			'step' => ''
		);
		
		
		// do not delete!
    	parent::__construct();
	}
		
	
	/*
	*  create_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function create_field( $field )
	{
		$o = array( 'id', 'class', 'min', 'max', 'step', 'name', 'value' );
		
		$e = '<input type="number"';
		
		foreach( $o as $k )
		{
			$e .= ' ' . $k . '="' . esc_attr( $field[ $k ] ) . '"';	
		}
		
		$e .= ' />';
		
		echo $e;
		
	}
	
	
	/*
	*  create_options()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function create_options( $field )
	{
		// vars
		$key = $field['name'];
		
		?>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Default Value",'acf'); ?></label>
	</td>
	<td>
		<?php
		
		do_action('acf/create_field', array(
			'type'	=>	'number',
			'name'	=>	'fields['.$key.'][default_value]',
			'value'	=>	$field['default_value'],
		));

		?>
	</td>
</tr>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Min",'acf'); ?></label>
		<p><?php _e("Specifies the minimum value allowed",'acf'); ?></p>
	</td>
	<td>
		<?php
		
		do_action('acf/create_field', array(
			'type'	=>	'number',
			'name'	=>	'fields['.$key.'][min]',
			'value'	=>	$field['min'],
		));

		?>
	</td>
</tr>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Max",'acf'); ?></label>
		<p><?php _e("Specifies the maximim value allowed",'acf'); ?></p>
	</td>
	<td>
		<?php
		
		do_action('acf/create_field', array(
			'type'	=>	'number',
			'name'	=>	'fields['.$key.'][max]',
			'value'	=>	$field['max'],
		));

		?>
	</td>
</tr>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Step",'acf'); ?></label>
		<p><?php _e("Specifies the legal number intervals",'acf'); ?></p>
	</td>
	<td>
		<?php
		
		do_action('acf/create_field', array(
			'type'	=>	'number',
			'name'	=>	'fields['.$key.'][step]',
			'value'	=>	$field['step'],
		));

		?>
	</td>
</tr>

		<?php
	}
	
	
	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the $post_id of which the value will be saved
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field )
	{
		// remove ','
		$value = str_replace(',', '', $value);
		
		
		// convert to float. This removes any chars
		$value = floatval( $value );
		
		
		// convert back to string. This alows decimals to save
		$value = (string) $value;
		
		
		return $value;
	}
	
	
}

new acf_field_number();

?>