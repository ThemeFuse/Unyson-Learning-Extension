<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

function _action_theme_fw_ext_learning_lesson_on_passed_to_next_lesson( $lesson_id ) {

	/**
	 * @var FW_Extension_learning $learning
	 */
	$learning = fw()->extensions->get( 'learning' );

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

add_action( 'fw_ext_learning_student_completed_lesson', '_action_theme_fw_ext_learning_lesson_on_passed_to_next_lesson',
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

add_action( 'fw_ext_learning_student_completed_course', 'fw_ext_learning_student_took_course_redirect', 9999 );

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

function _action_fw_ext_learning_student_redirect_from_lesson_page() {
	global $post;

	/**
	 * @var FW_Extension_Learning $learning
	 */
	$learning = fw_ext( 'learning' );

	/**
	 * @var FW_Extension_Learning_Student $student
	 */
	$student = fw_ext( 'learning-student' );

	if ( ! $learning->is_lesson() || $student->is_author( $post->ID ) ) {
		return;
	}

	$course = $post->post_parent;

	if ( $learning->is_course( $course ) && ! $student->is_subscribed( $course ) ) {
		FW_Flash_Messages::add( 'learning-student-redirect', __( 'Please apply to this course', 'fw' ) );
		wp_redirect( get_permalink( $course ) );
		exit;
	}

	if ( $student->get_config( 'lessons-in-order' ) == false ) {
		return;
	}

	$previous = $learning->get_previous_lesson();
	if ( $previous === false || $previous == null ) {
		return;
	}

	if ( $student->has_passed( $previous->ID ) ) {
		FW_Flash_Messages::add(
			'learning-student-redirect',
			__( 'You have to pass previous lesson in order to apply to the current', 'fw' )
		);
		wp_redirect( get_permalink( $previous->ID ) );
		exit;
	}
}

//add_action( 'wp', '_action_fw_ext_learning_student_redirect_from_lesson_page', 10 );

function _action_fw_ext_learning_student_quiz_access() {

	/**
	 * @var FW_Extension_Learning_Quiz $quiz
	 */
	$quiz = fw()->extensions->get( 'learning-quiz' );

	if ( empty( $quiz ) ) {
		return;
	}

	/**
	 * @var FW_Extension_Learning_Student $student
	 */
	$student = fw()->extensions->get( 'learning-student' );

	if ( ! $quiz->is_quiz() ) {
		return;
	}

	/**
	 * @var FW_Extension_Learning $learning
	 */
	$learning = fw()->extensions->get( 'learning' );

	global $post;

	if ( ! $learning->is_lesson( $post->post_parent ) ) {
		wp_redirect( home_url() );
		exit;
	}

	if ( ! $student->is_student() ) {
		wp_redirect( get_permalink( $post->post_parent ) );
		exit;
	}

	if ( $student->is_student() && $student->is_author( $post->post_parent ) ) {
		return;
	}

	$previous = $learning->get_previous_lesson( $post->post_parent );

	if ( $previous === false ) {
		wp_redirect( home_url() );
		exit;
	}

	if ( $previous === null ) {
		return;
	}

	if ( $student->has_passed( $previous->ID ) ) {
		wp_redirect( get_permalink( $previous->ID ) );
		exit();
	}
}

add_action( 'wp', '_action_fw_ext_learning_student_quiz_access' );