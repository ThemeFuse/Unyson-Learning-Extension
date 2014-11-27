<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var int $user
 * @var int $course
 * @var string $status
 * @var array $all_lessons
 * @var array $passed_lessons
 */
?>

<?php if ( $status == 'completed' ) : ?>
	<h3><?php _e( 'Completed', 'fw' ); ?></h3>
	<?php return ?>
<?php endif ?>

<?php if ( count( $all_lessons ) >= count( $passed_lessons ) ) : ?>
	<span><?php echo count( $passed_lessons ) . '/' . count( $all_lessons ); ?></span>
<?php endif ?>