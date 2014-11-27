<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

function _action_theme_fw_ext_learning_lesson_on_passed_to_next_lesson( $lesson_id ) {

	/**
	 * @var FW_Extension_learning $learning
	 */
	$learning = fw()->extensions->get( 'learning' );

	if ( ! $learning->is_lesson( $lesson_id ) ) {
		return;
	}

	$next = $learning->get_next_lesson( $lesson_id );
	if ( $next === false ) {
		return;
	}

	if ( is_null( $next ) ) {
		$lesson = get_post( $lesson_id );

		if ( $learning->is_course( $lesson->post_parent ) ) {
			wp_redirect( get_permalink( $lesson->post_parent ) );
			exit;
		}
	}

	wp_redirect( get_permalink( $next->ID ) );
	exit;
}

add_action( 'fw_ext_learning_lesson_passed', '_action_theme_fw_ext_learning_lesson_on_passed_to_next_lesson', 9999 );

function fw_ext_learning_student_took_course_redirect( $course_id ) {
	/**
	 * @var FW_Extension_Learning $learning
	 */
	$learning = fw()->extensions->get( 'learning' );
	if ( ! $learning->is_course( $course_id ) ) {
		return;
	}

	wp_redirect( get_permalink( $course_id ) );
	exit;
}

add_action( 'fw_ext_learning_student_took_course', 'fw_ext_learning_student_took_course_redirect', 9999 );

/**
 * @param string $the_content
 *
 * @return string
 */
function _filter_ext_learning_student_course_the_content( $the_content ) {
	/**
	 * @var FW_Extension_Learning $learning
	 */
	$learning = fw()->extensions->get( 'learning' );

	if ( ! $learning->is_course() || ! $learning->get_config( 'user-require' ) ) {
		return $the_content;
	}

	return $the_content . fw_ext_learning_student_get_take_course_method();
}

/**
 * @param string $the_content
 *
 * @return string
 */
function _filter_ext_learning_student_lesson_the_content( $the_content ) {
	/**
	 * @var FW_Extension_Learning $learning
	 */
	$learning = fw()->extensions->get( 'learning' );

	if ( ! $learning->is_lesson() || ! $learning->get_config( 'user-require' ) ) {
		return $the_content;
	}

	return $the_content . fw_ext_learning_apply_course_get_lesson_pass_method();
}