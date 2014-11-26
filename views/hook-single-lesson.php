<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

global $post;

/**
 * @var string $the_content
 */

echo $the_content;

if ( $post->post_parent == 0 ) {
	return;
}
?>
<hr/>
<h4><?php _e( 'Back to', 'fw' ); ?>:
	<a href="<?php echo get_permalink( $post->post_parent ) ?>"><?php echo get_the_title( $post->post_parent ) ?></a>
</h4>