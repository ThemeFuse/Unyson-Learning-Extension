<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_Learning_Student extends FW_Extension {

	/**
	 * @var string
	 */
	private $user_role = 'learning-student';

	/**
	 * @var string
	 */
	private $user_name = 'Student';

	/**
	 * @var array
	 */
	private $user_capabilities = array(
		'read'
	);

	/**
	 * @var FW_Learning_Student|null
	 */
	private $current_user = null;

	/**
	 * @var FW_Extension_Learning
	 */
	private $learning = null;

	/**
	 * @var FW_Learning_Student_Take_Course_Default_Method
	 */
	private $take_course_method = null;

	/**
	 * @var FW_Learning_Student_Complete_Course
	 */
	private $complete_course_method = null;

	/**
	 * @var FW_Learning_Student_Pass_Lesson
	 */
	private $pass_lesson_method = null;

	/**
	 * @var FW_Form
	 */
	private $student_account_form = null;

	private $save_meta = array(
		'courses'        => false,
		'courses-status' => false,
		'lessons'        => false,
		'lessons-status' => false,
		'lessons-quiz'   => false,
	);

	/**
	 * @internal
	 */
	public function _init() {

		$user_require = $this->get_config( 'user-require' );

		if ( $user_require === false ) {
			return;
		}

		$this->learning           = fw()->extensions->get( 'learning' );
		$this->pass_lesson_method = new FW_Learning_Student_Pass_Lesson();
		$this->take_course_method = new FW_Learning_Student_Take_Course_Default_Method();

		$this->save_meta = array_merge(
			apply_filters( 'fw_ext_learning_student_save_meta', $this->save_meta ),
			$this->save_meta
		);

		$this->define_role();
		$this->register_role();
		$this->add_actions();
	}

	/**
	 * @param FW_Learning_Student_Complete_Course $method
	 */
	public function set_complete_course_method( FW_Learning_Student_Complete_Course $method ) {

		//If the current method is not set or has low priority automatically set the new method
		if ( empty( $this->complete_course_method ) || ( $this->complete_course_method->get_priority() == false ) ) {
			$this->complete_course_method = $method;

			return;
		}

		//If current method has high priority, need to check the priority of the new method
		if ( $method->get_priority() == true ) {
			$this->complete_course_method = $method;

			return;
		}
	}

	/**
	 * @return string
	 */
	public function get_role_type() {
		return $this->user_role;
	}

	/**
	 * @return string
	 */
	public function get_role_name() {
		return $this->user_name;
	}

	/**
	 * @return array
	 */
	public function get_capabilities() {
		return $this->user_capabilities;
	}

	/**
	 * Return URL to the login page
	 *
	 * @param bool $redirect , Redirect back to the current page, or not.
	 *
	 * @return string
	 */
	public function get_login( $redirect = false ) {
		if ( $redirect ) {
			return wp_login_url();
		}

		return wp_login_url( fw_current_url() );
	}

	/**
	 * Checks if the is logged in the current user and if it is an allowed user type.
	 * ( Now all user role types are allowed )
	 *
	 * @return bool
	 */
	public function is_logged_in() {
		if ( ! $this->is_student() ) {
			return false;
		}

		return true;
	}

	/**
	 * Attaches new course for the current user or updates the course data
	 * In case the course doesn't not exist, it is added, if it exists already, course data is updated.
	 *
	 * @param int $course_id
	 * @param array $data
	 *
	 * @return bool, true in case the course was added/updated, false in case the course ID is not valid.
	 */
	public function add_course_data( $course_id, array $data ) {
		if ( ! $this->is_student() ) {
			return false;
		}

		return $this->current_user->add_course_data( $course_id, $data );
	}

	/**
	 * Attaches new lesson for the current user or updates the lesson data
	 * In case the lesson doesn't not exist, it is added, if it exists already, lesson data is updated.
	 *
	 * @param int $lesson_id
	 * @param array $data
	 *
	 * @return bool, true in case the course was added/updated, false in case the course ID is not valid.
	 */
	public function add_lesson_data( $lesson_id, array $data ) {
		if ( ! $this->is_student() ) {
			return false;
		}

		return $this->current_user->add_lesson_data( $lesson_id, $data );
	}

	/**
	 * @return int
	 */
	public function id() {
		if ( empty( $this->current_user ) ) {
			return 0;
		}

		return $this->current_user->id();
	}

	/**
	 * Check if the current user is a student or not
	 *
	 * @return bool
	 */
	public function is_student() {

		if ( $this->id() <= 0 ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the user is subscribed to the current course
	 *
	 * @param int $id - course id
	 *
	 * @return bool
	 */
	public function is_subscribed( $id = null ) {
		if ( is_null( $id ) && isset( $GLOBALS['post'] ) ) {
			$id = $GLOBALS['post']->ID;
		}

		if ( empty( $id ) ) {
			return null;
		}

		if ( ! $this->is_student() || ! $this->learning->is_course( $id ) ) {
			return false;
		}

		$data = $this->get_courses_data( $id );

		if ( empty( $data ) ) {
			return false;
		}

		if ( ! isset( $data['status'] ) || $data['status'] != 'open' ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the user completed the course
	 *
	 * @param int $id - course id
	 *
	 * @return bool
	 */
	public function has_completed( $id ) {
		if ( is_null( $id ) && isset( $GLOBALS['post'] ) ) {
			$id = $GLOBALS['post']->ID;
		}

		if ( empty( $id ) ) {
			return null;
		}

		if ( ! $this->is_student() || ! $this->learning->is_course( $id ) ) {
			return false;
		}

		$data = $this->get_courses_data( $id );

		if ( empty( $data ) ) {
			return false;
		}

		if ( ! isset( $data['status'] ) || $data['status'] != 'completed' ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the user passed the lesson
	 *
	 * @param int $id - course id
	 *
	 * @return bool
	 */
	public function has_passed( $id ) {
		if ( is_null( $id ) && isset( $GLOBALS['post'] ) ) {
			$id = $GLOBALS['post']->ID;
		}

		if ( empty( $id ) ) {
			return null;
		}

		if ( ! $this->is_student() || ! $this->learning->is_lesson( $id ) ) {
			return false;
		}

		$data = $this->get_lessons_data( $id );

		if ( empty( $data ) ) {
			return false;
		}

		if ( ! isset( $data['status'] ) || $data['status'] != 'completed' ) {
			return false;
		}

		return true;
	}

	/**
	 * @param int $post_id
	 *
	 * @return bool
	 */
	public function is_author( $post_id ) {

		if ( ! $this->learning->is_lesson( $post_id ) ) {
			return false;
		}

		if ( ! is_user_logged_in() ) {
			return false;
		}

		$post = get_post( $post_id );

		if ( $post->post_author != get_current_user_id() ) {
			return false;
		}

		return true;
	}

	/**
	 * Return all data about user courses
	 * $ids parameter can be empty to return all courses data, or an array of courses ids, or and single id
	 *
	 * If user is not logged in, return false
	 *
	 * @param int|array[int] $ids
	 *
	 * @return array
	 */
	public function get_courses_data( $ids = null ) {
		if ( ! $this->is_student() ) {
			return false;
		}

		return $this->current_user->get_courses_data( $ids );
	}

	/**
	 * @param int $course_id
	 */
	public function remove_course( $course_id ) {
		if ( ! $this->is_student() ) {
			return;
		}

		$this->current_user->remove_course( $course_id );
	}

	/**
	 * Return all data about user lessons
	 * $ids parameter can be empty to return all lessons data, or an array of lessons ids, or and single id
	 *
	 * If user is not logged in, return false
	 *
	 * @param int|array[int] $ids
	 *
	 * @return array|bool
	 */
	public function get_lessons_data( $ids = null ) {
		if ( ! $this->is_student() ) {
			return false;
		}

		return $this->current_user->get_lessons_data( $ids );
	}

	/**
	 * Return user passed lessons of a specific course
	 *
	 * @param int $course_id
	 *
	 * @return array
	 */
	public function get_course_lessons( $course_id = 0 ) {

		if ( ! $this->learning->is_course( $course_id ) ) {
			return array();
		}

		$lessons = $this->get_lessons_data();
		if ( empty( $lessons ) ) {
			return array();
		}

		foreach ( $lessons as $key => $lesson ) {
			if ( $lesson['course-id'] != $course_id ) {
				unset( $lessons[ $key ] );
			}
		}

		return $lessons;
	}

	/**
	 * @param int $lesson_id
	 */
	public function remove_lesson( $lesson_id ) {
		if ( ! $this->is_student() ) {
			return;
		}

		$this->current_user->remove_lesson( $lesson_id );
	}

	/**
	 * @internal
	 */
	public function _action_define_current_user() {
		$this->set_current_user();

		if ( $this->is_student() ) {
			if ( is_admin() ) {
				$this->admin_active_actions();
				$this->admin_active_filters();
			} else {

				$this->student_account_form = new FW_Form(
					$this->get_name() . '-student-account',
					array(
						'render'   => array( $this, '_render_student_form' ),
						'validate' => array( $this, '_validate_student_form' ),
						'save'     => array( $this, '_save_student_form' ),
					)
				);

				$this->theme_active_actions();
				$this->theme_active_filters();
			}
		} else {
			if ( is_admin() ) {
				$this->admin_non_active_actions();
				$this->admin_non_active_filters();
			} else {
				$this->theme_non_active_actions();
				$this->theme_non_active_filters();
			}
		}
	}

	/**
	 * @internal
	 *
	 * @param int $post_id
	 */
	public function _action_admin_course_deleted( $post_id ) {
		if ( ! $this->learning->is_course( $post_id ) ) {
			return;
		}

		/**
		 * @var wpdb $wpdb
		 */
		global $wpdb;

		$users = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT user_id ID ' .
				'FROM ' . $wpdb->usermeta .
				" WHERE meta_key = 'learning-student-course-id' AND meta_value = %d",
				165 )
		);

		if ( empty( $users ) ) {
			return;
		}

		foreach ( $users as $user ) {
			$id = (int) $user->ID;

			if ( $id == 0 ) {
				continue;
			}

			$meta = fw_get_db_extension_user_data( $id, $this->get_name() );
			if ( ! is_array( $meta ) || empty( $meta ) ) {
				continue;
			}

			if ( ! isset( $meta['courses'][ $post_id ] ) ) {
				continue;
			}

			if ( $meta['courses'][ $post_id ]['status'] == 'open' ) {
				$meta['courses'][ $post_id ]['status'] = 'deleted';
				fw_set_db_extension_user_data( $id, $this->get_name(), $meta );
			}
		}

	}

	/**
	 * @internal
	 */
	public function _action_theme_define_pass_lesson_method() {
		if ( ! $this->learning->is_lesson() ) {
			return;
		}

		$this->pass_lesson_method->register_method();
	}

	/**
	 * @internal
	 *
	 * @param int $course_id
	 */
	public function _action_theme_apply_course_user( $course_id ) {
		if ( ! $this->learning->is_course( $course_id ) ) {
			return;
		}

		if ( $this->add_course_data( $course_id, array( 'status' => 'open' ) ) ) {
			do_action( 'fw_ext_learning_student_completed_course', $course_id );
		}
	}

	/**
	 * @internal
	 *
	 * @param int $course_id
	 */
	public function _action_theme_complete_course_user( $course_id ) {
		if ( $this->learning->is_course( $course_id ) ) {
			$this->add_course_data( $course_id, array( 'status' => 'completed' ) );
		}
	}

	/**
	 * @internal
	 *
	 * @param int $lesson_id
	 */
	public function _action_theme_user_completed_lesson( $lesson_id ) {
		if ( ! $this->learning->is_lesson( $lesson_id ) ) {
			return;
		}
		$course = $this->learning->get_lesson_course( $lesson_id );

		if ( empty( $course ) || ! $this->is_subscribed( $course->ID ) ) {
			return;
		}

		if ( $this->add_lesson_data( $lesson_id, array( 'status' => 'completed' ) ) ) {
			do_action( 'fw_ext_learning_student_completed_lesson', $lesson_id );
		}
	}

	/**
	 * @internal
	 */
	public function _action_theme_redirect_non_logged_in_user() {
		if ( $this->learning->is_lesson() ) {
			$this->add_flash( __( 'Login in order to view lessons', 'fw' ) );
			wp_redirect( $this->get_login() );
			exit;
		}
	}

	/**
	 * @internal
	 *
	 * @param string $the_content
	 *
	 * @return string
	 */
	public function _filter_theme_student_account( $the_content ) {

		$courses = $this->get_courses_data();

		if ( empty( $courses ) ) {
			return $the_content;
		}

		ob_start();
		$this->student_account_form->render( array( 'courses' => $courses ) );
		$html = ob_get_clean();

		return $the_content . $html;
	}

	/**
	 * @internal
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function _render_student_form( $data ) {
		$courses = $data['data']['courses'];

		echo '<input type="hidden" name="user-id" value="' . $this->current_user->id() . '">';
		foreach ( $courses as $course ) {
			$course['name'] = 'courses[]';
			echo $this->render_view( 'items/account-course-item', $course );
		}

		$data['submit']['value'] = __( 'Remove courses', 'fw' );

		return $data;
	}

	/**
	 * @internal
	 *
	 * @param array $errors
	 *
	 * @return array
	 */
	public function _validate_student_form( $errors ) {

		$id = FW_Request::POST( 'user-id' );

		if ( $id != $this->current_user->id() ) {
			$errors['invalid-id'] = __( "Couldn't process the request", 'fw' );
		}

		return $errors;
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function _save_student_form( $data ) {
		$data['redirect'] = fw_current_url();

		$courses = FW_Request::POST( 'courses' );

		if ( empty( $courses ) ) {
			FW_Flash_Messages::add( $this->get_name() . '-account-no-courses', __( 'No courses was deleted', 'fw' ) );

			return $data;
		}

		foreach ( $courses as $course ) {
			$id = (int) $course;
			$this->current_user->remove_course( $id );
		}

		FW_Flash_Messages::add( $this->get_name() . '-account-no-courses', __( 'Courses successfully removed', 'fw' ) );

		return $data;
	}

	private function add_actions() {
		add_action( 'init', array( $this, '_action_define_current_user' ) );
	}

	private function admin_active_actions() {
		add_action( 'before_delete_post', array( $this, '_action_admin_course_deleted' ), 9, 1 );
	}

	private function admin_active_filters() {

	}

	private function theme_active_actions() {
		add_action( 'wp', array( $this, '_action_theme_define_pass_lesson_method' ), 10 );
		add_action( 'fw_ext_learning_student_took_course', array( $this, '_action_theme_apply_course_user' ), 5 );
		add_action(
			'fw_ext_learning_student_completed_course',
			array( $this, '_action_theme_complete_course_user' ),
			5
		);
		add_action( 'fw_ext_learning_lesson_passed', array( $this, '_action_theme_user_completed_lesson' ), 5 );

		if ( $this->save_meta( 'lesson-quiz' ) ) {
			add_action( 'fw_ext_learning_quiz_form_process', array( $this, '_action_theme_save_quiz' ), 10 );
		}
	}

	private function theme_active_filters() {
		add_filter( 'fw_ext_users_account_content', array( $this, '_filter_theme_student_account' ) );
	}

	private function admin_non_active_actions() {
		add_action( 'before_delete_post', array( $this, '_action_admin_course_deleted' ), 9, 1 );
	}

	private function admin_non_active_filters() {

	}

	private function theme_non_active_actions() {
		add_action( 'wp', array( $this, '_action_theme_redirect_non_logged_in_user' ), 10 );
	}

	private function theme_non_active_filters() {

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

	private function set_current_user() {
		$user_id = (int) get_current_user_id();

		if ( $user_id == 0 ) {
			return;
		}

		if ( ! user_can( $user_id, 'read' ) ) {
			return;
		}

		$this->current_user = new FW_Learning_Student( $user_id );
	}

	private function add_flash( $message ) {
		if ( $this->get_config( 'enable-flash-messages' ) === true ) {
			FW_Flash_Messages::add( $this->get_name() . '-flash', $message );
		}
	}

	private function save_meta( $meta ) {
		if ( isset( $this->save_meta[ $meta ] ) && $this->save_meta[ $meta ] === true ) {
			return true;
		}

		return false;
	}
}