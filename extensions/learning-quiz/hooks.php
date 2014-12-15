<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

function _action_fw_ext_learning_quiz_form_process_answer( $process_response ) {
	FW_Session::set( 'learning-quiz-form-process-response', $process_response );
}

add_action( 'fw_ext_learning_quiz_form_process', '_action_fw_ext_learning_quiz_form_process_answer' );

function _action_fw_ext_learning_quiz_remove_quiz_response_from_session() {
	FW_Session::del( 'learning-quiz-form-process-response' );
}

add_action( 'wp_footer', '_action_fw_ext_learning_quiz_remove_quiz_response_from_session' );

/**
 * @param string $the_content
 *
 * @return string
 */
function _filter_ext_learning_quiz_the_content( $the_content ) {
	/**
	 * @var FW_Extension_Learning_Quiz $quiz
	 */
	$quiz = fw()->extensions->get( 'learning-quiz' );
	global $post;

	if ( ! $quiz->is_quiz( $post->ID ) ) {
		return $the_content;
	}

	return $the_content . fw_render_view( $quiz->locate_view_path( 'content' ) );
}

/**
 * Check is there are defined templates for the learning and loads them
 *
 * @param string $template
 *
 * @return string
 */
function _filter_ext_learning_quiz_add_start_button( $template ) {

	/**
	 * @var FW_Extension_Learning_Quiz $quiz
	 */
	$quiz = fw()->extensions->get( 'learning-quiz' );

	if ( is_singular( $quiz->get_quiz_post_type() ) ) {
		if ( $quiz->locate_view_path( 'single' ) ) {
			return $quiz->locate_view_path( 'single' );
		}

		add_filter( 'the_content', '_filter_ext_learning_quiz_the_content', 10 );
	}

	return $template;
}

add_action( 'template_include', '_filter_ext_learning_quiz_add_start_button', 10, 2 );