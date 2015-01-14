<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_Learning_Quiz extends FW_Extension {

	/**
	 * @var FW_Extension_Learning
	 */
	private $learning = null;

	/**
	 * @var string
	 */
	private $quiz_slug = 'quiz';

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
		$this->learning    = $this->get_parent();
		$this->pass_method = new FW_Learning_Quiz_Pass_Lesson();

		if ( is_admin() ) {
			$this->admin_actions();
			$this->admin_filters();
		} else {
			$this->theme_actions();
			$this->theme_filters();
		}
	}

	/**
	 * @internal
	 *
	 * @param int $post_id
	 * @param WP_Post $post
	 */
	public function _action_admin_attach_quiz_to_lesson( $post_id, $post ) {
		if ( $post->post_type != $this->learning->get_lesson_post_type() ) {
			return;
		}

		$questions = fw_get_db_post_option( $post_id, $this->get_name() . '-questions' );

		$questions_array = json_decode( $questions['json'] );
		if ( empty( $questions_array ) ) {
			fw_update_post_meta( $post_id, $this->get_name() . '-has-quiz', false );

			return;
		}

		fw_update_post_meta( $post_id, $this->get_name() . '-has-quiz', 1 );
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
		if ( $post_type != $this->learning->get_lesson_post_type() ) {
			return $options;
		}

		$external_options = array();

		foreach ( apply_filters( 'fw_ext_learning_quiz_settings', array() ) as $key => $option ) {
			if ( isset( $external_options[ $key ] ) ) {
				continue;
			}
			$external_options[ $key ] = $option;
		}

		$quiz_options = array(
			'title'   => __( 'Lesson Quiz', 'fw' ),
			'type'    => 'tab',
			'options' => array(
				'quiz-tab' => array(
					'title'   => __( 'Quiz Elements', 'fw' ),
					'type'    => 'tab',
					'options' => array(
						$this->get_name() . '-questions' => array(
							'label'           => false,
							'type'            => 'quiz-builder',
							'fullscreen'      => false,
							'template_saving' => false,
						),
					)
				),
				'pass-tab' => array(
					'title'   => __( 'Quiz settings', 'fw' ),
					'type'    => 'tab',
					'options' => array(
						$this->get_name() . '-passmark' => array(
							'label' => __( 'Quiz Passmark Percentage', 'fw' ),
							'type'  => 'text',
							'desc'  => __( 'The percentage at which the test will be passed.', 'fw' ),
						),
						'external-options'              => array(
							'type'    => 'group',
							'options' => $external_options
						)
					)
				),
			)
		);

		if ( isset( $options['main'] ) && $options['main']['type'] == 'box' ) {
			$options['main']['options'][ $this->get_name() . '-tab' ] = $quiz_options;
		} else {
			$options['main'] = array(
				'title'   => false,
				'type'    => 'box',
				'options' => array(
					'lesson-quiz-tab' => $quiz_options
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

		if ( ! $this->learning->is_lesson( $post->ID ) ) {
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

		if ( $this->is_quiz_page() ) {
			$this->register_form();
		}

		$id = FW_Session::get( $this->get_name() . '-form-id' );

		if ( empty( $id ) ) {
			return;
		}

		if ( ! $this->is_quiz_page() ) {
			FW_Session::del( $this->get_name() . '-form-id' );
		}
	}

	/**
	 * @internal
	 */
	public function _action_theme_add_rewrite_rules() {
		add_rewrite_rule(
			$this->learning->get_lessons_slug() . '/([^/]*)/(' . $this->quiz_slug . ')',
			'index.php?' . $this->learning->get_lesson_post_type() . '=$matches[1]&quiz=$matches[2]',
			'top'
		);
		flush_rewrite_rules();
	}

	/**
	 * @internal
	 */
	public function _action_theme_add_rewrite_tags() {
		add_rewrite_tag( '%' . $this->learning->get_lesson_post_type() . '%', '([^&]+)' );
		add_rewrite_tag( '%quiz%', '(*{1,})' );
	}

	/**
	 * Render quiz form
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function render_quiz( $post_id ) {
		if ( ! $this->has_quiz( $post_id ) ) {
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
	 * Return the lesson post if it has a quiz
	 *
	 * @deprecated
	 *
	 * @param $lesson_id
	 *
	 * @return null|WP_Post
	 */
	public function get_lesson_quiz( $lesson_id ) {
		if ( ! $this->learning->is_lesson( $lesson_id ) ) {
			return null;
		}

		$quiz_items = fw_get_db_post_option( $lesson_id, $this->get_name() . '-questions' );

		if ( empty( $quiz_items['json'] ) ) {
			return null;
		}

		$quiz_items = json_decode( $quiz_items['json'], ARRAY_A );

		if ( empty( $quiz_items ) ) {
			return null;
		}

		if ( ! $this->validate_quiz_items( $quiz_items ) ) {
			return null;
		}

		return get_post( $lesson_id );
	}

	/**
	 * Return lesson quiz permalink
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public function get_permalink( $id = null ) {

		if ( empty( $id ) ) {
			global $post;
			if ( ! $post instanceof WP_Post ) {
				return '';
			}

			$id = $post->ID;
		}

		if ( ! $this->has_quiz( $id ) ) {
			return '';
		}

		if ( get_option( 'permalink_structure' ) ) {
			return get_permalink( $id ) . '/' . $this->quiz_slug;
		}

		return get_permalink( $id ) . '&quiz=' . $this->quiz_slug;
	}

	/**
	 * Return lesson posts that have quiz
	 *
	 * @param array $args
	 * @param bool $all
	 *
	 * @return WP_Post[]
	 */
	public function get_lessons_with_quiz( array $args, $all = true ) {
		$args['post_type']    = $this->learning->get_lesson_post_type();
		$args['meta_query'][] = array(
			'key'   => $this->get_name() . '-has-quiz',
			'value' => 1,
			'type'  => 'NUMERIC'
		);

		if ( $all ) {
			$args['posts_per_page'] = - 1;
		}

		fw_print( $args );

		$query = new WP_Query( $args );

		return $query->get_posts();
	}

	/**
	 * Define if the lesson has a quiz
	 *
	 * @param int $lesson_id
	 *
	 * @return bool
	 */
	public function has_quiz( $lesson_id ) {
		return $this->validate_quiz( $lesson_id );
	}

	/**
	 * @deprecated
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function is_quiz( $id = null ) {
		return $this->has_quiz( $id );
	}

	/**
	 * Detects if you are on quiz page
	 *
	 * @return bool
	 */
	public function is_quiz_page() {
		global $post;

		if ( is_admin()
		     || did_action( 'wp' ) == 0
		     || ! $post instanceof WP_Post
		     || ! $this->learning->is_lesson( $post->ID )
		     || ! $this->has_quiz( $post->ID )
		     || get_query_var( 'quiz' ) != $this->quiz_slug
		) {
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

		if ( ! $this->has_quiz( $post_id ) ) {
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

		do_action( 'fw_ext_learning_quiz_form_process', $return, $post_id );

		if ( $total >= $return['minimum-pass-mark'] ) {
			$lesson = get_post( $post_id )->post_parent;
			$this->pass_method->pass_lesson( $lesson );
		}

		wp_redirect( fw_current_url() );
		exit;
	}

	private function admin_actions() {
		add_action( 'fw_save_post_options', array( $this, '_action_admin_attach_quiz_to_lesson' ), 9, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, '_action_admin_add_static' ), 10 );
	}

	private function theme_actions() {
		add_action( 'init', array( $this, '_action_theme_add_rewrite_rules' ), 9998 );
		add_action( 'init', array( $this, '_action_theme_add_rewrite_tags' ), 9998 );
		add_action( 'wp', array( $this, '_action_theme_define_pass_method' ) );
		add_action( 'wp', array( $this, '_action_theme_define_form' ) );
	}

	private function admin_filters() {
		add_filter( 'fw_post_options', array( $this, '_filter_admin_lessons_quiz_option' ), 10, 2 );
	}

	private function theme_filters() {
		//add_filter('query_vars', array( $this, '_filter_theme_add_query_var'));
	}

	private function register_form() {
		$this->form = new FW_Form( $this->get_name() . '-quiz-form', array(
			'render'   => array( $this, '_form_render' ),
			'validate' => array( $this, '_form_validate' ),
			'save'     => array( $this, '_form_save' ),
		) );
	}

	private function validate_quiz( $lesson_id ) {
		if ( ! $this->learning->is_lesson( $lesson_id ) ) {
			return false;
		}

		$quiz_items = fw_get_db_post_option( $lesson_id, $this->get_name() . '-questions' );

		if ( empty( $quiz_items['json'] ) ) {
			return null;
		}

		$quiz_items = json_decode( $quiz_items['json'], ARRAY_A );

		if ( empty( $quiz_items ) ) {
			return null;
		}

		return $this->validate_quiz_items( $quiz_items );
	}

	private function validate_quiz_items( array $quiz_items ) {
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
				unset( $quiz_items[ $key ] );
				continue;
			}
		}

		if ( empty( $quiz_items ) ) {
			return false;
		}

		return true;
	}
}