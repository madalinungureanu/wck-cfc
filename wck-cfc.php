<?php
/*
Plugin Name: WCK CFC
Description: Creates Custom Meta Box Fields
*/

/* include Custom Fields Creator API */
require_once('wordpress-creation-kit-api/wordpress-creation-kit.php');

/* Create the WCK Page */
$args = array(							
			'page_title' => 'Wordpress Creation Kit',
			'menu_title' => 'WCK',
			'capability' => 'edit_theme_options',
			'menu_slug' => 'wck-page',									
			'page_type' => 'menu_page',
			'position' => 30
		);
new WCK_CFC_WCK_Page_Creator( $args );

add_action('admin_menu', 'wck_cfc_remove_wck_submanu_page', 11);
function wck_cfc_remove_wck_submanu_page(){	
	remove_submenu_page( 'wck-page', 'wck-page' );
}

/* Add Scripts */
add_action('admin_enqueue_scripts', 'wck_cfc_print_scripts' );
function wck_cfc_print_scripts($hook){
	$post_type = $_GET['post_type'] ? $_GET['post_type'] : get_post_type( $_GET['post'] );
	if( 'wck-meta-box' == $post_type ){			
		wp_register_style('wck-cfc-css', plugins_url('/css/wck-cfc.css', __FILE__));
		wp_enqueue_style('wck-cfc-css');

		wp_register_script('wck-cfc-js', plugins_url('/js/wck-cfc.js', __FILE__), array( 'jquery' ), '1.0' );
		wp_enqueue_script('wck-cfc-js');
	}	
}

/* hook to create custom post types */
add_action( 'init', 'wck_cfc_create_custom_fields_cpt' );

function wck_cfc_create_custom_fields_cpt(){	
			
	$labels = array(
		'name' => _x( 'Custom Meta Boxes', 'post type general name'),
		'singular_name' => _x( 'Custom Meta Box', 'post type singular name'),
		'add_new' => _x( 'Add New', 'Custom Meta Box' ),
		'add_new_item' => __( "Add New Meta Box" ),
		'edit_item' => __( "Edit Meta Box" ) ,
		'new_item' => __( "New Meta Box" ),
		'all_items' => __( "Custim Fields Creator" ),
		'view_item' => __( "View Meta Box" ),
		'search_items' => __( "Search Meta Boxes" ),
		'not_found' =>  __( "No Meta Boxes found" ),
		'not_found_in_trash' => __( "No Meta Boxes found in Trash"), 
		'parent_item_colon' => '',
		'menu_name' => 'Custom Meta Boxes'
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true, 	
		'show_in_menu' => 'wck-page', 				
		'has_archive' => false,
		'hierarchical' => false,									
		'capability_type' => 'post',
		'supports' => array( 'title' )	
	);			
			
	register_post_type( 'wck-meta-box', $args );		
}

