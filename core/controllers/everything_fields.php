<?php 

class acf_everything_fields 
{
	
	var $settings,
		$data;
	
	
	/*
	*  __construct
	*
	*  @description: 
	*  @since 3.1.8
	*  @created: 23/06/12
	*/
		
	function __construct()
	{
		// data for passing variables
		$this->data = array(
			'page_id' => '', // a string used to load values
			'metabox_ids' => array(),
			'page_type' => '', // taxonomy / user / media
			'page_action' => '', // add / edit
			'option_name' => '', // key used to find value in wp_options table. eg: user_1, category_4
		);
		
		
		// actions
		add_action('admin_menu', array($this,'admin_menu'));
		add_action('wp_ajax_acf/everything_fields', array($this, 'acf_everything_fields'));
		
		
		// save
		add_action('create_term', array($this, 'save_taxonomy'));
		add_action('edited_term', array($this, 'save_taxonomy'));
		add_action('edit_user_profile_update', array($this, 'save_user'));
		add_action('personal_options_update', array($this, 'save_user'));
		add_action('user_register', array($this, 'save_user'));
		add_filter("attachment_fields_to_save", array($this, 'save_attachment'), null , 2);


		// shopp
		add_action('shopp_category_saved', array($this, 'shopp_category_saved'));
		
		
		// delete
		add_action('delete_term', array($this, 'delete_term'), 10, 4);
	}
	
	
	/*
	*  validate_page
	*
	*  @description: returns true | false. Used to stop a function from continuing
	*  @since 3.2.6
	*  @created: 23/06/12
	*/
	
