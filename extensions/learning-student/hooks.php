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

	wp_redirect( get_permalink( $lesson_id ) );
	exit;
}

add_action( 'fw_ext_learning_lesson_taken', '_action_theme_fw_ext_learning_lesson_on_passed_to_next_lesson',
	9999 );

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

add_action( 'fw_ext_learning_completed_course', 'fw_ext_learning_student_took_course_redirect', 9999 );

function _action_fw_ext_learning_student_manage_user_location() {
	globaL $post;

	/**
	 * @var FW_Extension_Learning $learning
	 */
	$learning = fw_ext( 'learning' );

	/**
	 * @var FW_Extension_Learning_Student $student
	 */
	$student = fw_ext( 'learning-student' );

	/**
	 * @var FW_Extension_Learning_Quiz $quiz
	 */
	$quiz = fw_ext( 'learning-quiz' );

	if ( is_admin() || empty( $post ) || $student->is_author( $post->ID ) ) {
		return;
	}

	if ( is_singular( $learning->get_lesson_post_type() ) ) {
		$course = $learning->get_lesson_course( $post->ID );

		if ( ! empty( $course ) && ! $student->is_subscribed( $course->ID ) ) {
			wp_redirect( get_permalink($course->ID  ) );
			exit;
		}

		if ( ! $student->is_studying() && ! $learning->is_first_lesson() ) {
			wp_redirect( $learning->get_previous_lesson()->ID );
			exit;
		}

	} elseif ( !empty( $quiz ) && is_singular( $quiz->get_quiz_post_type() ) ) {
		if ( ! $student->is_studying( $post->post_parent ) && ! $student->is_author( $post->post_parent ) ) {
			wp_redirect( get_permalink( $post->post_parent ) );
		}
	}
}

add_action( 'wp', '_action_fw_ext_learning_student_manage_user_location' );