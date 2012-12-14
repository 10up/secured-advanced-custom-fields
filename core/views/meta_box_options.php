<?php

/*
*  Meta Box: Options
*
*  @description: 
*  @created: 23/06/12
*/
	

// global
global $post;

	
// vars
$options = $this->parent->get_acf_options($post->ID);
	

?>
<table class="acf_input widefat" id="acf_options">
	<tr>
		<td class="label">
			<label for=""><?php _e("Order No.",'acf'); ?></label>
			<p class="description"><?php _e("Field groups are created in order <br />from lowest to highest",'acf'); ?></p>
		</td>
		<td>
			<?php 
			
			$this->parent->create_field(array(
				'type'	=>	'text',
				'name'	=>	'menu_order',
				'value'	=>	$post->menu_order,
			));
			
			?>
		</td>
	</tr>
	<tr>
		<td class="label">
			<label for=""><?php _e("Position",'acf'); ?></label>
		</td>
		<td>
			<?php 
			
			$this->parent->create_field(array(
				'type'	=>	'radio',
				'name'	=>	'options[position]',
				'value'	=>	$options['position'],
				'choices' => array(
					'normal'	=>	__("Normal",'acf'),
					'side'		=>	__("Side",'acf'),
				)
			));

			?>
		</td>
	</tr>
	<tr>
		<td class="label">
			<label for="post_type"><?php _e("Style",'acf'); ?></label>
		</td>
		<td>
			<?php 
			
			$this->parent->create_field(array(
				'type'	=>	'radio',
				'name'	=>	'options[layout]',
				'value'	=>	$options['layout'],
				'choices' => array(
					'no_box'	=>	__("No Metabox",'acf'),
					'default'	=>	__("Standard Metabox",'acf'),
				)
			));
			
			?>
		</td>
	</tr>
	<tr>
		<td class="label">
			<label for="post_type"><?php _e("Hide on screen",'acf'); ?></label>
			<p class="description"><?php _e("<b>Select</b> items to <b>hide</b> them from the edit screen",'acf'); ?></p>
			<p class="description"><?php _e("If multiple field groups appear on an edit screen, the first field group's options will be used. (the one with the lowest order number)",'acf'); ?></p>
		</td>
		<td>
			<?php 
			
			$this->parent->create_field(array(
				'type'	=>	'checkbox',
				'name'	=>	'options[hide_on_screen]',
				'value'	=>	$options['hide_on_screen'],
				'choices' => array(
					'the_content'		=>	__("Content Editor",'acf'),
					'excerpt'			=>	__("Excerpt"),
					'custom_fields'		=>	__("Custom Fields"),
					'discussion'		=>	__("Discussion"),
					'comments'			=>	__("Comments"),
					'revisions'			=>	__("Revisions"),
					'slug'				=>	__("Slug"),
					'author'			=>	__("Author"),
					'format'			=>	__("Format"),
					'featured_image'	=>	__("Featured Image"),
					'categories'		=>	__("Categories"),
					'tags'				=>	__("Tags"),
					'send-trackbacks'	=>	__("Send Trackbacks"),
				)
			));
			
			?>
		</td>
	</tr>
</table>