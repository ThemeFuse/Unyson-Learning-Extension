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

	/**
	 * @internal
	 *
	 * @param int $user_id
	 */
	public function __construct( $user_id ) {
		$this->user_data        = get_userdata( $user_id );
		$this->learning         = fw()->extensions->get( 'learning' );
		$this->learning_student = fw()->extensions->get( 'learning-student' );
	}

	/**
	 * @return int
	 */
	public function id() {
		return $this->user_data->ID;
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

		if ( $response === true ) {
			if ( ! $exist ) {
				fw_add_user_meta( $this->id(), $this->learning_student->get_name() . '-course-id', $course_id );
			}
			do_action( 'fw_ext_learning_student_update_student_courses_data', $this->user_data->ID,
				$courses_data[ $course_id ] );

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
		fw_delete_user_meta( $this->user_data->ID, $this->learning_student->get_name() . '-course-id', $course_id );

		if ( ! empty( $data['lessons'] ) ) {
			foreach ( $data['lessons'] as $id => $lesson ) {
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

			return true;
		}

		return false;
	}

	/**
	 * Return all student meta
	 *
	 * @return array|null
	 */
	public function get_user_data() {
		{
			//$this->add_user_data(array());
			//delete_user_meta( $this->user_data->ID, $this->learning_student->get_name() . '-course-id' );
			//TODO: Remove this
			global $asfdasdfuser;

			if ( $asfdasdfuser != true ) {
				fw_print( fw_get_db_extension_user_data( $this->user_data->ID, $this->learning_student->get_name() ) );
				$asfdasdfuser = true;
			}
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
	 * @param array $data
	 *
	 * @return bool|int
	 */
	private function add_user_data( array $data ) {
		return fw_set_db_extension_user_data( $this->user_data->ID, $this->learning_student->get_name(), $data );
	}
}