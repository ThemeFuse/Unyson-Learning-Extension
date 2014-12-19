<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_Learning_Grading extends FW_Extension {

	/**
	 * @var string
	 */
	private $user_role = 'learning-instructor';

	/**
	 * @var string
	 */
	private $user_name = 'Instructor';

	/**
	 * @var array
	 */
	private $user_capabilities = array(
		'read',
		'publish_posts',
		'edit_published_posts',
		'edit_posts',
		'delete_published_posts',
		'delete_posts',
		'upload_files',
	);

	/**
	 * @var FW_Extension_Learning
	 */
	private $learning = null;

	/**
	 * @var FW_Extension_Learning_Quiz
	 */
	private $quiz = null;

	/**
	 * @var FW_Extension_Learning_Student
	 */
	private $student = null;

	/**
	 * @internal
	 */
	public function _init() {

		$this->learning = fw_ext( 'learning' );
		$this->quiz = fw_ext( 'learning-quiz' );
		$this->student = fw_ext( 'learning-student' );
		$this->define_role();
		$this->register_role();

		if ( is_admin() ) {
			$this->admin_filters();
			$this->admin_actions();
		} else {
			$this->theme_actions();
		}
	}

	public function _instructor_page() {
		$table = new FW_Learning_Grading_WP_List_Table();
		$table->prepare_items();
		?>
		<div class="wrap">
			<div id="icon-users" class="icon32"></div>
			<h2><?php _e( 'Quiz List', 'fw' ); ?></h2>
			<?php $table->display(); ?>
		</div>
	<?php

	}

	/**
	 * @internal
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function _action_filter_set_quiz_options( $options ) {
		$grading_options = array(
			$this->get_name() . '-process-manually' => array(
				'type'  => 'switch',
				'value' => false,
				'label' => __('Process quiz manually', 'fw'),
				'desc'  => __('The quiz requires to be reviewed by lesson author before grading the student', 'fw'),
				'left-choice' => array(
					'value' => false,
					'label' => __('No', 'fw'),
				),
				'right-choice' => array(
					'value' => true,
					'label' => __('Yes', 'fw'),
				),
			)
		);

		return array_merge( $options, $grading_options );
	}

	/**
	 * @internal
	 *
	 * @param int $post_id
	 * @param WP_Post $post
	 */
	public function _action_admin_save_quiz_type( $post_id, $post ) {
		if ( $post->post_type != $this->learning->get_lesson_post_type() ) {
			return;
		}

		$option = fw_get_db_post_option( $post_id, $this->get_name() . '-process-manually' );

		if ( $option ) {
			update_post_meta( $post_id, $this->get_name() . '-process-manually', true );
		} else {
			update_post_meta( $post_id, $this->get_name() . '-process-manually', false );
		}
	}

	/**
	 * @internal
	 */
	public function _action_admin_add_admin_menu() {
		add_menu_page(
			__( 'Instructor', 'fw' ),
			__( 'Instructor', 'fw' ),
			'publish_posts',
			$this->get_name(),
			array( $this, '_instructor_page' ),
			'dashicons-businessman',
			8
		);
	}

	/**
	 * @internal
	 * 
	 * @param array $return
	 * @param int $id
	 */
	public function _action_theme_process_quiz( $return, $id ) {
		if ( ! $this->quiz->is_quiz( $id ) ) {
			return;
		}

		if ( $this->requires_instructor( $id ) ) {
			$return['status'] = 'pending';
		} else {
			if ( $return['minimum-pass-mark'] <= $return['accumulated'] ) {
				$return['status'] = 'passed';
			} else {
				$return['status'] = 'failed';
			}
		}
		
		$lesson = get_post( $id )->post_parent;
		
		$data = array(
			'quiz' => $return
		);

		$this->student->add_lesson_data( $lesson, $data );
		fw_update_user_meta( $this->student->id(), $this->get_name() . '-quiz-status-' . $id, $return['status'] );
	}

	/**
	 * Check if the quiz requires instructor to be processed
	 * 
	 * @param int $id
	 *
	 * @return bool
	 */
	public function requires_instructor( $id ) {
		if ( empty($id) || ! $this->quiz->is_quiz( $id ) ) {
			return false;
		}

		if ( fw_get_db_post_option( $id, $this->get_name() . '-process-manually' == true ) ) {
			return true;
		}
		
		return false;
	}

	private function define_role() {
		$name         = $this->get_config( 'user-name' );
		$capabilities = $this->get_config( 'user-capabilities' );

		if ( ! empty( $name ) && is_string( $name ) ) {
			$this->user_name = $name;
		}
		if ( ! empty( $capabilities ) && is_array( $capabilities ) ) {
			$this->user_capabilities = array_unique( array_merge( $this->user_capabilities, $capabilities ) );
		}
	}

	private function register_role() {
		add_role( $this->user_role, $this->user_name, $this->user_capabilities );

		$role = get_role( $this->user_role );

		foreach ( $this->user_capabilities as $cap ) {
			$role->add_cap( $cap );
		}
	}

	private function admin_actions() {
		add_action( 'fw_save_post_options', array( $this, '_action_admin_save_quiz_type' ), 9, 2 );
		add_action( 'admin_menu', array( $this, '_action_admin_add_admin_menu' ) );
	}

	private function admin_filters() {
		add_action( 'fw_ext_learning_quiz_settings', array( $this, '_action_filter_set_quiz_options' ) );
	}

	private function theme_actions() {
		add_action( 'fw_ext_learning_quiz_form_process', array( $this, '_action_theme_process_quiz' ), 2, 10 );
	}
}