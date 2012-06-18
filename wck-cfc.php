<?php
/*
Plugin Name: WCK Custom Fields Creator
Description: Creates Custom Meta Box Fields for WordPress. It supports repeater fields and uses AJAX to handle data.
Author: Reflection Media, Madalin Ungureanu
Version: 1.0.2
Author URI: http://www.reflectionmedia.ro

License: GPL2

== Copyright ==
Copyright 2011 Reflection Media (wwww.reflectionmedia.ro)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
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
		'name' => _x( 'WCK Custom Meta Boxes', 'post type general name'),
		'singular_name' => _x( 'Custom Meta Box', 'post type singular name'),
		'add_new' => _x( 'Add New', 'Custom Meta Box' ),
		'add_new_item' => __( "Add New Meta Box" ),
		'edit_item' => __( "Edit Meta Box" ) ,
		'new_item' => __( "New Meta Box" ),
		'all_items' => __( "Custom Fields Creator" ),
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
		'publicly_queryable' => false,
		'show_ui' => true, 	
		'show_in_menu' => 'wck-page', 				
		'has_archive' => false,
		'hierarchical' => false,									
		'capability_type' => 'post',
		'supports' => array( 'title' )	
	);			
			
	register_post_type( 'wck-meta-box', $args );		
}
/* Remove view action from post list view */
add_filter('post_row_actions','wck_cfc_remove_view_action');
function wck_cfc_remove_view_action($actions){
	global $post;
   if ($post->post_type =="wck-meta-box"){	
	   unset( $actions['view'] );	  
   }
   return $actions;
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
		if ( $post_type->name != 'attachment' && $post_type->name != 'wck-meta-box' ) 
			$post_type_names[] = $post_type->name;
	}
	
	/* get page templates */	
	$templates = wck_get_page_templates();	
	
	/* set up the fields array */
	$cfc_box_args_fields = array( 		
		array( 'type' => 'text', 'title' => 'Meta name', 'description' => 'The name of the meta field. It is the name by which you will query the data in the frontend. Must be unique, only lowercase letters, no spaces and no special characters.', 'required' => true ),		
		array( 'type' => 'select', 'title' => 'Post Type', 'options' => $post_type_names, 'default-option' => true, 'description' => 'What post type the meta box should be attached to', 'required' => true ),		
		array( 'type' => 'select', 'title' => 'Repeater', 'options' => array( 'false', 'true' ), 'default' => 'false', 'description' => 'Whether the box supports just one entry or if it is a repeater field. By default it is a single field.' ),
		array( 'type' => 'select', 'title' => 'Sortable', 'options' => array( 'true', 'false' ), 'default' => 'false', 'description' => 'Whether the entries are sortable or not. Thsi is valid for repeater fields.' ),
		array( 'type' => 'text', 'title' => 'Post ID', 'description' => 'ID of a post on which the meta box should appear.' )			
	);
	
	if( !empty( $templates ) )
		$cfc_box_args_fields[] = array( 'type' => 'select', 'title' => 'Page Template', 'options' => $templates, 'default-option' => true, 'description' => 'If post type is "page" you can further select a page templete. The meta box will only appear  on the page that has that selected page template.' );
	
	/* set up the box arguments */
	$args = array(
		'metabox_id' => 'wck-cfc-args',
		'metabox_title' => 'Meta Box Arguments',
		'post_type' => 'wck-meta-box',
		'meta_name' => 'wck_cfc_args',
		'meta_array' => $cfc_box_args_fields,			
		'sortable' => false,
		'single' => true
	);

	/* create the box */
	new WCK_CFC_Wordpress_Creation_Kit( $args );
	
	
	/* set up the fields array */
	$cfc_box_fields_fields = array( 
		array( 'type' => 'text', 'title' => 'Field Title', 'description' => 'Title of the field. A slug will automatically be generated.', 'required' => true ),
		array( 'type' => 'select', 'title' => 'Field Type', 'options' => array( 'text', 'textarea', 'select', 'checkbox', 'radio', 'upload' ), 'default-option' => true, 'description' => 'The field type', 'required' => true ),
		array( 'type' => 'textarea', 'title' => 'Description', 'description' => 'The description of the field.' ),				
		array( 'type' => 'select', 'title' => 'Required', 'options' => array( 'false', 'true' ), 'default' => 'false', 'description' => 'Whether the field is required or not' ),
		array( 'type' => 'text', 'title' => 'Default Value', 'description' => 'Default value of the field. For Checkboxes if there are multiple values separete them with a ","' ),
		array( 'type' => 'text', 'title' => 'Options', 'description' => 'Options for field types "select", "checkbox" and "radio". For multiple options separete them with a ","' ),
		array( 'type' => 'radio', 'title' => 'Attach upload to post', 'description' => 'Whether or not the uploads should be attached to the post', 'options' => array( 'yes', 'no' ) )
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

/* advanced label options container for update form */
add_action( "wck_before_add_form_wck_cfc_args_element_0", 'wck_cfc_description_for_args_box' );
function wck_cfc_description_for_args_box(){
	echo '<div class="cfc-message"><p>Enter below the arguments for the meta box.</p></div>';	
}


/* advanced label options container for update form */
add_filter( "wck_before_update_form_wck_cfc_fields_element_1", 'wck_cfc_update_form_get_field_value', 10, 3 );
function wck_cfc_update_form_get_field_value( $form, $i, $value ){
	$GLOBALS['wck_cfc_update_field_type'] = $value;
	return $form;
}

add_filter( "wck_before_update_form_wck_cfc_fields_element_5", 'wck_cfc_update_form_option_wrapper_start', 10, 3 );
function wck_cfc_update_form_option_wrapper_start( $form, $i, $value ){
	if( !in_array( $GLOBALS['wck_cfc_update_field_type'], array( 'select', 'checkbox', 'radio' ) ) )
		$form .= '<div class="hide-options" style="display:none;">';
	return $form;
}

add_filter( "wck_after_update_form_wck_cfc_fields_element_5", 'wck_cfc_update_form_option_wrapper_end', 10, 3 );
function wck_cfc_update_form_option_wrapper_end( $form, $i, $value ){
	if( !in_array( $GLOBALS['wck_cfc_update_field_type'], array( 'select', 'checkbox', 'radio' ) ) )
		$form .= '</div>';
	return $form;
}

/* attach to post show or hide based on field typr */
add_filter( "wck_before_update_form_wck_cfc_fields_element_6", 'wck_cfc_update_form_attach_wrapper_start', 10, 3 );
function wck_cfc_update_form_attach_wrapper_start( $form, $i, $value ){
	if( $GLOBALS['wck_cfc_update_field_type'] != 'upload' )
		$form .= '<div class="hide-attach" style="display:none;">';
	return $form;
}

add_filter( "wck_after_update_form_wck_cfc_fields_element_6", 'wck_cfc_update_form_attach_wrapper_end', 10, 3 );
function wck_cfc_update_form_attach_wrapper_end( $form, $i, $value ){
	if( $GLOBALS['wck_cfc_update_field_type'] != 'upload' )
		$form .= '</div>';
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

/* Show or hide attach field in list view */
add_filter( "wck_before_listed_wck_cfc_fields_element_6", 'wck_cfc_display_label_wrapper_attach_start', 10, 3 );
function wck_cfc_display_label_wrapper_attach_start( $form, $i, $value ){
	if( $GLOBALS['wck_cfc_field_type'] != 'upload' )
		$form .= '<div style="display:none;">';
	return $form;
}

add_filter( "wck_after_listed_wck_cfc_fields_element_6", 'wck_cfc_display_label_wrapper_attach_end', 10, 3 );
function wck_cfc_display_label_wrapper_attach_end( $form, $i, $value ){
	if( $GLOBALS['wck_cfc_field_type'] != 'upload' )
		$form .= '</div>';
	return $form;
}

/* Show the slug for field title */
add_filter( "wck_after_listed_wck_cfc_fields_element_0", 'wck_cfc_display_field_title_slug', 10, 3 );
function wck_cfc_display_field_title_slug( $form, $i, $value ){	
		$form .= '<li class="slug-title"><em>Slug:</em><span>'. sanitize_title_with_dashes( remove_accents( $value ) ) .'</span> (Note:changing the slug when you already have a lot of existing entries may result in unexppected bahaviour.) </li>';
	return $form;
}



/* add refresh to page */
add_action("wck_refresh_list_wck_cfc", "wck_cfc_after_refresh_list");
function wck_cfc_after_refresh_list(){
	echo '<script type="text/javascript">window.location="'. get_admin_url() . 'admin.php?page=cfc-page&updated=true' .'";</script>';
}

/* hook to create custom meta boxes */
add_action( 'admin_init', 'wck_cfc_create_boxes' );

function wck_cfc_create_boxes(){
	$args = array(
		'post_type' => 'wck-meta-box',
		'numberposts' => -1
	);
	
	$all_meta_boxes = get_posts( $args );
	
	foreach( $all_meta_boxes as $meta_box ){
		$wck_cfc_args = get_post_meta( $meta_box->ID, 'wck_cfc_args', true );
		$wck_cfc_fields = get_post_meta( $meta_box->ID, 'wck_cfc_fields', true );
		
		$box_title = get_the_title( $meta_box->ID );
		/* treat case where the post has no title */
		if( empty( $box_title ) )
			$box_title = '(no title)';
		
		$fields_array = array();
		if( !empty( $wck_cfc_fields ) ){
			foreach( $wck_cfc_fields as $wck_cfc_field ){
				$fields_inner_array = array( 'type' => $wck_cfc_field['field-type'], 'title' => $wck_cfc_field['field-title'] ); 
				if( !empty( $wck_cfc_field['description'] ) )
					$fields_inner_array['description'] = $wck_cfc_field['description']; 
				if( !empty( $wck_cfc_field['required'] ) )
					$fields_inner_array['required'] = $wck_cfc_field['required'] == 'false' ? false : true;
				if( !empty( $wck_cfc_field['default-value'] ) )
					$fields_inner_array['default'] = $wck_cfc_field['default-value'];
				if( !empty( $wck_cfc_field['options'] ) ){
					$fields_inner_array['options'] = explode( ',', $wck_cfc_field['options'] );
					
					foreach( $fields_inner_array['options'] as  $key => $value ){
						$fields_inner_array['options'][$key] = trim( $value );
					}					
					
				}
				if( !empty( $wck_cfc_field['attach-upload-to-post'] ) )
					$fields_inner_array['attach_to_post'] = $wck_cfc_field['attach-upload-to-post'] == 'yes' ? true : false;
					
				$fields_array[] = $fields_inner_array;
			}
		}
		
		if( !empty( $wck_cfc_args ) ){
			foreach( $wck_cfc_args as $wck_cfc_arg ){
			
				/* metabox_id must be different from meta_name */
				$metabox_id = sanitize_title_with_dashes( remove_accents ( $box_title ) );				
				if( $wck_cfc_arg['meta-name'] == $metabox_id )
					$metabox_id = 'wck-'. $metabox_id;
				
				$box_args = array(
								'metabox_id' => $metabox_id,
								'metabox_title' => $box_title,
								'post_type' => $wck_cfc_arg['post-type'],
								'meta_name' => $wck_cfc_arg['meta-name'],
								'meta_array' => $fields_array
							);
				if( !empty( $wck_cfc_arg['sortable'] ) )
					$box_args['sortable'] = $wck_cfc_arg['sortable'] == 'false' ? false : true;
				
				if( !empty( $wck_cfc_arg['repeater'] ) )					
					$box_args['single'] = $wck_cfc_arg['repeater'] == 'false' ? true : false;
				
				if( !empty( $wck_cfc_arg['post-id'] ) )
					$box_args['post_id'] = $wck_cfc_arg['post-id'];
					
				if( !empty( $wck_cfc_arg['page-template'] ) )
					$box_args['page_template'] = $wck_cfc_arg['page-template'];	

				/* create the box */
				new WCK_CFC_Wordpress_Creation_Kit( $box_args );
			}
		}
	}
}

/* Meta Name Verification */
add_filter( 'wck_required_test_wck_cfc_args_meta-name', 'wck_cfc_ceck_meta_name', 10, 3 );
function wck_cfc_ceck_meta_name( $bool, $value, $post_id ){
	global $wpdb;
	
	$wck_cfc_args = get_post_meta( $post_id, 'wck_cfc_args', true );
	
	if( empty( $wck_cfc_args ) ){		
		//this is the add case		
		$check_meta_existance = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(meta_key) FROM $wpdb->postmeta WHERE meta_key = '$value'" ) );		
	}
	else{
		//this is the update case
		if( $wck_cfc_args[0]['meta-name'] != $value ){
			$check_meta_existance = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(meta_key) FROM $wpdb->postmeta WHERE meta_key = '$value'" ) );
		}
		else 
			$check_meta_existance = false;
	}
	
	if( strpos( $value, ' ' ) === false )
		$contains_spaces = false;
	else 
		$contains_spaces = true;
	
	return ( $check_meta_existance || empty($value) || $contains_spaces );
}

add_filter( 'wck_required_message_wck_cfc_args_meta-name', 'wck_cfc_change_meta_message', 10, 2 );
function wck_cfc_change_meta_message( $message, $value ){
	if( empty( $value ) )
		return $message;
	else if( strpos( $value, ' ' ) !== false )
		return "Choose a different Meta Name as this one contains spaces\n";
	else
		return "Choose a different Meta Name as this one already exists\n";
}

/* Add the separete meta for post type, post id and page template */
add_action( 'wck_before_add_meta', 'wck_cfc_add_separate_meta', 10, 3 );
function wck_cfc_add_separate_meta( $meta, $id, $values ){	
	if( $meta == 'wck_cfc_args' ){		
		// Post Type
		if( !empty( $values['post-type'] ) ){
			update_post_meta( $id, 'wck_cfc_post_type_arg', $values['post-type'] );
		}
		
		// Post Id
		if( !empty( $values['post-id'] ) ){
			update_post_meta( $id, 'wck_cfc_post_id_arg', $values['post-id'] );
		}
		
		// Page Template
		if( !empty( $values['page-template'] ) ){
			update_post_meta( $id, 'wck_cfc_page_template_arg', $values['page-template'] );
		}
	}
}

/* Change meta_key in db if field changed and also update the separete meta for post type, post id and page template */
add_action( 'wck_before_update_meta', 'wck_cfc_change_meta_key', 10, 4 );
function wck_cfc_change_meta_key( $meta, $id, $values, $element_id ){
	global $wpdb;
	if( $meta == 'wck_cfc_args' ){
		$wck_cfc_args = get_post_meta( $id, 'wck_cfc_args', true );		
		
		if( $wck_cfc_args[0]['meta-name'] != $values['meta-name'] ){			
			$wpdb->update( 
				$wpdb->postmeta, 
				array( 'meta_key' => $values['meta-name'] ), 
				array( 'meta_key' => $wck_cfc_args[0]['meta-name'] )				
			);
		}
		
		// Post Type
		if( $wck_cfc_args[0]['post-type'] != $values['post-type'] ){
			update_post_meta( $id, 'wck_cfc_post_type_arg', $values['post-type'] );
		}
		
		// Post Id
		if( $wck_cfc_args[0]['post-id'] != $values['post-id'] ){
			update_post_meta( $id, 'wck_cfc_post_id_arg', $values['post-id'] );
		}
		
		// Page Template
		if( $wck_cfc_args[0]['page-template'] != $values['page-template'] ){
			update_post_meta( $id, 'wck_cfc_page_template_arg', $values['page-template'] );
		}
	}
}

/* Change Field Title in db if field changed */
add_action( 'wck_before_update_meta', 'wck_cfc_change_field_title', 10, 4 );
function wck_cfc_change_field_title( $meta, $id, $values, $element_id ){
	global $wpdb;
	if( $meta == 'wck_cfc_fields' ){
		$wck_cfc_fields = get_post_meta( $id, 'wck_cfc_fields', true );
		
		if( $wck_cfc_fields[$element_id]['field-title'] != $values['field-title'] ){						
			
			$wck_cfc_args = get_post_meta( $id, 'wck_cfc_args', true );
			$meta_name = $wck_cfc_args[0]['meta-name'];
			$post_id_with_this_meta = $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '$meta_name'" ) );
			
			foreach( $post_id_with_this_meta as $post ){
				$results = get_post_meta( $post->post_id, $meta_name, true );
				foreach( $results as $key => $result ){			
					$results[$key][ sanitize_title_with_dashes( remove_accents( $values['field-title'] ) ) ] = $results[$key][ sanitize_title_with_dashes( remove_accents( $wck_cfc_fields[$element_id]['field-title'] ) ) ];
					unset( $results[$key][ sanitize_title_with_dashes( remove_accents( $wck_cfc_fields[$element_id]['field-title'] ) ) ] );
				}
				update_post_meta( $post->post_id, $meta_name, $results );
			}
		}
	}
}

/* Add Custom columns to listing */
add_filter("manage_wck-meta-box_posts_columns", "wck_cfc_edit_columns" );
function wck_cfc_edit_columns($columns){
	$columns['cfc-id'] = "Id";
	$columns['cfc-post-type'] = "Post Type"; 
	$columns['cfc-page-template'] = "Page Template"; 
	return $columns;
}

/* Register the column as sortable */
add_filter( 'manage_edit-wck-meta-box_sortable_columns', 'wck_cfc_register_sortable_columns' );
function wck_cfc_register_sortable_columns( $columns ) {
	$columns['cfc-id'] = 'cfc-id';
	$columns['cfc-post-type'] = 'cfc-post-type';
	$columns['cfc-page-template'] = 'cfc-page-template';
 
	return $columns;
}

/* Tell WordPress how to handle the sorting */
add_filter( 'request', 'wck_cfc_column_orderby' );
function wck_cfc_column_orderby( $vars ) {
	if ( isset( $vars['orderby'] ) && 'cfc-id' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => 'wck_cfc_post_id_arg',
			'orderby' => 'meta_value_num'
		) );
	}
	
	if ( isset( $vars['orderby'] ) && 'cfc-post-type' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => 'wck_cfc_post_type_arg',
			'orderby' => 'meta_value'
		) );
	}
	
	if ( isset( $vars['orderby'] ) && 'cfc-page-template' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => 'wck_cfc_page_template_arg',
			'orderby' => 'meta_value'
		) );
	}
 
	return $vars;
}