/* create the meta box */
add_action( 'init', 'wck_cfc_create_box', 11 );
function wck_cfc_create_box(){
	global $wpdb;
	
	/* get post types */
	$args = array(
			'public'   => true
		);
	$output = 'objects'; // or objects
	$post_types = get_post_types($args,$output);
	$post_type_names = array(); 
	foreach ($post_types  as $post_type ) {
		if ( $post_type->name != 'attachment' || $post_type->name != 'wck-meta-box' ) 
			$post_type_names[] = $post_type->name;
	}
	
	/* get page templates */
	$page_templates = array();
	$page_template_results = $wpdb->get_results( $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_wp_page_template'" ) );
	
	foreach( $page_template_results as $page_template_result ){
		if( $page_template_result->meta_value != 'default' )
			$page_templates[] = $page_template_result->meta_value;
	}
	
	/* set up the fields array */
	$cfc_box_args_fields = array( 
		array( 'type' => 'text', 'title' => 'Meta Box Title', 'description' => 'Title of the meta box', 'required' => true ), 
		array( 'type' => 'text', 'title' => 'Meta name', 'description' => 'the name of the meta field. only lowercase letters', 'required' => true ),		
		array( 'type' => 'select', 'title' => 'Post Type', 'options' => $post_type_names, 'default-option' => true, 'description' => 'What post type the meta box should be attached to', 'required' => true ),
		array( 'type' => 'select', 'title' => 'Sortable', 'options' => array( 'true', 'false' ), 'default' => 'true', 'description' => 'Whether the metabox is sortable or not' ),
		array( 'type' => 'text', 'title' => 'Post ID', 'description' => 'ID of a post on which the meta box should appear.' )			
	);
	
	if( !empty( $page_templates ) )
		$cfc_box_args_fields[] = array( 'type' => 'select', 'title' => 'Page Template', 'options' => $page_templates, 'default-option' => true, 'description' => 'If post type is "page" you can further select a page templete. The meta box will only appear  on the page that has that page template selected.' );
	
	/* set up the box arguments */
	$args = array(
		'metabox_id' => 'wck-cfc-args',
		'metabox_title' => 'Meta Box Arguments',
		'post_type' => 'wck-meta-box',
		'meta_name' => 'wck_cfc_args',
		'meta_array' => $cfc_box_args_fields,			
		'sortable' => false
	);

	/* create the box */
	new WCK_CFC_Wordpress_Creation_Kit( $args );
	
	
	/* set up the fields array */
	$cfc_box_fields_fields = array( 
		array( 'type' => 'text', 'title' => 'Field Title', 'description' => 'Title of the field', 'required' => true ),
		array( 'type' => 'select', 'title' => 'Field Type', 'options' => array( 'text', 'textarea', 'select', 'checkbox', 'radio', 'upload' ), 'default-option' => true, 'description' => 'The field type', 'required' => true ),
		array( 'type' => 'textarea', 'title' => 'Description', 'description' => 'The description of the field.' ),				
		array( 'type' => 'select', 'title' => 'Required', 'options' => array( 'false', 'true' ), 'default' => 'false', 'description' => 'Whether the field is required or not' ),
		array( 'type' => 'text', 'title' => 'Default Value', 'description' => 'Default value of the field. For Checkboxes if there are multiple values separete them with a ","' ),
		array( 'type' => 'text', 'title' => 'Options', 'description' => 'Options for field types "select", "checkbox" and "radio". For multiple options separete them with a ","' )
	);	
	
	
	/* set up the box arguments */
	$args = array(
		'metabox_id' => 'wck-cfc-fields',
		'metabox_title' => 'Meta Box Fields',
		'post_type' => 'wck-meta-box',
		'meta_name' => 'wck_cfc_fields',
		'meta_array' => $cfc_box_fields_fields
	);

	/* create the box */
	new WCK_CFC_Wordpress_Creation_Kit( $args );
}



/* Flush rewrite rules */
//add_action('init', 'cfc_flush_rules', 20);
function cfc_flush_rules(){
	if( isset( $_GET['page'] ) && $_GET['page'] == 'cptc-page' && isset( $_GET['updated'] ) && $_GET['updated'] == 'true' )
		flush_rewrite_rules( false  );
}

/* advanced labels container for add form */
//add_action( "wck_before_add_form_wck_cfc_element_7", 'wck_cfc_form_label_wrapper_start' );
function wck_cfc_form_label_wrapper_start(){
	echo '<li><a href="javascript:void(0)" onclick="jQuery(\'#cptc-advanced-label-options-container\').toggle(); if( jQuery(this).text() == \'Show Advanced Label Options\' ) jQuery(this).text(\'Hide Advanced Label Options\');  else if( jQuery(this).text() == \'Hide Advanced Label Options\' ) jQuery(this).text(\'Show Advanced Label Options\');">Show Advanced Label Options</a></li>';
	echo '<li id="cptc-advanced-label-options-container" style="display:none;"><ul>';
}

//add_action( "wck_after_add_form_wck_cfc_element_17", 'wck_cfc_form_label_wrapper_end' );
function wck_cfc_form_label_wrapper_end(){
	echo '</ul></li>';	
}

/* advanced options container for add form */
//add_action( "wck_before_add_form_wck_cfc_element_18", 'wck_cfc_form_wrapper_start' );
function wck_cfc_form_wrapper_start(){
	echo '<li><a href="javascript:void(0)" onclick="jQuery(\'#cptc-advanced-options-container\').toggle(); if( jQuery(this).text() == \'Show Advanced Options\' ) jQuery(this).text(\'Hide Advanced Options\');  else if( jQuery(this).text() == \'Hide Advanced Options\' ) jQuery(this).text(\'Show Advanced Options\');">Show Advanced Options</a></li>';
	echo '<li id="cptc-advanced-options-container" style="display:none;"><ul>';
}

//add_action( "wck_after_add_form_wck_cfc_element_25", 'wck_cfc_form_wrapper_end' );
function wck_cfc_form_wrapper_end(){
	echo '</ul></li>';	
}

/* advanced label options container for update form */
//add_filter( "wck_before_update_form_wck_cfc_element_7", 'wck_cfc_update_form_label_wrapper_start', 10, 2 );
function wck_cfc_update_form_label_wrapper_start( $form, $i ){
	$form .=  '<li><a href="javascript:void(0)" onclick="jQuery(\'#cptc-advanced-label-options-update-container-'.$i.'\').toggle(); if( jQuery(this).text() == \'Show Advanced Label Options\' ) jQuery(this).text(\'Hide Advanced Label Options\');  else if( jQuery(this).text() == \'Hide Advanced Label Options\' ) jQuery(this).text(\'Show Advanced Label Options\');">Show Advanced Label Options</a></li>';
	$form .= '<li id="cptc-advanced-label-options-update-container-'.$i.'" style="display:none;"><ul>';
	return $form;
}

//add_filter( "wck_after_update_form_wck_cfc_element_17", 'wck_cfc_update_form_label_wrapper_end', 10, 2 );
function wck_cfc_update_form_label_wrapper_end( $form, $i ){
	$form .=  '</ul></li>';
	return $form;
}

/* advanced options container for update form */
//add_filter( "wck_before_update_form_wck_cfc_element_18", 'wck_cfc_update_form_wrapper_start', 10, 2 );
function wck_cfc_update_form_wrapper_start( $form, $i ){
	$form .=  '<li><a href="javascript:void(0)" onclick="jQuery(\'#cptc-advanced-options-update-container-'.$i.'\').toggle(); if( jQuery(this).text() == \'Show Advanced Options\' ) jQuery(this).text(\'Hide Advanced Options\');  else if( jQuery(this).text() == \'Hide Advanced Options\' ) jQuery(this).text(\'Show Advanced Options\');">Show Advanced Options</a></li>';
	$form .= '<li id="cptc-advanced-options-update-container-'.$i.'" style="display:none;"><ul>';
	return $form;
}

//add_filter( "wck_after_update_form_wck_cfc_element_25", 'wck_cfc_update_form_wrapper_end', 10, 2 );
function wck_cfc_update_form_wrapper_end( $form, $i ){
	$form .=  '</ul></li>';	
	return $form;
}


/* display or show options based on the field type */
add_filter( "wck_before_listed_wck_cfc_fields_element_1", 'wck_cfc_display_label_wrapper_start', 10, 3 );
function wck_cfc_display_label_wrapper_start( $form, $i, $value ){
	$GLOBALS['wck_cfc_field_type'] = $value;
	return $form;
}

add_filter( "wck_before_listed_wck_cfc_fields_element_5", 'wck_cfc_display_label_wrapper_options_start', 10, 3 );
function wck_cfc_display_label_wrapper_options_start( $form, $i, $value ){
	if( !in_array( $GLOBALS['wck_cfc_field_type'], array( 'select', 'checkbox', 'radio' ) ) )
		$form .= '<div style="display:none;">';
	return $form;
}

add_filter( "wck_after_listed_wck_cfc_fields_element_5", 'wck_cfc_display_label_wrapper_options_end', 10, 3 );
function wck_cfc_display_label_wrapper_options_end( $form, $i, $value ){
	if( !in_array( $GLOBALS['wck_cfc_field_type'], array( 'select', 'checkbox', 'radio' ) ) )
		$form .= '</div>';
	return $form;
}

//add_filter( "wck_after_listed_wck_cfc_element_17", 'wck_cfc_display_label_wrapper_end', 10, 2 );
function wck_cfc_display_label_wrapper_end( $form, $i ){
	$form .=  '</ul></li>';	
	return $form;
}

/* advanced options container for display */
//add_filter( "wck_before_listed_wck_cfc_element_18", 'wck_cfc_display_adv_wrapper_start', 10, 2 );
function wck_cfc_display_adv_wrapper_start( $form, $i ){
	$form .=  '<li><a href="javascript:void(0)" onclick="jQuery(\'#cptc-advanced-options-display-container-'.$i.'\').toggle(); if( jQuery(this).text() == \'Show Advanced Options\' ) jQuery(this).text(\'Hide Advanced Options\');  else if( jQuery(this).text() == \'Hide Advanced Options\' ) jQuery(this).text(\'Show Advanced Options\');">Show Advanced Options</a></li>';
	$form .= '<li id="cptc-advanced-options-display-container-'.$i.'" style="display:none;"><ul>';
	return $form;
}

//add_filter( "wck_after_listed_wck_cfc_element_25", 'wck_cfc_display_adv_wrapper_end', 10, 2 );
function wck_cfc_display_adv_wrapper_end( $form, $i ){
	$form .=  '</ul></li>';	
	return $form;
}

/* add refresh to page */
add_action("wck_refresh_list_wck_cfc", "wck_cfc_after_refresh_list");
function wck_cfc_after_refresh_list(){
	echo '<script type="text/javascript">window.location="'. get_admin_url() . 'admin.php?page=cfc-page&updated=true' .'";</script>';
}

/* Add side metaboxes */
add_action('add_meta_boxes', 'wck_cfc_add_side_boxes' );
function wck_cfc_add_side_boxes(){
	add_meta_box( 'wck-cfc-side', 'Side Box', 'wck_cfc_side_box_one', 'wck-meta-box', 'side', 'low' );
}
function wck_cfc_side_box_one(){
	?>
		<iframe src="http://www.cozmoslabs.com/iframes/cozmoslabs_plugin_iframe.php?origin=<?php echo get_option('home'); ?>" width="260" id="wck-iframe"></iframe>
		<script type="text/javascript">			
			var onmessage = function(e) {
				if( e.origin == 'http://www.cozmoslabs.com' )
					jQuery('#wck-iframe').height(e.data);			
			}
			if(window.postMessage) {
				if(typeof window.addEventListener != 'undefined') {
					window.addEventListener('message', onmessage, false);
				}
				else if(typeof window.attachEvent != 'undefined') {
					window.attachEvent('onmessage', onmessage);
				}
			}			
		</script>
	<?php
}


/* Contextual Help */
//add_action('load-wck_page_cfc-page', 'wck_cfc_help');

function wck_cfc_help () {    
    $screen = get_current_screen();

    /*
     * Check if current screen is wck_page_cptc-page
     * Don't add help tab if it's not
     */
    if ( $screen->id != 'wck_page_cfc-page' )
        return;

    // Add help tabs
    $screen->add_help_tab( array(
        'id'	=> 'wck_cfc_overview',
        'title'	=> __('Overview'),
        'content'	=> '<p>' . __( 'WCK Custom Post Type Creator allows you to easily create custom post types for Wordpress without any programming knowledge.<br />Most of the common options for creating a post type are displayed by default while the advanced options and label are just one click away.' ) . '</p>',
    ) );
	
	$screen->add_help_tab( array(
        'id'	=> 'wck_cfc_labels',
        'title'	=> __('Labels'),
        'content'	=> '<p>' . __( 'For simplicity you are required to introduce only the Singular Label and Plural Label from wchich the rest of the labels will be formed.<br />For a more detailed control of the labels you just have to click the "Show Advanced Label Options" link and all the availabel labels will be displayed' ) . '</p>',
    ) );
	
	$screen->add_help_tab( array(
        'id'	=> 'wck_cfc_advanced',
        'title'	=> __('Advanced Options'),
        'content'	=> '<p>' . __( 'The Advanced Options are set to the most common defaults for custom post types. To display them click the "Show Advanced Options" link.' ) . '</p>',
    ) );
}
?>