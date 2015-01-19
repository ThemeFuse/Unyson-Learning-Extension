<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Learning_Student {

	/**
	 * @var WP_User
	 */
	private $user_data = null;

	/**
	 * @var FW_Extension_Learning
	 */
	private $learning = null;

	/**
	 * @var FW_Extension_Learning_Student
	 */
	private $learning_student = null;

	private $save_meta = array(
		'courses'        => false,
		'courses-status' => false,
		'lessons'        => false,
		'lessons-status' => false,
	);

	/**
	 * @param int $user_id
	 */
	public function __construct( $user_id ) {
		$this->user_data        = get_userdata( $user_id );
		$this->learning         = fw()->extensions->get( 'learning' );
		$this->learning_student = fw()->extensions->get( 'learning-student' );

		$this->save_meta = array_merge(
			$this->save_meta,
			apply_filters( 'fw_ext_learning_student_save_meta', $this->save_meta )
		);
	}

	/**
	 * @return int
	 */
	public function id() {
		if ( empty( $this->user_data ) ) {
			return 0;
		}

		return $this->user_data->ID;
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
	public function has_completed( $id = null ) {
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
	 * Checks if the lesson is the current user active lesson of the course
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public function is_studying( $id = null ) {
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

		if ( ! isset( $data['status'] ) || $data['status'] != 'open' ) {
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
	public function has_passed( $id = null ) {
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
	 * Attaches new course for the current user or updates the course data
	 * In case the course doesn't not exist, it is added, if it exists already, course data is updated.
	 *
	 * @param int $course_id
	 * @param array $data
	 *
	 * @return bool, true in case the course was added/updated, false in case the course ID is not valid.
	 */
	public function add_course_data( $course_id, array $data ) {

		if ( ! $this->learning->is_course( $course_id ) ) {
			return false;
		}

		if ( empty( $data ) ) {
			return false;
		}

		$user_data = $this->get_user_data();

		$courses_data = ( empty( $user_data['courses'] ) ) ? array() : $user_data['courses'];

		$exist = false;

		if ( empty( $courses_data ) ) {
			$courses_data               = array();
			$courses_data[ $course_id ] = array();
		} else {
			if ( ! isset( $courses_data[ $course_id ] ) || ! is_array( $courses_data[ $course_id ] ) ) {
				$courses_data[ $course_id ] = array();
			} else {
				$exist = true;
			}
		}

		$courses_data[ $course_id ] = array_merge( $courses_data[ $course_id ], $data );

		$course = get_post( $course_id );

		$courses_data[ $course_id ]['id']     = $course_id;
		$courses_data[ $course_id ]['title']  = $course->post_title;
		$courses_data[ $course_id ]['author'] = $course->post_author;

		$user_data['courses'] = $courses_data;

		$response = $this->add_user_data( $user_data );

		if ( $response == true ) {
			do_action( 'fw_ext_learning_student_update_student_courses_data', $this->user_data->ID,
				$courses_data[ $course_id ] );

			if ( isset( $this->save_meta['courses'] ) && $this->save_meta['courses'] == true ) {
				fw_update_user_meta( $this->id(), 'learning-student-courses', $course_id );
			}

			if (
				isset( $this->save_meta['courses-status'] )
				&& $this->save_meta['courses-status'] == true
				&& isset( $courses_data[ $course_id ]['status'] )
			) {
				fw_update_user_meta( $this->id(), 'learning-student-courses-status-' . $course_id,
					$courses_data[ $course_id ]['status'] );
			}

			return true;
		}

		return false;
	}

	/**
	 * @param int $course_id
	 */
	public function remove_course( $course_id ) {
		if ( ! $this->learning->is_course( $course_id ) ) {
			return;
		}

		$course_data = $this->get_courses_data( $course_id );

		if ( empty( $course_data ) ) {
			return;
		}

		$data = $this->get_user_data();

		unset( $data['courses'][ $course_id ] );
		do_action( 'fw_ext_learning_student_remove_course', $this->user_data->ID, $course_id );

		if ( ! empty( $data['lessons'] ) ) {
			foreach ( $data['lessons'] as $id => $lesson ) {
				//fixme: May be to use remove_lesson()
				if ( $lesson['course-id'] == $course_id ) {
					unset( $data['lessons'][ $id ] );
				}
			}
		}

		$this->add_user_data( $data );
	}

	/**
	 * @param int $lesson_id
	 */
	public function remove_lesson( $lesson_id ) {
		if ( ! $this->learning->is_lesson( $lesson_id ) ) {
			return;
		}

		$lesson_data = $this->get_lessons_data( $lesson_id );
		if ( empty( $lesson_data ) ) {
			return;
		}

		$data = $this->get_user_data();
		unset( $data['lessons'][ $lesson_id ] );
		do_action( 'fw_ext_learning_student_remove_lesson', $this->user_data->ID, $lesson_id );

		$this->add_user_data( $data );
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
		if ( ! $this->learning->is_lesson( $lesson_id ) ) {
			return false;
		}

		if ( empty( $data ) ) {
			return false;
		}

		$user_data = $this->get_user_data();

		$lessons_data = ( empty( $user_data['lessons'] ) ) ? array() : $user_data['lessons'];

		if ( empty( $lessons_data ) ) {
			$lessons_data               = array();
			$lessons_data[ $lesson_id ] = array();
		} else {
			if ( ! isset( $lessons_data[ $lesson_id ] ) || ! is_array( $lessons_data[ $lesson_id ] ) ) {
				$lessons_data[ $lesson_id ] = array();
			}
		}

		$lessons_data[ $lesson_id ] = array_merge( $lessons_data[ $lesson_id ], $data );

		$lesson = get_post( $lesson_id );

		$lessons_data[ $lesson_id ]['id']        = $lesson_id;
		$lessons_data[ $lesson_id ]['title']     = $lesson->post_title;
		$lessons_data[ $lesson_id ]['author']    = $lesson->post_author;
		$lessons_data[ $lesson_id ]['course-id'] = $lesson->post_parent;

		$user_data['lessons'] = $lessons_data;

		$response = $this->add_user_data( $user_data );

		if ( $response === true ) {
			do_action( 'fw_ext_learning_student_update_student_lessons_data', $this->user_data->ID,
				$lessons_data[ $lesson_id ] );

			if ( isset( $this->save_meta['lessons'] ) && $this->save_meta['lessons'] == true ) {
				fw_update_user_meta( $this->id(), 'learning-student-lessons', $lesson_id );
			}

			if (
				isset( $this->save_meta['lessons-status'] )
				&& $this->save_meta['lessons-status'] == true
				&& isset( $lessons_data[ $lesson_id ]['status'] )
			) {
				fw_update_user_meta( $this->id(), 'learning-student-lessons-status-' . $lesson_id,
					$lessons_data[ $lesson_id ]['status'] );
			}

			return true;
		}

		return false;
	}

	/**
	 * @param int $id
	 * @param FW_Learning_Grading_Quiz_Review $data
	 *
	 * @return bool
	 */
	public function add_quiz_data( $id, FW_Learning_Grading_Quiz_Review $data ) {
		$learning_quiz = fw_ext( 'learning-quiz' );

		if ( empty( $learning_quiz ) ) {
			return false;
		}

		$lesson = $this->get_lessons_data( $id );

		if ( empty( $lesson ) ) {
			return false;
		}

		$lesson['quiz'] = $data;

		return $this->add_lesson_data( $id, $lesson );
	}

	/**
	 * Return all student meta
	 *
	 * @return array|null
	 */
	public function get_user_data() {
		if ( empty( $this->user_data ) ) {
			return null;
		}
		return fw_get_db_extension_user_data( $this->user_data->ID, $this->learning_student->get_name() );
	}

	/**
	 * Return all data about user courses
	 * $ids parameter can be empty to return all courses data, or an array of courses ids, or and single id
	 *
	 * @param int|array[int] $ids
	 *
	 * @return array;
	 */
	public function get_courses_data( $ids = null ) {

		$data = $this->get_user_data();

		if ( empty( $data ) || ! isset( $data['courses'] ) ) {
			if ( is_array( $ids ) ) {
				return array();
			} else {
				return null;
			}
		}

		if ( is_null( $ids ) ) {
			return $data['courses'];
		}

		if ( is_array( $ids ) ) {

			if ( empty( $ids ) ) {
				return array();
			}

			$courses = array();
			foreach ( $ids as $id ) {
				if ( isset( $data[ (int) $id ] ) ) {
					$courses[ (int) $id ] = $data[ (int) $id ];
				}
			}

			return $courses;
		}

		if ( isset( $data['courses'][ (int) $ids ] ) ) {
			return $data['courses'][ (int) $ids ];
		}

		return null;
	}

	/**
	 * Return all data about user lessons
	 * $ids parameter can be empty to return all lessons data, or an array of lessons ids, or and single id
	 *
	 * @param int|array[int] $ids
	 *
	 * @return array;
	 */
	public function get_lessons_data( $ids = null ) {

		$data = $this->get_user_data();

		if ( empty( $data ) || ! isset( $data['lessons'] ) ) {
			if ( is_array( $ids ) ) {
				return array();
			} else {
				return null;
			}
		}

		if ( is_null( $ids ) ) {
			return $data['lessons'];
		}

		if ( is_array( $ids ) ) {

			if ( empty( $ids ) ) {
				return array();
			}

			$lessons = array();
			foreach ( $ids as $id ) {
				if ( isset( $data[ (int) $id ] ) ) {
					$lessons[ (int) $id ] = $data[ (int) $id ];
				}
			}

			return $lessons;
		}

		if ( isset( $data['lessons'][ (int) $ids ] ) ) {
			return $data['lessons'][ (int) $ids ];
		}

		return null;
	}

	/**
	 * @param int $id
	 *
	 * @return null|FW_Learning_Grading_Quiz_Review
	 */
	public function get_quiz_data( $id ) {
		$learning_quiz = fw_ext( 'learning-quiz' );

		if ( empty( $learning_quiz ) || empty( $id ) || ! $this->learning->is_lesson( $id ) ) {
			return null;
		}

		$data = $this->get_lessons_data( $id );

		if ( empty( $data ) || empty( $data['quiz'] ) ) {
			return null;
		}

		return $data['quiz'];
	}

	/**
	 * @param int $id
	 *
	 * @return null|string
	 */
	public function get_quiz_status( $id ) {
		$data = $this->get_quiz_data( $id );
		if ( empty( $data ) ) {
			return null;
		}

		return $data->get_status();
	}

	/**
	 * @param array $data
	 *
	 * @return bool|int
	 */
	private function add_user_data( array $data ) {
		return fw_set_db_extension_user_data( $this->user_data->ID, $this->learning_student->get_name(), $data );
	}
}