/* Let's set up what to display in the columns */
add_action("manage_wck-meta-box_posts_custom_column",  "wck_cfc_custom_columns", 10, 2);
function wck_cfc_custom_columns( $column_name, $post_id ){
	if( $column_name == 'cfc-id' ){
		$post_id_arg = get_post_meta( $post_id, 'wck_cfc_post_id_arg', true );
		echo $post_id_arg;
	}
	
	if( $column_name == 'cfc-post-type' ){
		$post_type_arg = get_post_meta( $post_id, 'wck_cfc_post_type_arg', true );
		echo $post_type_arg;
	}
	
	if( $column_name == 'cfc-page-template' ){
		$page_template_arg = get_post_meta( $post_id, 'wck_cfc_page_template_arg', true );
		echo $page_template_arg;
	}	
}

/* Add side metaboxes */
add_action('add_meta_boxes', 'wck_cfc_add_side_boxes' );
function wck_cfc_add_side_boxes(){
	add_meta_box( 'wck-cfc-side', 'Wordpress Creation Kit', 'wck_cfc_side_box_one', 'wck-meta-box', 'side', 'low' );
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
add_action('current_screen', 'wck_cfc_help');

function wck_cfc_help () {    
    $screen = get_current_screen();	
    /*
     * Check if current screen is wck_page_cptc-page
     * Don't add help tab if it's not
     */
    if ( $screen->id != 'wck-meta-box' )
        return;

    // Add help tabs
    $screen->add_help_tab( array(
        'id'	=> 'wck_cfc_overview',
        'title'	=> __('Overview'),
        'content'	=> '<p>' . __( 'WCK Custom Fields Creator allows you to easily create custom meta boxes for Wordpress without any programming knowledge.' ) . '</p>',
    ) );
	
	$screen->add_help_tab( array(
        'id'	=> 'wck_cfc_arguments',
        'title'	=> __('Meta Box Arguments'),
        'content'	=> '<p>' . __( 'Define here the rules for the meta box. This rules are used to set up where the meta box will appear, it\'s type and also the meta key name stored in the database. The name of the entry (Enter title here) will be used as the meta box title.' ) . '</p>',
    ) );
	
	$screen->add_help_tab( array(
        'id'	=> 'wck_cfc_fields',
        'title'	=> __('Meta Box Fields'),
        'content'	=> '<p>' . __( 'Define here the fields contained in the meta box. From "Field Title" a slug will be automatically generated and you will use this slug to display the data in the frontend.' ) . '</p>',
    ) );
	
	$screen->add_help_tab( array(
        'id'	=> 'wck_cfc_example',
        'title'	=> __('CFC Frontend Example'),
        'content'	=> '<p>' . __( 'Let\'s consider we have a meta box with the following arguments:<br /> - Meta name: books <br /> - Post Type: post <br />And we also have two fields deffined:<br /> - A text field with the Field Title: Book name <br /> - And another text field with the Field Title: Author name ' ) . '</p>' . '<p>' . __( 'You will notice that slugs will automatically be created for the two text fields. For "Book name" the slug will be "book-name" and for "Author name" the slug will be "author-name"' ) . '</p>' . '<p>' . __( 'Let\'s see what the code for displaying the meta box values in single.php of your theme would be:' ) . '</p>' . '<pre>' . '$books = get_post_meta( $post->ID, \'books\', true ); <br />foreach( $books as $book){<br />	echo $book[\'book-name\'];<br / >	echo $book[\'author-name\'];<br />}' . '</pre>' . '<p>' . __( 'So as you can see the Meta Name "books" is used as the $key parameter of the funtion <a href="http://codex.wordpress.org/Function_Reference/get_post_meta" target="_blank">get_post_meta()</a> and the slugs of the text fields are used as keys for the resulting array. Basically CFC stores the entries as post meta in a multidimensioanl array. In our case the array would be: <br /><pre>array( array( "book-name" => "The Hitchhiker\'s Guide To The Galaxy", "author-name" => "Douglas Adams" ),  array( "book-name" => "Ender\'s Game", "author-name" => "Orson Scott Card" ) );</pre> This is true even for single entries.' ) . '</p>'
    ) );
}

/**
 * Get the Page Templates available in the current theme
 *
 * Based on wordpress get_page_templates()
 *
 * @return array Key is the template name, value is the filename of the template
 */
function wck_get_page_templates() {
	$themes = get_themes();
	$theme = get_current_theme();
	$templates = $themes[$theme]['Template Files'];
	$page_templates = array();

	if ( is_array( $templates ) ) {
		$base = array( trailingslashit(get_template_directory()), trailingslashit(get_stylesheet_directory()) );

		foreach ( $templates as $template ) {
			$basename = str_replace($base, '', $template);

			// don't allow template files in subdirectories
			if ( false !== strpos($basename, '/') )
				continue;

			if ( 'functions.php' == $basename )
				continue;

			$template_data = implode( '', file( $template ));

			$name = '';
			if ( preg_match( '|Template Name:(.*)$|mi', $template_data, $name ) )
				$name = _cleanup_header_comment($name[1]);

			if ( !empty( $name ) ) {
				$page_templates[trim( $name )] = $basename;
			}
		}
	}

	return $page_templates;
}
?>