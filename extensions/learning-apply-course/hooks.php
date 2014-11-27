<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @param string $the_content
 *
 * @return string
 */
function _filter_fw_ext_learning_apply_course_the_content( $the_content ) {
	/**
	 * @var FW_Extension_Learning $learning
	 */
	$learning = fw()->extensions->get( 'learning' );

	if ( ! $learning->is_lesson() && ! $learning->is_course() ) {
		return $the_content;
	}

	if ( is_singular( $learning->get_course_post_type() ) && ! $learning->locate_view_path( 'single-course' ) ) {
		return $the_content . fw_ext_learning_student_get_take_course_method();
	}

	if ( is_singular( $learning->get_lesson_post_type() ) && ! $learning->locate_view_path( 'single-lesson' ) ) {
		return $the_content . fw_ext_learning_apply_course_get_lesson_pass_method();
	}

	return $the_content;
}

add_filter( 'the_content', '_filter_fw_ext_learning_apply_course_the_content' );