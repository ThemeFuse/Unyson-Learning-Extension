<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @param int $post_id
 *
 * @return bool
 */
function fw_ext_learning_quiz_has_quiz( $post_id = null ) {
	return fw_ext('learning-quiz')->has_quiz( $post_id );
}

/**
 * @param int $post_id
 *
 * @return bool|WP_Post
 */
function fw_ext_learning_quiz_get_quiz( $post_id ) {
	return fw_ext( 'learning-quiz' )->get_lesson_quiz( $post_id );
}

/**
 * @param int $post_id
 *
 * @return bool|string
 */
function fw_ext_learning_quiz_get_quiz_permalink( $post_id ) {
	return fw_ext('learning-quiz')->get_permalink( $post_id );
}

/**
 * @param null|int $id
 *
 * @return array|null
 */
function fw_ext_learning_quiz_get_response( $id = null ) {
	if ( is_null( $id ) ) {
		global $post;
	} else {
		$post = get_post( $id );
	}

	if ( empty( $post ) || ! fw()->extensions->get( 'learning-quiz' )->is_quiz( $post->ID ) ) {
		return null;
	}

	$response = FW_Session::get( 'learning-quiz-form-process-response' );

	return $response;
}