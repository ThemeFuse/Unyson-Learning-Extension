<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var string $before_widget
 * @var string $after_widget
 * @var string $before_title
 * @var string $after_title
 * @var string $title
 * @var WP_Post[] $courses
 */
?>
<?php echo $before_widget; ?>
<?php echo $before_title . $title . $after_title ?>
	<ul class="items">
		<?php foreach ( $courses as $course ) : ?>
			<li class="item">
				<a href="<?php echo esc_attr(get_permalink( $course->ID )); ?>"><?php echo $course->post_title; ?></a>
			</li>
		<?php endforeach ?>
	</ul>
<?php echo $after_widget; ?>