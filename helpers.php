<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Get all course lessons
 *
 * @param int $post_id
 *
 * @return WP_Post[]
 */
function fw_ext_learning_get_course_lessons( $post_id ) {
	/**
	 * @var FW_Extension_Learning $learning
	 */
	$learning = fw()->extensions->get( 'learning' );

	if ( ! $learning->is_course( $post_id ) ) {
		return array();
	}

	return get_posts( array(
		'post_type'      => $learning->get_lesson_post_type(),
		'posts_per_page' => 300,
		'post_parent'    => $post_id,
		'order'          => 'ASC'
	) );
}

/**
 * Get previous lesson
 *
 * @param int $post_id
 *
 * @return WP_Post|null
 */
function fw_ext_learning_get_previous_lesson( $post_id = null ) {
	/**
	 * @var FW_Extension_Learning $learning
	 */
	$learning = fw()->extensions->get( 'learning' );

	return $learning->get_previous_lesson( $post_id );
}

/**
 * Get next lesson
 *
 * @param int $post_id
 *
 * @return WP_Post|null
 */
function fw_ext_learning_get_next_lesson( $post_id = null ) {
	/**
	 * @var FW_Extension_Learning $learning
	 */
	$learning = fw()->extensions->get( 'learning' );

	return $learning->get_next_lesson( $post_id );
}

/**
 * Converts lesson post format into FontAwesome font class
 *
 * @param int $post_id
 *
 * @return string
 */
function fw_ext_learning_get_lesson_type( $post_id ) {
	/**
	 * @var FW_Extension_Learning $learning
	 */
	$learning = fw()->extensions->get( 'learning' );

	if ( ! $learning->is_lesson( $post_id ) ) {
		return array();
	}

	$format = get_post_format( $post_id );

	switch ( $format ) {
		case 'image' :
			$type = 'fa-image';
			break;
		case 'video' :
			$type = 'fa-play';
			break;
		case 'audio' :
			$type = 'fa-music';
			break;
		default :
			$type = 'fa-book';
	}

	return $type;
}

/**
 * Returns first n words from string
 *
 * @param string $string
 * @param int $count
 * @param string $suffix
 *
 * @return string
 */
function fw_ext_learning_get_words( $string = '', $count = 10, $suffix = '' ) {

	if ( empty( $string ) || ! is_string( $string ) ) {
		return '';
	}

	$string = strip_shortcodes( strip_tags( $string ) );
	$count  = ( (int) $count == 0 ) ? 10 : (int) $count;

	if ( str_word_count( $string ) < $count ) {
		if ( strlen( $string ) == 0 ) {
			return '';
		}

		return $string . $suffix;
	}

	$string = implode( ' ', array_slice( explode( ' ', $string ), 0, $count ) );

	return $string . $suffix;
}