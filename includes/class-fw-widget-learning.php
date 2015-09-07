<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Widget_Learning extends WP_Widget {
	/**
	 * @var FW_Extension_Learning
	 */
	private $learning = null;

	function __construct() {
		$this->learning = fw()->extensions->get( 'learning' );

		if ( is_null( $this->learning ) ) {
			return;
		}

		$widget_ops = array( 'description' => __( 'Get list of courses', 'fw' ) );

		parent::__construct( false, __( 'Lesson Courses', 'fw' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		$number = ( (int) $instance['number'] <= 0 ) ? 5 : (int) $instance['number'];

		$courses = get_posts(
			array(
				'post_type'      => $this->learning->get_course_post_type(),
				'posts_per_page' => $number,
			)
		);

		if ( is_singular( $this->learning->get_course_post_type() ) || is_singular( $this->learning->get_lesson_post_type() ) ) {
			global $post;
			$exit = false;

			if ( $post->post_type == $this->learning->get_lesson_post_type() && ! empty( $post->post_parent ) ) {
				$course = get_post( $post->post_parent );
				if ( empty( $course ) || is_wp_error( $course ) ) {
					$exit = true;
				}
			} else {
				$course = $post;
			}

			if ( ! $exit ) {
				$terms = wp_get_post_terms( $course->ID, $this->learning->get_categories_taxonomy() );

				if ( ! empty( $terms ) || ! is_wp_error( $terms ) ) {
					$ids = array();
					foreach ( $terms as $term ) {
						$ids[] = $term->term_id;
					}

					$posts = new WP_Query( array(
						'post_type'      => $this->learning->get_course_post_type(),
						'posts_per_page' => $number,
						'post__not_in'   => array( $course->ID ),
						'tax_query'      => array(
							array(
								'taxonomy' => $this->learning->get_categories_taxonomy(),
								'field'    => 'id',
								'terms'    => $ids,
							)
						),
					) );

					if ( ! empty( $posts->posts ) ) {
						$courses = $posts->get_posts();
					} else {
						$courses = get_posts(
							array(
								'post_type'      => $this->learning->get_course_post_type(),
								'posts_per_page' => $number,
								'exclude'        => $course->ID,
							)
						);
					}
				}
			}
		}

		if ( empty( $courses ) ) {
			return;
		}

		$args['before_widget'] = str_replace( 'class="', 'class="widget_learning_courses ', $args['before_widget'] );

		$data = array(
			'before_widget' => $args['before_widget'],
			'after_widget'  => $args['after_widget'],
			'before_title'  => $args['before_title'],
			'after_title'   => $args['after_title'],
			'title'         => $instance['title'],
			'courses'       => $courses,
		);

		echo fw_render_view( $this->learning->locate_view_path( 'widget' ), $data );
	}

	function update( $new_instance, $old_instance ) {
		$instance = wp_parse_args( (array) $new_instance, $old_instance );

		return $instance;
	}

	function form( $instance ) {
		$title = __( 'Courses', 'fw' );
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		}

		$number = 5;
		if ( isset( $instance['number'] ) ) {
			$number = $instance['number'];
		}

		?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php _e( 'Title', 'fw' ); ?> </label>
			<input type="text" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>"
			       value="<?php echo esc_attr( $title ); ?>" class="widefat"
			       id="<?php $this->get_field_id( 'title' ); ?>"/>
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'number' )); ?>"><?php _e( 'Number of courses', 'fw' ); ?>
				:</label>
			<input type="text" name="<?php echo esc_attr($this->get_field_name( 'number' )); ?>"
			       value="<?php echo esc_attr( $number ); ?>" class="widefat"
			       id="<?php echo esc_attr($this->get_field_id( 'number' )); ?>"/>
		</p>
	<?php
	}
}

function fw_ext_learning_register_widget() {
	register_widget( 'FW_Widget_Learning' );
}

add_action( 'widgets_init', 'fw_ext_learning_register_widget' );
