<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var FW_Quiz_Question_Process_Response[] $questions
 */

?>
<div class="row">
	<div class="fw-col-xs-9">
		<?php foreach ( $questions as $question ) : ?>
			<div class="question">
				<h3><?php echo $question->get_question(); ?></h3>
				<?php
				$item = FW_Option_Type_Quiz_Builder::get_item_type( $question->get_item()['type'] );
				if ( ! empty( $item ) && ! is_null( $item->review( $question ) ) ) {
					$item->review( $question );
					continue;
				} else {
					echo fw_render_view(
						fw_ext( 'learning-grading' )->get_declared_path() . '/views/quiz-review-question.php',
						array(
							'question' => $question
						)
					);
				}
				?>
				<strong><?php _e( 'Accumulated', 'fw' ); ?>: </strong>
				<span><?php echo (int) $question->get_current_percentage(); ?>%</span>
			</div>
		<?php endforeach ?>
	</div>
	<div class="fw-col-xs-3">
		<input type="submit" class="button-primary" value="Save">
	</div>
</div>