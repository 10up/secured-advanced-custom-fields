<?php

class acf_Tab extends acf_Field
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
    	
    	$this->name = 'tab';
		$this->title = __("Tab",'acf');
		
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
		echo '<div class="acf-tab" data-id="' . $field['key'] . '">' . $field['label'] . '</div>';
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
		?>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Instructions",'acf'); ?></label>
			</td>
			<td>
				<p><?php _e("All fields proceeding this \"tab field\" (or until another \"tab field\"  is defined) will appear grouped on the edit screen.",'acf'); ?></p>
				<p><?php _e("You can use multiple tabs to break up your fields into sections.",'acf'); ?></p>
			</td>
		</tr>
		<?php
	}
	
}

?>