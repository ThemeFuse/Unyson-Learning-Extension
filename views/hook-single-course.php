<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * @var string $the_content
 */
global $post;

$lessons = fw_ext_learning_get_course_lessons( $post->ID )
?>
<?php echo $the_content ?>
<?php if ( ! empty( $lessons ) ) : ?>
	<hr/>
	<h3><?php _e( 'Lessons', 'fw' ); ?></h3>
	<div class="listing-lessons">
		<?php foreach ( $lessons as $lesson ) : fw_ext_learning_get_lesson_type( $lesson->ID ) ?>
			<div class="lesson-item">
				<?php if ( get_the_post_thumbnail( $lesson->ID ) != '' ) : ?>
					<div class="lesson-thumbnail">
						<a href="<?php echo get_permalink( $lesson->ID ) ?>">
							<?php echo get_the_post_thumbnail( $lesson->ID, array( 64, 64 ) ) ?>
						</a>
					</div>
				<?php endif ?>
				<div class="lesson-desc">
					<h4><a href="<?php echo get_permalink( $lesson->ID ) ?>"><?php echo $lesson->post_title ?></a></h4>

					<p><?php echo fw_ext_learning_get_words( $lesson->post_content, 8 ) ?></p>
				</div>
				<div style="clear: both"></div>
			</div>
		<?php endforeach ?>
	</div>
<?php endif ?>