<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_Learning extends FW_Extension {

	/**
	 * @var string
	 */
	private $courses = 'fw-learning-courses';

	/**
	 * @var string
	 */
	private $courses_slug = 'course';

	/**
	 * @var string
	 */
	private $lessons = 'fw-learning-articles';

	/**
	 * @var string
	 */
	private $lessons_slug = 'lesson';

	/**
	 * @var string
	 */
	private $categories = 'fw-learning-courses-taxonomy';

	/**
	 * @var string
	 */
	private $categories_slug = 'courses';

	/**
	 * @internal
	 */
	public function _init() {
		$this->define_slugs();
		$this->add_actions();
		if ( is_admin() ) {
			$this->admin_actions();
			$this->admin_filters();
		}
	}

	/**
	 * @internal
	 */
	public function _action_register_custom_posts_taxonomies() {

		//Register articles
		$post_names = apply_filters( 'fw_ext_learning_lessons_label_name', array(
			'singular' => __( 'Lesson', 'fw' ),
			'plural'   => __( 'Lessons', 'fw' )
		) );

		$articles_labels = array(
			'name'               => $post_names['plural'],
			'singular_name'      => $post_names['singular'],
			'add_new'            => __( 'Add New', 'fw' ),
			'add_new_item'       => sprintf( __( 'Add New %s', 'fw' ), $post_names['singular'] ),
			'edit'               => sprintf( __( 'Edit %s', 'fw' ), $post_names['singular'] ),
			'edit_item'          => sprintf( __( 'Edit %s', 'fw' ), $post_names['singular'] ),
			'new_item'           => sprintf( __( 'New %s', 'fw' ), $post_names['singular'] ),
			'all_items'          => sprintf( __( 'All %s', 'fw' ), $post_names['plural'] ),
			'view'               => sprintf( __( 'View %s', 'fw' ), $post_names['singular'] ),
			'view_item'          => sprintf( __( 'View %s', 'fw' ), $post_names['singular'] ),
			'search_items'       => sprintf( __( 'Search %s', 'fw' ), $post_names['plural'] ),
			'not_found'          => sprintf( __( 'No %s Found', 'fw' ), $post_names['plural'] ),
			'not_found_in_trash' => sprintf( __( 'No %s Found In Trash', 'fw' ), $post_names['plural'] )
		);

		register_post_type( $this->lessons, array(
				'labels'             => $articles_labels,
				'description'        => __( 'Create a lesson', 'fw' ),
				'public'             => true,
				'show_ui'            => true,
				'show_in_admin_bar'  => true,
				'show_in_menu'       => true,
				'publicly_queryable' => true,
				'has_archive'        => false,
				'rewrite'            => array(
					'slug' => $this->lessons_slug
				),
				'menu_position'      => 6,
				'show_in_nav_menus'  => true,
				'menu_icon'          => 'dashicons-welcome-learn-more',
				'hierarchical'       => false,
				'supports'           => array(
					'title',
					'editor',
					'excerpt',
					'thumbnail',
					'post-formats'
				),
				'capabilities'       => array(
					'edit_post'              => 'edit_pages',
					'read_post'              => 'edit_pages',
					'delete_post'            => 'edit_pages',
					'edit_posts'             => 'edit_pages',
					'edit_others_posts'      => 'edit_pages',
					'publish_posts'          => 'edit_pages',
					'read_private_posts'     => 'edit_pages',
					'read'                   => 'edit_pages',
					'delete_posts'           => 'edit_pages',
					'delete_private_posts'   => 'edit_pages',
					'delete_published_posts' => 'edit_pages',
					'delete_others_posts'    => 'edit_pages',
					'edit_private_posts'     => 'edit_pages',
					'edit_published_posts'   => 'edit_pages',
				),
			)
		);

		//Register courses
		$post_names = apply_filters( 'fw_ext_learning_courses_label_name', array(
			'singular' => __( 'Course', 'fw' ),
			'plural'   => __( 'Courses', 'fw' )
		) );

		$courses_labels = array(
			'name'               => $post_names['plural'],
			'singular_name'      => $post_names['singular'],
			'add_new'            => __( 'Add New', 'fw' ),
			'add_new_item'       => sprintf( __( 'Add New %s', 'fw' ), $post_names['singular'] ),
			'edit'               => __( 'Edit', 'fw' ),
			'edit_item'          => sprintf( __( 'Edit %s', 'fw' ), $post_names['singular'] ),
			'new_item'           => sprintf( __( 'New %s', 'fw' ), $post_names['singular'] ),
			'all_items'          => sprintf( __( 'All %s', 'fw' ), $post_names['plural'] ),
			'view'               => sprintf( __( 'View %s', 'fw' ), $post_names['singular'] ),
			'view_item'          => sprintf( __( 'View %s', 'fw' ), $post_names['singular'] ),
			'search_items'       => sprintf( __( 'Search %s', 'fw' ), $post_names['plural'] ),
			'not_found'          => sprintf( __( 'No %s Found', 'fw' ), $post_names['plural'] ),
			'not_found_in_trash' => sprintf( __( 'No %s Found In Trash', 'fw' ), $post_names['plural'] )
		);

		register_post_type( $this->courses, array(
				'labels'             => $courses_labels,
				'description'        => __( 'Create a course', 'fw' ),
				'public'             => true,
				'show_ui'            => true,
				'show_in_menu'       => 'edit.php?post_type=' . $this->lessons,
				'publicly_queryable' => true,
				'has_archive'        => true,
				'rewrite'            => array(
					'slug' => $this->courses_slug
				),
				'menu_position'      => 6,
				'show_in_nav_menus'  => true,
				'menu_icon'          => 'dashicons-welcome-write-blog',
				'hierarchical'       => false,
				'supports'           => array(
					'title', /* Text input field to create a post title. */
					'editor',
					'thumbnail', /* Displays a box for featured image. */
				),
				'capabilities'       => array(
					'edit_post'              => 'edit_pages',
					'read_post'              => 'edit_pages',
					'delete_post'            => 'edit_pages',
					'edit_posts'             => 'edit_pages',
					'edit_others_posts'      => 'edit_pages',
					'publish_posts'          => 'edit_pages',
					'read_private_posts'     => 'edit_pages',
					'read'                   => 'edit_pages',
					'delete_posts'           => 'edit_pages',
					'delete_private_posts'   => 'edit_pages',
					'delete_published_posts' => 'edit_pages',
					'delete_others_posts'    => 'edit_pages',
					'edit_private_posts'     => 'edit_pages',
					'edit_published_posts'   => 'edit_pages',
				),
			)
		);

		//Register categories
		$category_names = apply_filters( 'fw_ext_' . $this->courses . '_category_name', array(
			'singular' => __( 'Course Category', 'fw' ),
			'plural'   => __( 'Course Categories', 'fw' )
		) );

		$labels = array(
			'name'              => sprintf( _x( '%s', 'taxonomy general name', 'fw' ), $category_names['plural'] ),
			'singular_name'     => sprintf( _x( '%s', 'taxonomy singular name', 'fw' ), $category_names['singular'] ),
			'search_items'      => __( 'Search categories', 'fw' ),
			'all_items'         => sprintf( __( 'All %s', 'fw' ), $category_names['plural'] ),
			'parent_item'       => sprintf( __( 'Parent %s', 'fw' ), $category_names['singular'] ),
			'parent_item_colon' => sprintf( __( 'Parent %s:', 'fw' ), $category_names['singular'] ),
			'edit_item'         => sprintf( __( 'Edit %s', 'fw' ), $category_names['singular'] ),
			'update_item'       => sprintf( __( 'Update %s', 'fw' ), $category_names['singular'] ),
			'add_new_item'      => __( 'Add New category', 'fw' ),
			'new_item_name'     => sprintf( __( 'New %s Name', 'fw' ), $category_names['singular'] ),
			'menu_name'         => sprintf( __( '%s', 'fw' ), $category_names['plural'] )
		);

		$args = array(
			'labels'            => $labels,
			'public'            => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'rewrite'           => array(
				'slug' => $this->categories_slug
			),
		);

		register_taxonomy( $this->categories, esc_attr( $this->courses ), $args );
	}

	/**
	 * @internal
	 */
	public function _action_admin_add_course_taxonomy_under_lessons() {

		$category_names = apply_filters( 'fw_ext_' . $this->courses . '_category_name', array(
			'singular' => __( 'Course Category', 'fw' ),
			'plural'   => __( 'Course Categories', 'fw' )
		) );

		add_submenu_page( 'edit.php?post_type=' . $this->lessons,
			$category_names['plural'], $category_names['plural'],
			'manage_categories',
			'edit-tags.php?taxonomy=' . $this->categories . '&post_type=' . $this->lessons );
	}

	/**
	 * @internal
	 *
	 * @param int $post_id
	 * @param WP_Post $post
	 */
	public function _action_admin_set_lesson_parent( $post_id, $post ) {
		if ( $post->post_type != $this->lessons ) {
			return;
		}

		$option_value = fw_get_db_post_option( $post_id, $this->get_name() . '-courses' );

		if ( empty( $option_value ) ) {
			return;
		}

		$parent_id = (int) $option_value;

		remove_action( 'fw_save_post_options', array( $this, '_action_admin_set_lesson_parent' ) );

		wp_update_post( array(
			'ID'          => $post_id,
			'post_parent' => $parent_id
		) );

		add_action( 'fw_save_post_options', array( $this, '_action_admin_set_lesson_parent' ), 10, 2 );
	}

	/**
	 * @internal
	 */
	public function _action_admin_add_lessons_edit_page_filter() {
		$screen = fw_current_screen_match( array(
			'only' => array(
				'base'      => 'edit',
				'id'        => 'edit-' . $this->lessons,
				'post_type' => $this->lessons,
			)
		) );

		if ( ! $screen ) {
			return;
		}

		$courses = get_posts( array(
			'post_type'     => $this->courses,
			'post_status'   => 'any',
			'post_per_page' => - 1,
			'post_parent'   => 0,
		) );

		$form = '<select name="' . $this->get_name() . '-filter-by-course">' .
		        '<option value="0">' . __( 'View all courses', 'fw' ) . '</option>';

		$get = FW_Request::GET( $this->get_name() . '-filter-by-course' );
		$id  = ( ! empty( $get ) ) ? (int) $get : 0;

		foreach ( $courses as $course ) {
			$selected = ( $id == $course->ID ) ? 'selected="selcted"' : '';
			$form .= '<option value="' . $course->ID . '" ' . $selected . '>' . $course->post_title . '</option>';
		}
		$form .= '</select>';

		echo '';
		echo $form;
		echo '';
	}

	/**
	 * @internal
	 */
	public function _action_admin_add_courses_edit_page_filter() {
		$screen = fw_current_screen_match( array(
			'only' => array(
				'base'      => 'edit',
				'id'        => 'edit-' . $this->courses,
				'post_type' => $this->courses,
			)
		) );

		if ( ! $screen ) {
			return;
		}

		$terms = get_terms( $this->categories );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			echo '<select name="' . $this->get_name() . '-filter-by-courses-category"><option value="0">' . __( 'View all categories',
					'fw' ) . '</option></select>';

			return;
		}

		$get = FW_Request::GET( $this->get_name() . '-filter-by-courses-category' );
		$id  = ( ! empty( $get ) ) ? (int) $get : 0;

		$dropdown_options = array(
			'selected'        => $id,
			'name'            => $this->get_name() . '-filter-by-course-category">',
			'taxonomy'        => $this->categories,
			'show_option_all' => __( 'View all categories', 'fw' ),
			'hide_empty'      => true,
			'hierarchical'    => 1,
			'show_count'      => 0,
			'orderby'         => 'name',
		);

		wp_dropdown_categories( $dropdown_options );
	}

	/**
	 * @internal
	 */
	public function _action_admin_initial_nav_menu_meta_boxes() {
		$screen = array(
			'only' => array(
				'base' => 'nav-menus'
			)
		);

		if ( ! fw_current_screen_match( $screen ) ) {
			return;
		}

		$user_ID = get_current_user_id();
		$meta    = fw_get_db_extension_user_data( $user_ID, $this->get_name() );

		if ( isset( $meta['metaboxhidden_nav-menus'] ) && $meta['metaboxhidden_nav-menus'] == true ) {
			return;
		}

		$hidden_meta_boxes = get_user_meta( $user_ID, 'metaboxhidden_nav-menus' );
		if ( $key = array_search( 'add-' . $this->categories, $hidden_meta_boxes[0] ) ) {
			unset( $hidden_meta_boxes[0][ $key ] );
		}

		update_user_option( $user_ID, 'metaboxhidden_nav-menus', $hidden_meta_boxes[0], true );

		if ( ! is_array( $meta ) ) {
			$meta = array();
		}

		if ( ! isset( $meta['metaboxhidden_nav-menus'] ) ) {
			$meta['metaboxhidden_nav-menus'] = true;
		}

		fw_set_db_extension_user_data( $user_ID, $this->get_name(), $meta );
	}

	/**
	 * @internal
	 */
	public function _action_admin_set_lessons_format() {
		if ( ! fw_current_screen_match( array(
			'only' => array(
				'base' => 'post',
				'id'   => $this->lessons
			)
		) )
		) {
			return;
		}

		add_theme_support( 'post-formats', array( 'image', 'video', 'audio' ) );
	}

	/**
	 * @internal
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param WP_Post $post
	 */
	public function _action_admin_course_is_updated( $new_status, $old_status, $post ) {
		if ( ! $this->is_course( $post->ID ) ) {
			return;
		}

		$lessons = get_posts( array(
			'post_type'     => $this->lessons,
			'post_parent'   => $post->ID,
			'post_per_page' => 300,
			'post_status'   => $old_status
		) );

		if ( empty( $lessons ) || is_wp_error( $lessons ) ) {
			return;
		}

		foreach ( $lessons as $lesson ) {
			$new_lesson = array(
				'ID'          => $lesson->ID,
				'post_status' => $new_status,
			);

			wp_update_post( $new_lesson );
		}
	}

	/**
	 * @internal
	 *
	 * @param int $post_id
	 */
	public function _action_admin_course_is_removed( $post_id ) {
		if ( ! $this->is_course( $post_id ) ) {
			return;
		}

		$course = get_post( $post_id );

		$lessons = get_posts( array(
			'post_type'     => $this->lessons,
			'post_parent'   => $post_id,
			'post_per_page' => 300,
			'post_status'   => $course->post_status
		) );

		if ( empty( $lessons ) || is_wp_error( $lessons ) ) {
			return;
		}

		foreach ( $lessons as $lesson ) {
			fw_set_db_post_option( $lesson->ID, $this->get_name() . '-courses', 0 );
		}
	}

	/**
	 * @internal
	 *
	 * @param array $filters
	 *
	 * @return array
	 */
	public function _filter_admin_remove_select_by_date_filter( $filters ) {
		$lesson_screen = array(
			'only' => array(
				'base' => 'edit',
				'id'   => 'edit-' . $this->lessons,
			)
		);

		$course_screen = array(
			'only' => array(
				'base' => 'edit',
				'id'   => 'edit-' . $this->courses,
			)
		);

		if ( ! fw_current_screen_match( $lesson_screen ) && ! fw_current_screen_match( $course_screen ) ) {
			return $filters;
		}

		return array();
	}

	/**
	 * @internal
	 *
	 * @param array $options
	 * @param string $post_type
	 *
	 * @return array
	 */
	public function _filter_admin_add_parent_course_select_option( $options, $post_type ) {
		if ( $post_type != $this->lessons ) {
			return $options;
		}

		$courses = get_posts( array(
			'post_type'      => $this->courses,
			'post_status'    => 'any',
			'posts_per_page' => 300,
		) );

		$list = array();

		if ( empty( $courses ) ) {
			$list[0] = __( 'No courses available', 'fw' );
		} else {
			$list[0] = __( 'Without Course', 'fw' );
			foreach ( $courses as $course ) {
				$list[ $course->ID ] = $course->post_title;
			}
		}

		$options[ $this->get_name() ] = array(
			'title'   => __( 'Select Course', 'fw' ),
			'type'    => 'box',
			'context' => 'side',
			'options' => array(
				$this->get_name() . '-courses' => array(
					'label'   => false,
					'type'    => 'select',
					'choices' => $list
				)
			)
		);

		return $options;
	}

	/**
	 * @internal
	 *
	 * @param WP_Query $query
	 *
	 * @return WP_Query
	 */
	public function _filter_admin_filter_lessons_by_course( $query ) {
		$screen = fw_current_screen_match( array(
			'only' => array(
				'base'      => 'edit',
				'id'        => 'edit-' . $this->lessons,
				'post_type' => $this->lessons,
			)
		) );

		if ( ! $screen || ! $query->is_main_query() ) {
			return $query;
		}

		$filter_value = FW_Request::GET( $this->get_name() . '-filter-by-course' );

		if ( empty( $filter_value ) ) {
			return $query;
		}

		$filter_value = (int) $filter_value;

		$query->set( 'post_parent', $filter_value );

		return $query;
	}

	/**
	 * @internal
	 *
	 * @param WP_Query $query
	 *
	 * @return WP_Query
	 */
	public function _filter_admin_filter_courses_by_course_category( $query ) {
		$screen = fw_current_screen_match( array(
			'only' => array(
				'base'      => 'edit',
				'id'        => 'edit-' . $this->courses,
				'post_type' => $this->courses,
			)
		) );

		if ( ! $screen || ! $query->is_main_query() ) {
			return $query;
		}

		$filter_value = FW_Request::GET( $this->get_name() . '-filter-by-course-category' );

		if ( empty( $filter_value ) ) {
			return $query;
		}

		$filter_value = (int) $filter_value;

		$query->set( 'tax_query', array(
			array(
				'taxonomy' => $this->categories,
				'field'    => 'id',
				'terms'    => $filter_value,
			)
		) );

		return $query;
	}

	/**
	 * {@inheritdoc}
	 */
	public function _get_link() {
		return self_admin_url('edit.php?post_type=' . $this->lessons);
	}

	/**
	 * Return the courses custom post name
	 * @return string
	 */
	public function get_course_post_type() {
		return $this->courses;
	}

	/**
	 * Return the courses custom post slug
	 * @return string
	 */
	public function get_course_slug() {
		return $this->courses_slug;
	}

	/**
	 * Return the lessons custom post name
	 * @return string
	 */
	public function get_lesson_post_type() {
		return $this->lessons;
	}

	/**
	 * Return the lessons custom post slug
	 * @return string
	 */
	public function get_lessons_slug() {
		return $this->lessons_slug;
	}

	/**
	 * Return the courses categories taxonomy name
	 * @return string
	 */
	public function get_categories_taxonomy() {
		return $this->categories;
	}

	/**
	 * Return the courses categories taxonomy slug
	 * @return string
	 */
	public function get_categories_slug() {
		return $this->categories_slug;
	}

	/**
	 * Checks if the post is course post type
	 * User can send the post id via $course_id parameters to check, in other case will be used the global $post
	 *
	 * @param int $course_id
	 *
	 * @return bool
	 */
	public function is_course( $course_id = null ) {

		if ( $course_id === 0 ) {
			return false;
		}

		if ( $course_id === null ) {
			global $post;
		} else {
			$post = get_post( (int) $course_id );
		}

		if ( is_null( $post ) ) {
			return false;
		}

		if ( $post->post_type != $this->get_course_post_type() ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the post is lesson post type,
	 * If $lesson_id is null, will be used the global $post
	 *
	 * @param int $lesson_id
	 *
	 * @return bool
	 */
	public function is_lesson( $lesson_id = null ) {

		if ( $lesson_id === 0 ) {
			return false;
		}

		if ( $lesson_id === null ) {
			global $post;
		} else {
			$post = get_post( (int) $lesson_id );
		}

		if ( is_null( $post ) ) {
			return false;
		}

		if ( $post->post_type != $this->get_lesson_post_type() ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the previous lesson of the current course,
	 * If $lesson_id is null, will be used the global $post
	 * If the provided lesson id is not valid, returns false
	 * If the lesson doesn't have any other previous posts, returns null
	 *
	 * @param int $lesson_id
	 *
	 * @return WP_Post|null|bool
	 */
	public function get_previous_lesson( $lesson_id = null ) {
		if ( ! $this->is_lesson( $lesson_id ) ) {
			return false;
		}

		if ( $lesson_id === null ) {
			global $post;
		} else {
			$post = get_post( $lesson_id );
		}

		/**
		 * @var WPDB $wpdb
		 */
		global $wpdb;

		$result = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->posts} AS p " .
			"WHERE p.post_date < %s AND p.post_type = %s AND p.post_parent = %d AND p.post_status = 'publish' " .
			"ORDER BY p.post_date DESC LIMIT 1",
			$post->post_date,
			$post->post_type,
			$post->post_parent
		) );

		if ( empty( $result ) ) {
			return null;
		}

		return $result[0];
	}

	/**
	 * Get the next lesson of the current course
	 * If $lesson_id is null, will be used the global $post
	 * If the provided lesson id is not valid, returns false
	 * If the lesson doesn't have any other next posts, returns null
	 *
	 * @param int $lesson_id
	 *
	 * @return WP_Post|null|bool
	 */
	public function get_next_lesson( $lesson_id ) {
		if ( ! $this->is_lesson( $lesson_id ) ) {
			return false;
		}

		if ( $lesson_id === null ) {
			global $post;
		} else {
			$post = get_post( $lesson_id );
		}

		/**
		 * @var WPDB $wpdb
		 */
		global $wpdb;

		$result = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->posts} AS p " .
			"WHERE p.post_date > %s AND p.post_type = %s AND p.post_parent = %d AND p.post_status = 'publish' " .
			"ORDER BY p.post_date ASC LIMIT 1",
			$post->post_date,
			$post->post_type,
			$post->post_parent
		) );

		if ( empty( $result ) ) {
			return null;
		}

		return $result[0];
	}

	/**
	 * @param int $course_id
	 *
	 * @return array
	 */
	public function get_course_lessons( $course_id = 0 ) {
		if ( ! $this->is_course( $course_id ) ) {
			return array();
		}

		return get_posts( array(
			'post_type'      => $this->lessons,
			'post_parent'    => $course_id,
			'post_status'    => 'any',
			'posts_per_page' => - 1,
		) );
	}

	private function admin_filters() {
		add_filter( 'fw_post_options', array( $this, '_filter_admin_add_parent_course_select_option' ), 10, 2 );
		add_filter( 'parse_query', array( $this, '_filter_admin_filter_lessons_by_course' ), 10, 2 );
		add_filter( 'parse_query', array( $this, '_filter_admin_filter_courses_by_course_category' ), 10, 2 );
		add_filter( 'months_dropdown_results', array( $this, '_filter_admin_remove_select_by_date_filter' ) );
	}

	private function add_actions() {
		add_action( 'init', array( $this, '_action_register_custom_posts_taxonomies' ) );
	}

	private function admin_actions() {
		add_action( 'fw_save_post_options', array( $this, '_action_admin_set_lesson_parent' ), 10, 2 );
		add_action( 'admin_menu', array( $this, '_action_admin_add_course_taxonomy_under_lessons' ), 10 );
		add_action( 'admin_head', array( $this, '_action_admin_initial_nav_menu_meta_boxes' ), 999 );
		add_action( 'admin_head', array( $this, '_action_admin_set_lessons_format' ), 999 );
		add_action( 'transition_post_status', array( $this, '_action_admin_course_is_updated' ), 9, 3 );
		add_action( 'before_delete_post', array( $this, '_action_admin_course_is_removed' ), 9, 1 );
		add_action( 'restrict_manage_posts', array( $this, '_action_admin_add_lessons_edit_page_filter' ) );
		add_action( 'restrict_manage_posts', array( $this, '_action_admin_add_courses_edit_page_filter' ) );
	}

	private function define_slugs() {
		$settings = $this->get_config( 'slugs' );

		$this->courses_slug = ( isset( $settings['courses'] ) && ! empty( $settings['courses'] ) )
			? $settings['courses'] : $this->courses_slug;

		$this->lessons_slug = ( isset( $settings['lessons'] ) && ! empty( $settings['lessons'] ) )
			? $settings['lessons'] : $this->lessons_slug;

		$this->categories_slug = ( isset( $settings['categories'] ) && ! empty( $settings['categories'] ) )
			? $settings['categories'] : $this->categories_slug;
	}
}