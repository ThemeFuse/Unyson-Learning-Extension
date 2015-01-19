<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var FW_Quiz_Question_Process_Response $question
 */

?>

<div class="correct-answer">
	<strong><?php _e( 'Correct answer: ', 'fw' ); ?></strong>
	<?php if( is_array( $question->get_correct_answer() ) ) : ?>
		<ul>
			<?php foreach( $question->get_correct_answer() as $answer ) : ?>
				<li><?php echo $answer ?></li>
			<?php endforeach ?>
			<?php unset( $answer ) ?>
		</ul>
	<?php else : ?>
		<?php echo $question->get_correct_answer(); ?>
	<?php endif ?>
</div>
<div class="current-answer">
	<strong><?php _e( 'Current answer: ', 'fw' ); ?></strong>
	<?php if( is_array( $question->get_correct_answer() ) ) : ?>
		<ul>
			<?php foreach( $question->get_correct_answer() as $answer ) : ?>
				<li><?php echo $answer ?></li>
			<?php endforeach ?>
			<?php unset( $answer ) ?>
		</ul>
	<?php else : ?>
		<?php echo $question->get_current_answer(); ?>
	<?php endif ?>
</div>