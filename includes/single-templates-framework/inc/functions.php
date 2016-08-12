<?php

// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	exit;
}

/**
 * "st" in function names stands for "single template", that's what the functions are related to
 */
function dslc_st_init() {

	$capability = dslc_get_option( 'lc_min_capability_page', 'dslc_plugin_options_access_control' );
	if ( ! $capability ) $capability = 'publish_posts';

	register_post_type( 'dslc_templates', array(
		'menu_icon' => 'dashicons-admin-page',
		'labels' => array(
			'name' => __( 'Templates', 'live-composer-page-builder' ),
			'menu_name' => __( 'Templates', 'live-composer-page-builder' ),
			'singular_name' => __( 'Template', 'live-composer-page-builder' ),
			'add_new' => __( 'Add Template', 'live-composer-page-builder' ),
			'add_new_item' => __( 'Add Template', 'live-composer-page-builder' ),
			'edit' => __( 'Edit', 'live-composer-page-builder' ),
			'edit_item' => __( 'Edit Template', 'live-composer-page-builder' ),
			'new_item' => __( 'New Template', 'live-composer-page-builder' ),
			'view' => __( 'View Templates', 'live-composer-page-builder' ),
			'view_item' => __( 'View Template', 'live-composer-page-builder' ),
			'search_items' => __( 'Search Templates', 'live-composer-page-builder' ),
			'not_found' => __( 'No Templates found', 'live-composer-page-builder' ),
			'not_found_in_trash' => __( 'No Templates found in Trash', 'live-composer-page-builder' ),
			'parent' => __( 'Parent Template', 'live-composer-page-builder' ),
		),
		'public' => true,
		// 'exclude_from_search' => true, // 404 page is broken with this parameter.
		'publicly_queryable' => true,
		'supports' => array( 'title', 'custom-fields', 'thumbnail' ),
		'capabilities' => array(
			'publish_posts' => $capability,
			'edit_posts' => $capability,
			'edit_others_posts' => $capability,
			'delete_posts' => $capability,
			'delete_others_posts' => $capability,
			'read_private_posts' => $capability,
			'edit_post' => $capability,
			'delete_post' => $capability,
			'read_post' => $capability,
		),
		'show_in_menu' =>'themes.php',
	) );

	global $dslc_var_post_options;

	// Generate the choices.
	global $dslc_var_templates_pt;

	$pt_choices = array();
	$template_for = array();

	$template_for[] = array(
		'label' => __( 'Single Post Templates:', 'live-composer-page-builder' ),
		'description' => __( 'Design for a single blog post or custom post type entries', 'live-composer-page-builder' ),
		'value' => 'list-heading',
	);

	foreach ( $dslc_var_templates_pt as $pt_id => $pt_label ) {

		$template_for[] = array(
			'label' => $pt_label,
			'value' => $pt_id,
		);
	}

	$template_for[] = array(
		'label' => __( 'Archive Index Templates:', 'live-composer-page-builder' ),
		'description' => __( 'Design for posts listings like Category, Tag, Date or Custom Taxonomies', 'live-composer-page-builder' ),
		'value' => 'list-heading',
	);

	foreach ( $dslc_var_templates_pt as $pt_id => $pt_label ) {

		$template_for[] = array(
			'label' => $pt_label . __( ' (archive)', 'live-composer-page-builder' ),
			'value' => $pt_id . '_archive',
		);
	}

	$template_for[] = array(
		'label' => __( 'Special Page Templates:', 'live-composer-page-builder' ),
		'description' => __( 'Design a custom "Page Not Found" screen or search results page', 'live-composer-page-builder' ),
		'value' => 'list-heading',
	);

	$template_for[] = array(
		'label' => __( '404 Page', 'live-composer-page-builder' ),
		'value' => '404_page',
	);

	$template_for[] = array(
		'label' => __( 'Search Results', 'live-composer-page-builder' ),
		'value' => 'search_results',
	);

	$template_for[] = array(
		'label' => __( 'Author Archives', 'live-composer-page-builder' ),
		'value' => 'author',
	);

	$dslc_var_post_options['dslc-templates-opts'] = array(
		'title' => 'Template Options',
		'show_on' => 'dslc_templates',
		'options' => array(
			array(
				'label' => __( 'Use this template to output...', 'live-composer-page-builder' ),
				// 'descr' => __( '', 'live-composer-page-builder' ),
				'std' => '',
				'id' => 'dslc_template_for',
				'type' => 'checkbox',
				'choices' => $template_for,
			),
			array(
				'label' => __( 'Base', 'live-composer-page-builder' ),
				'descr' => __( 'If set to <strong>theme template</strong> the template will be appeneded to the regular single post template ( ex. If the theme shows thumbnail and title in it\'s template they will still be there ). If set to <strong>plugin template</strong> everything will be stripped and only the content from this template shown.', 'live-composer-page-builder' ),
				'std' => 'custom',
				'id' => 'dslc_template_base',
				'type' => 'select',
				'choices' => array(
					array(
						'label' => 'Plugin Template',
						'value' => 'custom',
					),
					array(
						'label' => 'Theme Template',
						'value' => 'theme',
					),
				),
			),
			array(
				'label' => __( 'Type', 'live-composer-page-builder' ),
				'std' => 'default',
				'descr' => __( '<strong>Default</strong> template will be used as the default for all the posts. <br><strong>Optional</strong> template is an additional template that you can set to specific posts.', 'live-composer-page-builder' ),
				'id' => 'dslc_template_type',
				'type' => 'radio',
				'choices' => array(
					array(
						'label' => 'Default',
						'value' => 'default',
					),
					array(
						'label' => 'Optional',
						'value' => 'regular',
					),
				),
			),
		),
	);

} add_action( 'init', 'dslc_st_init', 90 );

/**
 * Get the template ID of a specific post.
 *
 * @since 1.0
 */
function dslc_st_get_template_ID( $post_ID ) {

	// Get the template ID set for the post ( returns false if not set ).
	$template = get_post_meta( $post_ID, 'dslc_post_template', true );

	// If no template set, make it "default".
	if ( ! $template ) {
		$template = 'default';
	}

	// Default template supplied, find it and return the ID.
	if ( 'default' === $template ) {

		// Query for default template.
		$args = array(
			'post_type' => 'dslc_templates',
			'post_status' => 'publish',
			'posts_per_page' => 1,
			'meta_query' => array(
				array(
					'key' => 'dslc_template_for',
					'value' => get_post_type( $post_ID ),
					'compare' => '=',
				),
				array(
					'key' => 'dslc_template_type',
					'value' => 'default',
					'compare' => '=',
				),
			),
			'order' => 'DESC'
		);
		$tpls = get_posts( $args );

		// If default template found set the ID if not make it false.
		if ( $tpls )
			$template_ID = $tpls[0]->ID;
		else
			$template_ID = false;

	// Specific template supplied, return the ID.
	} elseif ( $template ) {

		$template_ID = $template;

	}

	// Return the template ID.
	return $template_ID;
}
