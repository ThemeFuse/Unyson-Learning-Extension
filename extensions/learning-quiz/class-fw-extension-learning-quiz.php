<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_Learning_Quiz extends FW_Extension {

	/**
	 * @var FW_Extension_Learning
	 */
	private $parent = null;

	/**
	 * @var string
	 */
	private $quiz_post = 'fw-learning-quiz';

	/**
	 * @var string
	 */
	private $quiz_post_slug = 'quiz';

	/**
	 * @var FW_Learning_Quiz_Pass_Lesson
	 */
	private $pass_method = null;

	/**
	 * @var FW_Form
	 */
	private $form = null;

	/**
	 * @internal
	 */
	public function _init() {
		$this->parent      = $this->get_parent();
		$this->pass_method = new FW_Learning_Quiz_Pass_Lesson();

		$this->add_actions();

		if ( is_admin() ) {
			$this->admin_actions();
			$this->admin_filters();
		} else {
			$this->theme_actions();
		}
	}

	/**
	 * @internal
	 */
	public function _action_register_custom_post() {
		register_post_type( $this->quiz_post, array(
				'labels'             => array(
					'name'               => 'Quizzes',
					'singular_name'      => 'Quiz',
					'add_new'            => __( 'Add New', 'fw' ),
					'add_new_item'       => __( 'Add New', 'fw' ),
					'edit'               => sprintf( __( 'Edit %s', 'fw' ), 'Quiz' ),
					'edit_item'          => sprintf( __( 'Edit %s', 'fw' ), 'Quiz' ),
					'new_item'           => sprintf( __( 'New %s', 'fw' ), 'Quiz' ),
					'all_items'          => sprintf( __( 'All %s', 'fw' ), 'Quiz' ),
					'view'               => sprintf( __( 'View %s', 'fw' ), 'Quiz' ),
					'view_item'          => sprintf( __( 'View %s', 'fw' ), 'Quiz' ),
					'search_items'       => sprintf( __( 'Search %s', 'fw' ), 'Quiz' ),
					'not_found'          => sprintf( __( 'No %s Found', 'fw' ), 'Quiz' ),
					'not_found_in_trash' => sprintf( __( 'No %s Found In Trash', 'fw' ), 'Quiz' )
				),
				'description'        => '',
				'public'             => false,
				'show_ui'            => false,
				'show_in_admin_bar'  => false,
				'show_in_menu'       => false,
				'publicly_queryable' => true,
				'has_archive'        => false,
				'rewrite'            => array(
					'slug' => $this->quiz_post_slug
				),
				'show_in_nav_menus'  => false,
				'hierarchical'       => false,
				'supports'           => array(
					'title',
				)
			)
		);
	}

	/**
	 * @internal
	 *
	 * @param int $post_id
	 * @param WP_Post $post
	 */
	public function _action_admin_attach_quiz_to_lesson( $post_id, $post ) {
		if ( $post->post_type != $this->parent->get_lesson_post_type() ) {
			return;
		}

		$questions = fw_get_db_post_option( $post_id, $this->get_name() . '-questions' );
		$passmark  = fw_get_db_post_option( $post_id, $this->get_name() . '-passmark' );

		$quiz = get_posts( array(
			'post_parent'    => $post_id,
			'post_type'      => $this->quiz_post,
			'post_status'    => 'any',
			'posts_per_page' => 1,
		) );

		if ( empty( $quiz ) ) {

			$questions_array = json_decode( $questions['json'] );
			if ( empty( $questions_array ) ) {
				return;
			}

			$quiz_post = array(
				'post_name'     => $post->post_name . '-quiz',
				'post_title'    => $post->post_title . ' ' . __( 'Quiz', 'fw' ),
				'post_status'   => $post->post_status,
				'post_type'     => $this->quiz_post,
				'post_parent'   => $post_id,
				'post_password' => $post->post_password,
			);

			$id = wp_insert_post( $quiz_post );

			if ( is_wp_error( $id ) ) {
				return;
			}

			fw_set_db_post_option( $id, $this->get_name() . '-questions', $questions );
			fw_set_db_post_option( $id, $this->get_name() . '-passmark', $passmark );
		} else {
			$id = $quiz[0]->ID;

			$questions_array = json_decode( $questions['json'] );
			if ( empty( $questions_array ) ) {
				wp_delete_post( $id, true );

				return;
			}

			wp_update_post( array(
				'ID'         => $id,
				'post_title' => $post->post_title . ' ' . __( 'Quiz', 'fw' ),
			) );

			fw_set_db_post_option( $id, $this->get_name() . '-questions', $questions );
			fw_set_db_post_option( $id, $this->get_name() . '-passmark', $passmark );
		}
	}

	/**
	 * @internal
	 *
	 * @param $post_id
	 */
	public function _action_admin_remove_lesson_quiz( $post_id ) {
		if ( ! $this->parent->is_lesson( $post_id ) ) {
			return;
		}

		$quiz = get_posts( array(
			'post_parent'    => $post_id,
			'post_type'      => $this->quiz_post,
			'posts_per_page' => 1,
			'post_status'    => 'any',
		) );

		if ( empty( $quiz ) ) {
			return;
		}

		wp_delete_post( $quiz[0]->ID, true );
	}

	/**
	 * @internal
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param WP_Post $post
	 */
	public function _action_admin_lesson_change_status( $new_status, $old_status, $post ) {
		if ( ! $this->parent->is_lesson( $post->ID ) ) {
			return;
		}

		$quiz = get_posts( array(
			'post_parent'    => $post->ID,
			'post_type'      => $this->quiz_post,
			'posts_per_page' => 1,
			'post_status'    => $old_status,
		) );

		if ( empty( $quiz ) ) {
			return;
		}

		wp_update_post( array(
			'ID'          => $quiz[0]->ID,
			'post_status' => $new_status
		) );
	}

	/**
	 * @internal
	 *
	 * @param $post_id
	 */
	public function _action_admin_untrash_lesson_quiz( $post_id ) {
		if ( ! $this->parent->is_lesson( $post_id ) ) {
			return;
		}

		$post = get_post( $post_id );

		$quiz = get_posts( array(
			'post_parent'    => $post_id,
			'post_type'      => $this->quiz_post,
			'posts_per_page' => 1,
			'post_status'    => 'trash',
		) );

		if ( empty( $quiz ) ) {
			return;
		}

		wp_update_post( array(
			'ID'          => $quiz[0]->ID,
			'post_status' => $post->post_status
		) );
	}

	/**
	 * @internal
	 */
	public function _action_admin_add_static() {
		wp_enqueue_style( $this->get_name() . '-styles', $this->get_declared_URI( '/static/css/admin-style.css' ),
			array(),
			fw()->manifest->get_version() );
	}

	/**
	 * @internal
	 *
	 * @param array $options
	 * @param string $post_type
	 *
	 * @return array
	 */
	public function _filter_admin_lessons_quiz_option( $options, $post_type ) {
		if ( $post_type != $this->parent->get_lesson_post_type() ) {
			return $options;
		}

		$tab_options = array(
			'quiz-tab' => array(
				'title'   => __( 'Quiz Elements', 'fw' ),
				'type'    => 'tab',
				'options' => array(
					$this->get_name() . '-questions' => array(
						'label'           => false,
						'type'            => 'quiz-builder',
						'fullscreen'      => false,
						'template_saving' => false,
						'history'         => true,
					),
				)
			),
			'pass-tab' => array(
				'title'   => __( 'Quiz settings', 'fw' ),
				'type'    => 'tab',
				'options' => array(
					$this->get_name() . '-passmark' => array(
						'label' => __( 'Quiz Passmark Points', 'fw' ),
						'type'  => 'text',
						'desc'  => __( 'The points number at which the test will be passed.', 'fw' ),
					),
				)
			)
		);

		if ( isset( $options['main'] ) && $options['main']['type'] == 'box' ) {
			$options['main']['options'][ $this->get_name() ] = array(
				'title'   => __( 'Lesson Quiz', 'fw' ),
				'type'    => 'tab',
				'options' => $tab_options
			);
		} else {
			$options['main'] = array(
				'title'   => false,
				'type'    => 'box',
				'options' => array(
					'lesson-quiz-tab' => array(
						'title'   => __( 'Lesson Quiz', 'fw' ),
						'type'    => 'tab',
						'options' => $tab_options
					)
				)
			);
		}

		return $options;
	}

	/**
	 * @internal
	 */
	public function _action_theme_define_pass_method() {
		global $post;

		if ( empty( $post ) ) {
			return;
		}

		if ( ! $this->parent->is_lesson( $post->ID ) ) {
			return;
		}

		if ( ! $this->has_quiz( $post->ID ) ) {
			return;
		}

		$this->pass_method->register_method();
	}

	/**
	 * @internal
	 */
	public function _action_theme_define_form() {
		global $post;

		if ( ! empty( $post ) && $this->is_quiz( $post->ID ) ) {
			$this->register_form();
		}

		$id = FW_Session::get( $this->get_name() . '-form-id' );

		if ( empty( $id ) ) {
			return;
		}

		if ( empty( $post ) || ! $this->is_quiz() ) {
			FW_Session::del( $this->get_name() . '-form-id' );
		}
	}

	/**
	 * @return string
	 */
	public function get_quiz_post_type() {
		return $this->quiz_post;
	}

	/**
	 * Render quiz form
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function render_quiz( $post_id ) {
		if ( ! $this->is_quiz( $post_id ) ) {
			return '';
		}

		$inputs = fw_get_db_post_option( $post_id, $this->get_name() . '-questions' );

		if ( ! is_array( $inputs ) ) {
			return '';
		}

		if ( ! isset( $inputs['json'] ) ) {
			return '';
		}

		$inputs = json_decode( $inputs['json'], true );

		if ( empty( $inputs ) ) {
			return '';
		}

		ob_start();

		$this->form->render( array(
			'id'     => $post_id,
			'inputs' => $inputs
		) );

		return ob_get_clean();
	}

	/**
	 * Return the quiz post of the lesson
	 *
	 * @param $lesson_id
	 *
	 * @return null|WP_Post
	 */
	public function get_lesson_quiz( $lesson_id ) {
		if ( ! $this->parent->is_lesson( $lesson_id ) ) {
			return null;
		}

		$quiz = get_posts( array(
			'post_type'      => $this->quiz_post,
			'post_parent'    => $lesson_id,
			'posts_per_page' => 1,
		) );

		if ( empty( $quiz ) ) {
			return null;
		}

		$quiz = $quiz[0];

		$quiz_items = fw_get_db_post_option( $lesson_id, $this->get_name() . '-questions' );

		if ( empty( $quiz_items['json'] ) ) {
			return null;
		}

		$quiz_items = json_decode( $quiz_items['json'], ARRAY_A );

		if ( empty( $quiz_items ) ) {
			return null;
		}

		/**
		 * @var FW_Option_Type_Quiz_Builder[] $quiz_builder_items
		 */
		$quiz_builder_items = fw()->backend->option_type( 'quiz-builder' )->get_items();

		foreach ( $quiz_items as $key => $item ) {
			if (
				! isset( $item['type'] ) ||
			    ! isset( $quiz_builder_items[ $item['type'] ] ) ||
				! $quiz_builder_items[ $item['type'] ]->validate_item( $item['options'] )
			) {
				unset($quiz_items[$key]);
				continue;
			}
		}

		if ( empty( $quiz_items ) ) {
			return null;
		}

		return $quiz;
	}

	/**
	 * Define if the lesson has a quiz
	 *
	 * @param int $lesson_id
	 *
	 * @return bool
	 */
	public function has_quiz( $lesson_id ) {
		if ( ! $this->get_lesson_quiz( $lesson_id ) ) {

			return false;
		}

		return true;
	}

	/**
	 * @param int $post_id
	 *
	 * @return bool
	 */
	public function is_quiz( $post_id = null ) {

		if ( $post_id === 0 ) {
			return false;
		}

		if ( $post_id === null ) {
			global $post;
		} else {
			$post = get_post( (int) $post_id );
		}

		if ( empty( $post ) ) {
			return false;
		}

		if ( $post->post_type != $this->quiz_post ) {
			return false;
		}

		return true;
	}

	/**
	 * @internal
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function _form_render( $data ) {
		$id   = $data['data']['id'];
		$name = $this->get_name() . '-form-id';

		FW_Session::set( $name, $id );

		/**
		 * @var FW_Option_Type_Quiz_Builder $builder
		 */
		$builder = fw()->backend->option_type( 'quiz-builder' );

		echo $builder->frontend_render( $data['data']['inputs'], array() );

		$data['submit']['html'] = '';

		return $data;
	}

	/**
	 * @internal
	 *
	 * @param array $errors
	 *
	 * @return array
	 */
	public function _form_validate( $errors ) {
		if ( ! isset( $_SESSION ) ) {
			session_start();
		}

		if ( ! isset( $_SESSION[ $this->get_name() . '-form-id' ] ) ) {
			$errors['invalid-quiz'] = __( 'Invalid Quiz', 'fw' );

			return $errors;
		}

		$post_id = $_SESSION[ $this->get_name() . '-form-id' ];

		if ( ! $this->is_quiz( $post_id ) ) {
			unset( $_SESSION[ $this->get_name() . '-form-id' ] );
			$errors['invalid-quiz'] = __( 'Invalid Quiz', 'fw' );

			return $errors;
		}

		$inputs = fw_get_db_post_option( $post_id, $this->get_name() . '-questions' );
		if ( ! is_array( $inputs ) ) {
			unset( $_SESSION[ $this->get_name() . '-form-id' ] );
			$errors['invalid-quiz'] = __( 'Invalid Quiz', 'fw' );

			return $errors;
		}

		if ( ! isset( $inputs['json'] ) ) {
			unset( $_SESSION[ $this->get_name() . '-form-id' ] );
			$errors['invalid-quiz'] = __( 'Invalid Quiz', 'fw' );

			return $errors;
		}

		$inputs = json_decode( $inputs['json'], true );

		if ( empty( $inputs ) ) {
			unset( $_SESSION[ $this->get_name() . '-form-id' ] );
			$errors['invalid-quiz'] = __( 'Invalid Quiz', 'fw' );

			return $errors;
		}

		return $errors;
	}

	/**
	 * @internal
	 */
	public function _form_save() {
		$post_id = FW_Session::get( $this->get_name() . '-form-id' );
		FW_Session::del( $this->get_name() . '-form-id' );

		$inputs = fw_get_db_post_option( $post_id, $this->get_name() . '-questions' );
		$inputs = json_decode( $inputs['json'], true );

		/**
		 * @var FW_Option_Type_Quiz_Builder $builder
		 */
		$builder = fw()->backend->option_type( 'quiz-builder' );

		$values = array();
		foreach ( $inputs as $input ) {
			$values[ $input['shortcode'] ] = FW_Request::POST( $input['shortcode'] );
		}

		/**
		 * @var FW_Quiz_Question_Process_Response[] $process_response
		 */
		$process_response = $builder->process_answers( $inputs, $values, $post_id );

		$return = array();
		$total  = 0;

		foreach ( $process_response as $response ) {
			$total += $response->get_current_percentage();
		}

		$return['questions']         = $process_response;
		$return['accumulated']       = $total;
		$return['minimum-pass-mark'] = (int) fw_get_db_post_option( $post_id, $this->get_name() . '-passmark' );

		do_action( 'fw_ext_learning_quiz_form_process', $return );

		if ( $total >= $return['minimum-pass-mark'] ) {
			$lesson = get_post( $post_id )->post_parent;
			$this->pass_method->pass_lesson( $lesson );
		}

		wp_redirect( fw_current_url() );
		exit;
	}

	private function add_actions() {
		add_action( 'init', array( $this, '_action_register_custom_post' ) );
	}

	private function admin_actions() {
		add_action( 'fw_save_post_options', array( $this, '_action_admin_attach_quiz_to_lesson' ), 9, 2 );
		add_action( 'before_delete_post', array( $this, '_action_admin_remove_lesson_quiz' ), 9, 1 );
		add_action( 'transition_post_status', array( $this, '_action_admin_lesson_change_status' ), 9, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, '_action_admin_add_static' ), 10 );
	}

	private function theme_actions() {
		add_action( 'wp', array( $this, '_action_theme_define_pass_method' ) );
		add_action( 'wp', array( $this, '_action_theme_define_form' ) );
	}

	private function admin_filters() {
		add_filter( 'fw_post_options', array( $this, '_filter_admin_lessons_quiz_option' ), 10, 2 );
	}

	private function register_form() {
		$this->form = new FW_Form( $this->get_name() . '-quiz-form', array(
			'render'   => array( $this, '_form_render' ),
			'validate' => array( $this, '_form_validate' ),
			'save'     => array( $this, '_form_save' ),
		) );
	}
}