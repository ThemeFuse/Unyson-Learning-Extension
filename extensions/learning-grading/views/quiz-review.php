<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var int $quiz
 * @var int $user
 */

$form = new FW_Learning_Grading_Quiz_Review_Form( $user, $quiz );
?>

<div class="wrap">
	<h2><?php _e( 'Review Quiz', 'fw' ); ?></h2>
	<div class="quiz-review-form">
		<?php echo $form->render(); ?>
	</div>
</div>