<?php

class acf_field_google_map extends acf_field
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
		$this->name = 'google_map';
		$this->label = __("Google Map",'acf');
		$this->category = __("jQuery",'acf');
		$this->defaults = array(
			'multiple' 		=>	0,
		);
		
		
		// do not delete!
    	parent::__construct();
	}
	
	
	/*
	*  load_field()
	*  
	*  This filter is appied to the $field after it is loaded from the database
	*  
	*  @type filter
	*  @since 3.6
	*  @date 23/01/13
	*  
	*  @param $field - the field array holding all the field options
	*  
	*  @return $field - the field array holding all the field options
	*/
	
	function load_field( $field )
	{
				
		
		// return
		return $field;
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
		// require the googlemaps JS
		wp_enqueue_script('acf-googlemaps');
		
		
		// vars
		$o = array(
			'class'		=>	'',
		);
		
		if( $field['value'] )
		{
			$o['class'] = 'active';
		}
		
		
		// default value
		if( !is_array($field['value']) )
		{
			$field['value'] = array();
		}
		
		$field['value'] = wp_parse_args($field['value'], array(
			'address'	=> '',
			'lat'		=> '',
			'lng'		=> ''
		));
		
		
		?>
		<div class="acf-location <?php echo $o['class']; ?>" data-id="<?php echo $field['id']; ?>">
			
			<div style="display:none;">
				<?php foreach( $field['value'] as $k => $v ): ?>
					<input type="hidden" class="input-<?php echo $k; ?>" name="<?php echo esc_attr($field['name']); ?>[<?php echo $k; ?>]" value="<?php echo esc_attr( $v ); ?>" />
				<?php endforeach; ?>
			</div>
			
			<div class="title">
				
				<div class="has-value">
					<a href="#" class="acf-sprite-remove ir">Remove</a>
					<h4><?php echo $field['value']['address']; ?></h4>
				</div>
				
				<div class="no-value">
					<a href="#" class="acf-sprite-locate ir">Locate</a>
					<input type="text" placeholder="Search for address..." class="search" />
				</div>
				
			</div>
			
			<div class="canvas">
				
			</div>
			
		</div>
		<?php
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
		<label><?php _e("Select multiple values?",'acf'); ?></label>
	</td>
	<td>
		<?php 
		do_action('acf/create_field', array(
			'type'	=>	'radio',
			'name'	=>	'fields['.$key.'][multiple]',
			'value'	=>	$field['multiple'],
			'choices'	=>	array(
				1	=>	__("Yes",'acf'),
				0	=>	__("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		?>
	</td>
</tr>
		<?php
		
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed to the create_field action
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/
	
	function format_value( $value, $post_id, $field )
	{
				
		// return value
		return $value;	
	}
	
	
	/*
	*  format_value_for_api()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed back to the api functions such as the_field
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/
	
	function format_value_for_api( $value, $post_id, $field )
	{
		
		
		// return
		return $value;
		
	}	
}

new acf_field_google_map();

?>