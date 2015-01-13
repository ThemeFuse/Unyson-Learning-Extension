<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var int $id
 * @var int $number
 */

$args  = array(
	'quiz-id'     => $id,
	'number' => $number,
);
$table = new FW_Learning_Grading_Students_WP_List_Table( $args );
$table->prepare_items();
?>
<div class="wrap">
	<h2><?php _e( 'Users List', 'fw' ); ?></h2>
	<?php $table->display(); ?>
</div>