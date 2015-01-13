<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var int $number
 */

$args  = array(
	'number' => $number,
);
$table = new FW_Learning_Grading_Quiz_WP_List_Table( $args );
$table->prepare_items();
?>
<div class="wrap">
	<h2><?php _e( 'Quiz List', 'fw' ); ?></h2>
	<?php $table->display(); ?>
</div>