	function validate_page()
	{
		// global
		global $pagenow;
		
		
		// vars
		$return = false;
		
		
		// validate page
		if( in_array( $pagenow, array( 'edit-tags.php', 'profile.php', 'user-new.php', 'user-edit.php', 'media.php' ) ) )
		{
			$return = true;
		}
				
		
		// validate page (Shopp)
		if( $pagenow == "admin.php" && isset( $_GET['page'], $_GET['id'] ) && $_GET['page'] == "shopp-categories" )
		{
			$return = true;
		}
		
		
		// return
		return $return;
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_menu
	*
	*	@author Elliot Condon
	*	@since 3.1.8
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_menu() 
	{
	
		global $pagenow;

		
		// validate page
		if( ! $this->validate_page() ) return;
		
		
		// set page type
		$filter = array();
		
		if( $pagenow == "admin.php" && isset( $_GET['page'], $_GET['id'] ) && $_GET['page'] == "shopp-categories" )
		{
			// filter
			$_GET['id'] = filter_var($_GET['id'], FILTER_SANITIZE_STRING);
			
			
			$this->data['page_type'] = "shopp_category";
			$filter['ef_taxonomy'] = "shopp_category";
			
			$this->data['page_action'] = "add";
			$this->data['option_name'] = "";
			
			if( $_GET['id'] != "new" )
			{
				$this->data['page_action'] = "edit";
				$this->data['option_name'] = "shopp_category_" . $_GET['id'];
			}
			
		}
		if( $pagenow == "edit-tags.php" && isset($_GET['taxonomy']) )
		{
			// filter
			$_GET['taxonomy'] = filter_var($_GET['taxonomy'], FILTER_SANITIZE_STRING);
			
			
			$this->data['page_type'] = "taxonomy";
			$filter['ef_taxonomy'] = $_GET['taxonomy'];
			
			$this->data['page_action'] = "add";
			$this->data['option_name'] = "";
			
			if( isset($_GET['action']) && $_GET['action'] == "edit" )
			{
				// filter
				$_GET['tag_ID'] = filter_var($_GET['tag_ID'], FILTER_SANITIZE_NUMBER_INT);
			
				$this->data['page_action'] = "edit";
				$this->data['option_name'] = $_GET['taxonomy'] . "_" . $_GET['tag_ID'];
			}
			
		}
		elseif( $pagenow == "profile.php" )
		{
			
			$this->data['page_type'] = "user";
			$filter['ef_user'] = get_current_user_id();
			
			$this->data['page_action'] = "edit";
			$this->data['option_name'] = "user_" . get_current_user_id();
			
		}
		elseif( $pagenow == "user-edit.php" && isset($_GET['user_id']) )
		{
			// filter
			$_GET['user_id'] = filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT);

			
			$this->data['page_type'] = "user";
			$filter['ef_user'] = $_GET['user_id'];
			
			$this->data['page_action'] = "edit";
			$this->data['option_name'] = "user_" . $_GET['user_id'];
			
		}
		elseif( $pagenow == "user-new.php" )
		{
			$this->data['page_type'] = "user";
			$filter['ef_user'] ='all';
			
			$this->data['page_action'] = "add";
			$this->data['option_name'] = "";

		}
		elseif( $pagenow == "media.php" )
		{
			
			$this->data['page_type'] = "media";
			$filter['ef_media'] = 'all';
			
			$this->data['page_action'] = "add";
			$this->data['option_name'] = "";
			
			if(isset($_GET['attachment_id']))
			{
				// filter
				$_GET['attachment_id'] = filter_var($_GET['attachment_id'], FILTER_SANITIZE_NUMBER_INT);
			
			
				$this->data['page_action'] = "edit";
				$this->data['option_name'] = $_GET['attachment_id'];
			}
			
		}
		
		
		// get field groups
		$metabox_ids = array();
		$this->data['metabox_ids'] = apply_filters( 'acf/location/match_field_groups', $metabox_ids, $filter );

		
		// dont continue if no ids were found
		if( empty( $this->data['metabox_ids'] ) )
		{
			return false;	
		}
		
		
		// actions
		add_action('admin_enqueue_scripts', array($this,'admin_enqueue_scripts'));
		add_action('admin_head', array($this,'admin_head'));
		
		
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 30/01/13
	*/
	
	function admin_enqueue_scripts()
	{
		do_action('acf/input/admin_enqueue_scripts');
	}

	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_head
	*
	*	@author Elliot Condon
	*	@since 3.1.8
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_head()
	{	
		global $pagenow;
		
		
		// add user js + css
		do_action('acf/input/admin_head');
		
		
		?>
<script type="text/javascript">
(function($){

acf.data = {
	action 			:	'acf/everything_fields',
	metabox_ids		:	'<?php echo implode( ',', $this->data['metabox_ids'] ); ?>',
	page_type		:	'<?php echo $this->data['page_type']; ?>',
	page_action		:	'<?php echo $this->data['page_action']; ?>',
	option_name		:	'<?php echo $this->data['option_name']; ?>'
};

$(document).ready(function(){

	$.ajax({
		url: ajaxurl,
		data: acf.data,
		type: 'post',
		dataType: 'html',
		success: function(html){
			
<?php 
			if($this->data['page_type'] == "user")
			{
				if($this->data['page_action'] == "add")
				{
					echo "$('#createuser > table.form-table:last > tbody').append( html );";
				}
				else
				{
					echo "$('#your-profile > p.submit').before( html );";
				}
			}
			elseif($this->data['page_type'] == "shopp_category")
			{
				echo "$('#post-body-content').append( html );";
			}
			elseif($this->data['page_type'] == "taxonomy")
			{
				if($this->data['page_action'] == "add")
				{
					echo "$('#addtag > p.submit').before( html );";
				}
				else
				{
					echo "$('#edittag > table.form-table:last > tbody').append( html );";
				}
			}
			elseif($this->data['page_type'] == "media")
			{
				if($this->data['page_action'] == "add")
				{
					echo "$('#addtag > p.submit').before( html );";
				}
				else
				{
					echo "$('#media-single-form table tbody tr.submit').before( html );";
				}
			}
?>

			setTimeout( function(){ 
				$(document).trigger('acf/setup_fields', $('#wpbody') ); 
			}, 200);
			
		}
	});
	
	
	/*
	*  Taxonomy Add
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 24/02/13
	*/
	
	$(document).ajaxComplete(function(event, xhr, settings) {
		
		// vars
		data = acf.helpers.url_to_object(settings.data);
		
		
		// validate
		if( data.action != 'add-tag' )
		{
			return;
		}
		

		// vars
		var $el = $('#addtag');
		
		
		// clear WYSIWYG field
		$el.find('.acf_wysiwyg textarea').each(function(){

			
			// vars
			var textarea = $(this),
				id = textarea.attr('id'),
				editor = tinyMCE.get( id );
			
			if( editor )
			{
				editor.setContent('');
				editor.save();
			}
			
		});
		
		
		// clear image / file fields
		$el.find('.field .active').removeClass('active');
		
		
		// clear checkbox
		$el.find('input[type="checkbox"]').removeAttr('checked');

	});
	
});


})(jQuery);
</script>
		<?php
	}
	
		
	
	/*--------------------------------------------------------------------------------------
	*
	*	save_taxonomy
	*
	*	@author Elliot Condon
	*	@since 3.1.8
	* 
	*-------------------------------------------------------------------------------------*/
	
	function save_taxonomy( $term_id )
	{
		// verify nonce
		if( !isset($_POST['acf_nonce']) || !wp_verify_nonce($_POST['acf_nonce'], 'input') )
		{
			return $term_id;
		}
		
		
		// for some weird reason, this is triggered by saving a menu... 
		if( !isset($_POST['taxonomy']) )
		{
			return;
		}
		
		// $post_id to save against
		$post_id = $_POST['taxonomy'] . '_' . $term_id;
		
		
		// set post_lock
		$GLOBALS['acf_save_lock'] = $post_id;
				
		
		// update the post
		do_action('acf/save_post', $post_id);
		
		
		// set post_lock and allow saves
		$GLOBALS['acf_save_lock'] = false;
		
	}
		
		
	/*--------------------------------------------------------------------------------------
	*
	*	profile_save
	*
	*	@author Elliot Condon
	*	@since 3.1.8
	* 
	*-------------------------------------------------------------------------------------*/
	
	function save_user( $user_id )
	{
		// verify nonce
		if( !isset($_POST['acf_nonce']) || !wp_verify_nonce($_POST['acf_nonce'], 'input') )
		{
			return $user_id;
		}
		
		
		// $post_id to save against
		$post_id = 'user_' . $user_id;
		
		
		// set post_lock
		$GLOBALS['acf_save_lock'] = $post_id;
				
		
		// update the post
		do_action('acf/save_post', $post_id);
		
		
		// set post_lock and allow saves
		$GLOBALS['acf_save_lock'] = false;		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	save_attachment
	*
	*	@author Elliot Condon
	*	@since 3.1.8
	* 
	*-------------------------------------------------------------------------------------*/
	
	function save_attachment( $post, $attachment )
	{
		// verify nonce
		if( !isset($_POST['acf_nonce']) || !wp_verify_nonce($_POST['acf_nonce'], 'input') )
		{
			return $post;
		}
		
		
		// $post_id to save against
		$post_id = $post['ID'];
		
		
		// set post_lock
		$GLOBALS['acf_save_lock'] = $post_id;
				
		
		// update the post
		do_action('acf/save_post', $post_id);
		
		
		// set post_lock and allow saves
		$GLOBALS['acf_save_lock'] = false;
		
		
		return $post;
	}
	
	
	/*
	*  shopp_category_saved
	*
	*  @description: 
	*  @since 3.5.2
	*  @created: 27/11/12
	*/
	
	function shopp_category_saved( $category )
	{
		// verify nonce
		if( !isset($_POST['acf_nonce']) || !wp_verify_nonce($_POST['acf_nonce'], 'input') )
		{
			return $category;
		}
		
		
		// $post_id to save against
		$post_id = 'shopp_category_' . $category->id;
		
		
		// set post_lock
		$GLOBALS['acf_save_lock'] = $post_id;
				
		
		// update the post
		do_action('acf/save_post', $post_id);
		
		
		// set post_lock and allow saves
		$GLOBALS['acf_save_lock'] = false;
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	acf_everything_fields
	*
	*	@description		Ajax call that renders the html needed for the page
	*	@author 			Elliot Condon
	*	@since 				3.1.8
	* 
	*-------------------------------------------------------------------------------------*/
	
	function acf_everything_fields()
	{
		// defaults
		$defaults = array(
			'metabox_ids' => '',
			'page_type' => '',
			'page_action' => '',
			'option_name' => '',
		);
		
		
		// load post options
		$options = array_merge($defaults, $_POST);
		
		
		// metabox ids is a string with commas
		$options['metabox_ids'] = explode( ',', $options['metabox_ids'] );
		
			
		// get acfs
		$acfs = apply_filters('acf/get_field_groups', false);
			
		
		// layout
		$layout = 'tr';	
		if( $options['page_type'] == "taxonomy" && $options['page_action'] == "add")
		{
			$layout = 'div';
		}
		if( $options['page_type'] == "shopp_category")
		{
			$layout = 'metabox';
		}
		
		
		if( $acfs )
		{
			foreach( $acfs as $acf )
			{
				// load options
				$acf['options'] = apply_filters('acf/field_group/get_options', array(), $acf['id']);
				
				
				// only add the chosen field groups
				if( !in_array( $acf['id'], $options['metabox_ids'] ) )
				{
					continue;
				}


				// title 
				if( $options['page_action'] == "edit" && $options['page_type'] == 'user' )
				{
					echo '<h3>' .$acf['title'] . '</h3><table class="form-table"><tbody>';
				}
				
				
				// wrapper
				if( $layout == 'tr' )
				{
					//nonce
					echo '<tr><td colspan="2"><input type="hidden" name="acf_nonce" value="' . wp_create_nonce( 'input' ) . '" /></td></tr>';
				}
				else
				{
					//nonce
					echo '<input type="hidden" name="acf_nonce" value="' . wp_create_nonce( 'input' ) . '" />';
				}
				
				if( $layout == 'metabox' )
				{
					echo '<div class="postbox acf_postbox" id="acf_'. $acf['id'] .'">';
					echo '<div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle"><span>' . $acf['title'] . '</span></h3>';
					echo '<div class="inside">';
				}
				
				
				// load fields
				$fields = apply_filters('acf/field_group/get_fields', array(), $acf['id']);
				
				
				if( is_array($fields) ){ foreach( $fields as $field ){
			
					// if they didn't select a type, skip this field
					if( !$field['type'] || $field['type'] == 'null' ) continue;
					
					
					// set value
					if( !isset($field['value']) )
					{
						$field['value'] = apply_filters('acf/load_value', false, $options['option_name'], $field);
						$field['value'] = apply_filters('acf/format_value', $field['value'], $options['option_name'], $field);
					}
					
					
					// required
					$required_class = "";
					$required_label = "";
					
					if( $field['required'] )
					{
						$required_class = ' required';
						$required_label = ' <span class="required">*</span>';
					}
					
					
					if( $layout == 'metabox' )
					{
						echo '<div id="acf-' . $field['name'] . '" class="field field_type-' . $field['type'] . ' field_key-' . $field['key'] . $required_class . '" data-field_name="' . $field['name'] . '" data-field_key="' . $field['key'] . '" data-field_type="' . $field['type'] . '">';
		
							echo '<p class="label">';
								echo '<label for="fields[' . $field['key'] . ']">' . $field['label'] . $required_label . '</label>';
								echo $field['instructions'];
							echo '</p>';
							
							$field['name'] = 'fields[' . $field['key'] . ']';
							do_action('acf/create_field', $field);
						
						echo '</div>';
					}
					elseif( $layout == 'div' )
					{
						echo '<div id="acf-' . $field['name'] . '" class="form-field field field_type-' . $field['type'] . ' field_key-' . $field['key'] . $required_class . '" data-field_name="' . $field['name'] . '" data-field_key="' . $field['key'] . '" data-field_type="' . $field['type'] . '">';
						
							echo '<label for="fields[' . $field['key'] . ']">' . $field['label'] . $required_label . '</label>';	
							$field['name'] = 'fields[' . $field['key'] . ']';
							do_action('acf/create_field', $field );
							if($field['instructions']) echo '<p class="description">' . $field['instructions'] . '</p>';
							
						echo '</div>';
					}
					else
					{
						echo '<tr id="acf-' . $field['name'] . '" class="form-field field field_type-' . $field['type'] . ' field_key-'.$field['key'] . $required_class . '" data-field_name="' . $field['name'] . '" data-field_key="' . $field['key'] . '" data-field_type="' . $field['type'] . '">';
							echo '<th valign="top" scope="row"><label for="fields[' . $field['key'] . ']">' . $field['label'] . $required_label . '</label></th>';	
							echo '<td>';
								$field['name'] = 'fields[' . $field['key'] . ']';
								do_action('acf/create_field', $field );
								
								if($field['instructions']) echo '<p class="description">' . $field['instructions'] . '</p>';
							echo '</td>';
						echo '</tr>';

					}
					
				}}
				
							
				
				// wrapper
				if( $layout == 'metabox' )
				{
					echo '</div></div>';
				}
				
				
				// title 
				if( $options['page_action'] == "edit" && $options['page_type'] == 'user' )
				{
					echo '</tbody></table>';
				}
				
				
			}
			// foreach($acfs as $acf)
		}
		// if($acfs)
		
		// exit for ajax
		die();

	}
	
	
	/*
	*  delete_term
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 12/01/13
	*/
	
	function delete_term( $term, $tt_id, $taxonomy, $deleted_term )
	{
		global $wpdb;
		
		$values = $wpdb->query($wpdb->prepare(
			"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
			'%' . $taxonomy . '_' . $term . '%'
		));
	}
	
			
}

new acf_everything_fields();

?>