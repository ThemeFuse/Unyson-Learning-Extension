<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

add_action( 'admin_enqueue_scripts', '_action_admin_fw_ext_learning_make_categories_menu_item_focused' );
add_filter( 'manage_edit-fw-learning-articles_columns', '_filter_admin_manage_lessons_columns_titles', 10, 1 );
add_action( 'manage_fw-learning-articles_posts_custom_column', '_filter_admin_manage_lessons_columns', 10, 2 );
add_action( 'template_include', '_filter_ext_learning_template_include', 10, 2 );

function _action_admin_fw_ext_learning_make_categories_menu_item_focused() {
	/**
	 * @var FW_Extension_Learning $learning
	 */
	$learning = fw()->extensions->get( 'learning' );

	if ( ! fw_current_screen_match( array(
		'only' => array(
			'base' => 'edit-tags',
			'id'   => 'edit-' . $learning->get_categories_taxonomy()
		)
	) )
	) {
		return;
	}

	$data = array(
		'selector' => '#menu-posts-' .
		              $learning->get_lesson_post_type() .
		              ' ul li a[href*="taxonomy=' . $learning->get_categories_taxonomy() . '"]',
		'clas'     => 'current'
	);

	wp_enqueue_script( $learning->get_name() . '-make-categories-focused-script',
		$learning->get_declared_URI() . '/static/js/admin-scripts.js', array( 'jquery' ),
		fw()->manifest->get_version() );

	wp_localize_script( $learning->get_name() . '-make-categories-focused-script',
		$learning->get_name() . '_make_categories_focused', $data );
}

/**
 * @param array $columns
 *
 * @return array
 */
function _filter_admin_manage_lessons_columns_titles( $columns ) {
	/**
	 * @var FW_Extension_Learning $learning
	 */
	$learning = fw()->extensions->get( 'learning' );

	unset( $columns['date'] );
	$columns[ $learning->get_name() . '-course' ] = __( 'Course', 'fw' );

	return $columns;
}

/**
 * @param string $column
 * @param int $ID
 */
function _filter_admin_manage_lessons_columns( $column, $ID ) {
	/**
	 * @var FW_Extension_Learning $learning
	 */
	$learning = fw()->extensions->get( 'learning' );

	if ( $column != $learning->get_name() . '-course' ) {
		return;
	}

	$post = get_post( $ID );
	if ( is_wp_error( $post ) || empty( $post ) || ( $post->post_parent == 0 ) ) {
		echo '&#8212;';
	} else {
		$parent = get_post( $post->post_parent );
		if ( is_wp_error( $parent ) || empty( $parent ) || ( $parent->post_status == 'trash' || $parent->post_status == 'auto-draft' ) ) {
			echo '&#8212;';
		} else {
			$permalink = get_permalink( $parent->ID );
			echo '<a href="' . $permalink . '">' . $parent->post_title . '</a>';
		}
	}
}

/**
 * @param string $the_content
 *
 * @return string
 */
function _filter_ext_learning_course_the_content( $the_content ) {
	/**
	 * @var FW_Extension_Learning $learning
	 */
	$learning = fw()->extensions->get( 'learning' );

	return fw_render_view( $learning->locate_view_path( 'content-course' ),
		array( 'the_content' => $the_content ) );
}

/**
 * @param string $the_content
 *
 * @return string
 */
function _filter_ext_learning_lesson_the_content( $the_content ) {
	/**
	 * @var FW_Extension_Learning $learning
	 */
	$learning = fw()->extensions->get( 'learning' );

	return fw_render_view( $learning->locate_view_path( 'content-lesson' ),
		array( 'the_content' => $the_content ) );
}

/**
 * Check is there are defined templates for the learning and loards them
 *
 * @param string $template
 *
 * @return string
 */
function _filter_ext_learning_template_include( $template ) {

	/**
	 * @var FW_Extension_Learning $learning
	 */
	$learning = fw()->extensions->get( 'learning' );

	if ( is_singular( $learning->get_course_post_type() ) ) {
		if ( $learning->locate_view_path( 'single-course' ) ) {
			return $learning->locate_view_path( 'single-course' );
		}

		add_filter( 'the_content', '_filter_ext_learning_course_the_content', 20 );
	} elseif ( is_singular( $learning->get_lesson_post_type() ) ) {
		if ( $learning->locate_view_path( 'single-lesson' ) ) {
			return $learning->locate_view_path( 'single-lesson' );
		}

		add_filter( 'the_content', '_filter_ext_learning_lesson_the_content', 20 );
	} else if ( is_tax( $learning->get_categories_taxonomy() ) && $learning->locate_view_path( 'taxonomy' ) ) {
		return $learning->locate_view_path( 'taxonomy' );
	} else if ( is_post_type_archive( $learning->get_course_post_type() ) && $learning->locate_view_path( 'taxonomy' ) ) {
		return $learning->locate_view_path( 'taxonomy' );
	}

	return $template;
}