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
	 * @var FW_Extension_Learning_Apply_Course
	 */
	private $apply_to_course = null;

	/**
	 * @var FW_Learning_Student_Take_Course_Method
	 */
	private $take_course_method = null;

	/**
	 * @var FW_Learning_Student_Pass_Lesson
	 */
	private $pass_lesson_method = null;

	/**
	 * @var FW_Form
	 */
	private $student_account_form = null;

	/**
	 * @internal
	 */
	public function _init() {

		$user_require = $this->get_config( 'user-require' );

		if ( $user_require === false ) {
			return;
		}

		$this->learning           = fw()->extensions->get( 'learning' );
		$this->apply_to_course    = fw()->extensions->get( 'learning-apply-course' );
		$this->pass_lesson_method = new FW_Learning_Student_Pass_Lesson();
		$this->take_course_method = new FW_Learning_Student_Take_Course_Method();

		$this->define_role();
		$this->register_role();
		$this->add_actions();
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
		return $this->current_user->is_student();
	}

	/**
	 * Check if the user is subscribed to the current course
	 *
	 * @param int $id - course id
	 *
	 * @return bool
	 */
	public function is_subscribed( $id = null ) {
		return $this->current_user->is_subscribed( $id );
	}

	/**
	 * Checks if the user completed the course
	 *
	 * @param int $id - course id
	 *
	 * @return bool
	 */
	public function has_completed( $id = null ) {
		return $this->current_user->has_completed( $id );
	}

	/**
	 * Checks if the lesson is the current user active lesson of the course
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public function is_studying( $id = null ) {
		return $this->current_user->is_studying( $id );
	}

	/**
	 * Checks if the user passed the lesson
	 *
	 * @param int $id - course id
	 *
	 * @return bool
	 */
	public function has_passed( $id = null ) {
		return $this->current_user->has_passed( $id );
	}

	/**
	 * @param int $id
	 *
	 * @return bool
	 */
	public function is_author( $id ) {
		return $this->current_user->is_author( $id );
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
	 * @param $id
	 * @param FW_Learning_Grading_Quiz_Review $data
	 *
	 * @return bool
	 */
	public function add_quiz_data( $id, FW_Learning_Grading_Quiz_Review $data ) {
		return $this->current_user->add_quiz_data( $id, $data );
	}

	/**
	 * @param $id
	 *
	 * @return null|FW_Learning_Grading_Quiz_Review
	 */
	public function get_quiz_data( $id ) {
		return $this->current_user->get_quiz_data( $id );
	}

	/**
	 * @param $id
	 *
	 * @return null|string
	 */
	public function get_quiz_status( $id ) {
		return $this->current_user->get_quiz_status( $id );
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
	public function _action_theme_student_took_lesson( $lesson_id ) {
		if ( ! $this->learning->is_lesson( $lesson_id ) ) {
			return;
		}
		$course = $this->learning->get_lesson_course( $lesson_id );

		if ( empty( $course ) || ! $this->is_subscribed( $course->ID ) ) {
			return;
		}

		$this->add_lesson_data( $lesson_id, array( 'status' => 'open' ) );
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

			if ( ! $this->learning->is_last_lesson( $lesson_id ) ) {
				$this->apply_to_course->take_lesson( $this->learning->get_next_lesson( $lesson_id )->ID );
			}
		}
	}

	/**
	 * @internal
	 *
	 * @param int $lesson_id
	 */
	public function _action_theme_complete_course_method( $lesson_id ) {

		$course = $this->learning->get_lesson_course( $lesson_id );

		if (
			empty( $course )
			|| ! $this->learning->is_course( $course->ID )
			|| ! $this->check_if_user_completed_the_course( $course->ID )
		) {
			return;
		}

		$this->apply_to_course->complete_course( $course->ID );
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
		add_action( 'fw_ext_learning_student_completed_lesson', array( $this, '_action_theme_complete_course_method' ),
			5 );
		add_action( 'fw_ext_learning_completed_course', array( $this, '_action_theme_complete_course_user' ), 5 );
		add_action( 'fw_ext_learning_lesson_taken', array( $this, '_action_theme_student_took_lesson' ), 5 );
		add_action( 'fw_ext_learning_lesson_passed', array( $this, '_action_theme_user_completed_lesson' ), 5 );
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
		$this->current_user = new FW_Learning_Student( $user_id );
	}

	private function add_flash( $message ) {
		if ( $this->get_config( 'enable-flash-messages' ) === true ) {
			FW_Flash_Messages::add( $this->get_name() . '-flash', $message );
		}
	}

	private function check_if_user_completed_the_course( $course_id ) {
		$lessons = $this->learning->get_course_lessons( $course_id );

		if ( empty( $lessons ) ) {
			return true;
		}

		foreach ( $lessons as $lesson ) {
			if ( ! $this->has_passed( $lesson->ID ) ) {
				return false;
			}
		}

		return true;
	}
}