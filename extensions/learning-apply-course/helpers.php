<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Return the HTML view of the take course method
 *
 * If the $course_id is empty, will be used the $global $post
 *
 * @param null $course_id
 *
 * @return string
 */
function fw_ext_learning_student_get_take_course_method( $course_id = null ) {
	/**
	 * @var FW_Extension_Learning_Apply_Course $ext
	 */
	$ext = fw()->extensions->get( 'learning-apply-course' );

	if ( empty( $course_id ) ) {
		global $post;

		if ( ! empty( $post ) ) {
			$course_id = $post->ID;
		} else {
			return '';
		}
	}

	return $ext->get_take_course_method( $course_id );
}

/**
 * Return the HTML view of the lesson pass method
 *
 * If the $lesson_id is empty, will be used the $global $post
 *
 * @param null $lesson_id
 *
 * @return string
 */
function fw_ext_learning_apply_course_get_lesson_pass_method( $lesson_id = null ) {
	/**
	 * @var FW_Extension_Learning_Apply_Course $ext
	 */
	$ext = fw()->extensions->get( 'learning-apply-course' );

	if ( empty( $lesson_id ) ) {
		global $post;

		if ( ! empty( $post ) ) {
			$lesson_id = $post->ID;
		} else {
			return '';
		}
	}

	return $ext->get_lesson_pass_method( $lesson_id );
}