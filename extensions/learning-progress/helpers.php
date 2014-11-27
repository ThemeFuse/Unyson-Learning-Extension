<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

function fw_ext_learning_progress_render_progress( $course_id = null ) {
	if ( empty( $course_id ) ) {
		global $post;
		$course_id = $post->ID;
	}

	/**
	 * @var FW_Extension_Learning_Progress
	 */
	$progress = fw()->extensions->get( 'learning-progress' );

	return $progress->render_progress( $course_id );
}