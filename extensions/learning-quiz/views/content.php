<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
?>
<?php
/**
 * The Template for displaying all single quiz
 */

global $post;
/**
 * @var WP_Query $wp_query
 */
global $wp_query;
$lesson   = get_post( $post->post_parent );
$response = fw_ext_learning_quiz_get_response();
$pass_mark = (int) fw_get_db_post_option( $lesson->ID, 'learning-quiz-passmark' );
$text      = '';

if ( $pass_mark > 0 ) {
	$text = sprintf( __( 'You require %d points in oder to pass the test', 'fw' ), $pass_mark );
}

if ( ! empty( $response ) ) {
	if ( (int) $response['minimum-pass-mark'] > 0 ) {
		if ( (int) $response['accumulated'] < (int) $response['minimum-pass-mark'] ) {
			$text = __( 'Sorry, you did not pass the test', 'fw' );
		} else {
			$text = __( 'Congratulation, you passed the test', 'fw' );
		}
	} else {
		$correct = 0;
		foreach ( $response['questions'] as $question ) {
			/**
			 * @var FW_Quiz_Question_Process_Response $question
			 */
			if ( $question->get_max_percentage() == $question->get_current_percentage() ) {
				$correct++;
			}
		}

		$text = sprintf(
			__( 'You answered correctly %s questions from %s', 'fw' ),
			$correct,
			count($response['questions'])
		);
	}
}
?>

<?php if ( ! empty( $text ) ) : ?>
	<h4><?php echo $text; ?></h4>
<?php endif ?>
<?php if ( empty( $response ) ) : ?>
	<hr/>
	<?php
	/**
	 * @var FW_Extension_Learning_Quiz $learning_quiz
	 */
	$learning_quiz = fw()->extensions->get( 'learning-quiz' );
	echo $learning_quiz->render_quiz( $post->ID ); ?>
<?php endif ?>
<?php
if ( $post->post_parent == 0 ) {
	return;
}
?>
<hr/>
<h4><?php _e( 'Back to', 'fw' ); ?>:
	<a href="<?php echo get_permalink( $post->post_parent ) ?>"><?php echo get_the_title( $post->post_parent ) ?></a>
</h4>