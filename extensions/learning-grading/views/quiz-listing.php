<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var string $title
 * @var WP_Post[] $posts
 */

/**
 * @var FW_Extension_learning $learning
 */
$learning = fw_ext( 'learning' );

?>
<div class="wrap">
	<h2><?php echo $title ?></h2>

	<div class="tablenav top">
		<div class="tablenav-pages one-page">
			<span class="displaying-num"><?php echo count($posts) . ' ' . _n( 'item', 'items', count( $posts ), 'fw' ); ?></span>
		</div>
		<br class="clear">
	</div>
	<table class="wp-list-table widefat fixed posts">
		<thead>
		<tr>
			<th scope="col" id="title" class="manage-column"><?php _e( 'Title', 'fw' ); ?></th>
			<th scope="col" id="lesson-course" class="manage-column"><?php _e( 'Course', 'fw' ); ?></th>
			<th scope="col" id="lesson-author" class="manage-column"><?php _e( 'Author', 'fw' ); ?></th>
		</tr>
		</thead>

		<tfoot>
		<tr>
			<th scope="col" class="manage-column"><?php _e( 'Title', 'fw' ); ?></th>
			<th scope="col" class="manage-column"><?php _e( 'Course', 'fw' ); ?></th>
			<th scope="col" class="manage-column"><?php _e( 'Author', 'fw' ); ?></th>
		</tr>
		</tfoot>

		<tbody id="the-list">
		<?php $count = 0; foreach( $posts as $post ) : $count++ ?>
			<?php $alternate = ( $count%2 > 0 ) ? ' alternate' : '' ?>
			<tr id="post-<?php echo $post->ID; ?>" class="post-<?php echo $post->ID; ?><?php echo $alternate; ?> type-<?php echo $post->post_type; ?> status-<?php echo $post->post_status; ?> has-post-thumbnail level-0">
				<td class="post-title page-title column-title">
					<strong>
						<a class="row-title" href="<?php echo get_edit_post_link($post->ID); ?>" title="<?php echo $post->post_title; ?>"><?php echo $post->post_title; ?></a>
					</strong>
					<div class="row-actions">
					<span class="edit">
						<a href="<?php echo get_edit_post_link( $post->ID ); ?>" title="<?php _e( 'Open', 'fw' ); ?> item"><?php _e( 'Open', 'fw' ); ?></a> |
					</span>
					<span class="view">
						<a href="<?php echo get_permalink( $post->ID ); ?>" title="View <?php echo $post->post_title; ?>" rel="permalink">View</a>
					</span>
					</div>
				</td>
				<td class="lesson-course column-course">
					<a href="<?php echo get_permalink( $learning->get_lesson_course( $post->post_parent )->ID ); ?>"><?php echo $learning->get_lesson_course( $post->post_parent )->post_title; ?></a>
				</td>
				<td class="lesson-author column-author"><?php echo get_the_author_meta( 'user_nicename', $post->post_author ); ?></td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
	<div class="tablenav bottom">
		<div class="alignleft actions"></div>
		<div class="tablenav-pages one-page">
			<span class="displaying-num"><?php echo count( $posts ) . ' ' . _n( 'item', 'items', count( $posts ), 'fw' ); ?></span>
			<span class="pagination-links">
				<a class="first-page disabled" title="Go to the first page" href="http://localhost/unyson/wp-admin/edit.php?post_type=fw-event">«</a>
				<a class="prev-page disabled" title="Go to the previous page" href="http://localhost/unyson/wp-admin/edit.php?post_type=fw-event&amp;paged=1">‹</a>
				<span class="paging-input">1 of
					<span class="total-pages">1</span>
				</span>
				<a class="next-page disabled" title="Go to the next page" href="http://localhost/unyson/wp-admin/edit.php?post_type=fw-event&amp;paged=1">›</a>
				<a class="last-page disabled" title="Go to the last page" href="http://localhost/unyson/wp-admin/edit.php?post_type=fw-event&amp;paged=1">»</a>
			</span>
		</div>
		<br class="clear">
	</div>
</div>