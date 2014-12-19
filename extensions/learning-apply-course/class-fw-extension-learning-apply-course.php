<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_Learning_Apply_Course extends FW_Extension {

	/**
	 * @var FW_Learning_Pass_Lesson
	 */
	private $lesson_pass_method = null;

	/**
	 * @var FW_Learning_Take_Lesson
	 */
	private $lesson_take_method = null;

	/**
	 * @var FW_Learning_Take_Course
	 */
	private $take_course_method = null;

	/**
	 * @var FW_Learning_Complete_Course
	 */
	private $complete_course_method = null;

	/**
	 * @var FW_Extension_Learning
	 */
	private $learning = null;

	/**
	 * @internal
	 */
	public function _init() {
		$this->learning = fw()->extensions->get( 'learning' );
	}

	/**
	 * @param FW_Learning_Pass_Lesson $method
	 */
	public function set_lesson_pass_method( FW_Learning_Pass_Lesson $method ) {

		//If the current method is not set or has low priority automatically set the new method
		if ( empty( $this->lesson_pass_method ) || ( $this->lesson_pass_method->get_priority() == false ) ) {
			$this->lesson_pass_method = $method;

			return;
		}

		//If current method has high priority, need to check the priority of the new method
		if ( $method->get_priority() == true ) {
			$this->lesson_pass_method = $method;

			return;
		}
	}

	/**
	 * Return the lesson pass method
	 *
	 * @param int $lesson_id
	 *
	 * @return string
	 */
	public function get_lesson_pass_method( $lesson_id ) {
		if ( ! $this->learning->is_lesson( $lesson_id ) ) {
			return '';
		}

		if ( empty ( $this->lesson_pass_method ) ) {
			return '';
		}

		return $this->lesson_pass_method->get_method( $lesson_id );
	}

	/**
	 * @param FW_Learning_Take_Lesson $method
	 */
	public function set_lesson_take_method( FW_Learning_Take_Lesson $method ) {

		//If the current method is not set or has low priority automatically set the new method
		if ( empty( $this->lesson_take_method ) || ( $this->lesson_take_method->get_priority() == false ) ) {
			$this->lesson_take_method = $method;

			return;
		}

		//If current method has high priority, need to check the priority of the new method
		if ( $method->get_priority() == true ) {
			$this->lesson_pass_method = $method;

			return;
		}
	}

	/**
	 * Return the lesson take method
	 *
	 * @param int $lesson_id
	 *
	 * @return string
	 */
	public function get_lesson_take_method( $lesson_id ) {
		if ( ! $this->learning->is_lesson( $lesson_id ) ) {
			return '';
		}

		if ( empty ( $this->lesson_take_method ) ) {
			return '';
		}

		return $this->lesson_take_method->get_method( $lesson_id );
	}

	/**
	 * @param FW_Learning_Take_Course $method
	 */
	public function set_take_course_method( FW_Learning_Take_Course $method ) {

		//If the current method is not set or has low priority automatically set the new method
		if ( empty( $this->take_course_method ) || ( $this->take_course_method->get_priority() == false ) ) {
			$this->take_course_method = $method;

			return;
		}

		//If current method has high priority, need to check the priority of the new method
		if ( $method->get_priority() == true ) {
			$this->take_course_method = $method;

			return;
		}
	}

	/**
	 * @param int $course_id
	 *
	 * @return string
	 */
	public function get_take_course_method( $course_id ) {
		if ( ! $this->learning->is_course( $course_id ) ) {
			return '';
		}

		if ( empty ( $this->take_course_method ) ) {
			return '';
		}

		return $this->take_course_method->get_method( $course_id );
	}

	/**
	 * @param FW_Learning_Complete_Course $method
	 */
	public function set_complete_course_method( FW_Learning_Complete_Course $method ) {

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
	 * @param int $course_id
	 *
	 * @return string
	 */
	public function get_complete_course_method( $course_id ) {
		if ( ! $this->learning->is_course( $course_id ) ) {
			return '';
		}

		if ( empty ( $this->complete_course_method ) ) {
			return '';
		}

		return $this->complete_course_method->get_method( $course_id );
	}

	/**
	 * Checks if the current lesson has an pass method
	 *
	 * @param int $lesson_id
	 *
	 * @return bool
	 */
	public function has_lesson_pass_method( $lesson_id = null ) {

		if ( is_null( $lesson_id ) && isset( $GLOBALS['post'] ) ) {
			$lesson_id = $GLOBALS['post']->ID;
		}

		if ( empty( $this->lesson_pass_method ) || ! $this->lesson_pass_method->has_method( $lesson_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the current lesson has an pass method
	 *
	 * @param int $lesson_id
	 *
	 * @return bool
	 */
	public function has_lesson_take_method( $lesson_id = null ) {

		if ( is_null( $lesson_id ) && isset( $GLOBALS['post'] ) ) {
			$lesson_id = $GLOBALS['post']->ID;
		}

		if ( empty( $this->lesson_take_method ) || ! $this->lesson_take_method->has_method( $lesson_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the current course has an take method
	 *
	 * @param int $course_id
	 *
	 * @return bool
	 */
	public function has_course_take_method( $course_id = null ) {

		if ( is_null( $course_id ) && isset( $GLOBALS['post'] ) ) {
			$course_id = $GLOBALS['post']->ID;
		}

		if ( empty( $this->take_course_method ) ) {
			return false;
		}

		return $this->take_course_method->has_method( $course_id );
	}

	/**
	 * Checks if the current course has an complete method
	 *
	 * @param int $course_id
	 *
	 * @return bool
	 */
	public function has_course_complete_method( $course_id = null ) {
		if ( is_null( $course_id ) && isset( $GLOBALS['post'] ) ) {
			$course_id = $GLOBALS['post']->ID;
		}

		if ( empty( $this->complete_course_method ) ) {
			return false;
		}

		return $this->complete_course_method->has_method( $course_id );
	}

	/**
	 * Initialize take course action
	 *
	 * @param int $course_id
	 */
	public function take_course( $course_id ) {
		if ( $this->take_course_method->has_method( $course_id ) ) {
			$this->take_course_method->take_course( $course_id );
		}
	}

	/**
	 * Initialize complete course action
	 *
	 * @param int $course_id
	 */
	public function complete_course( $course_id ) {
		if ( $this->complete_course_method->has_method( $course_id ) ) {
			$this->complete_course_method->complete_course( $course_id );
		}
	}

	/**
	 * Initialize take lesson action
	 *
	 * @param int $lesson_id
	 */
	public function take_lesson( $lesson_id ) {
		if ( $this->lesson_take_method->has_method( $lesson_id ) ) {
			$this->lesson_take_method->take_lesson( $lesson_id );
		}
	}

	/**
	 * Initialize pass lesson action
	 *
	 * @param int $lesson_id
	 */
	public function pass_lesson( $lesson_id ) {
		if ( $this->lesson_pass_method->has_method( $lesson_id ) ) {
			$this->lesson_pass_method->pass_lesson( $lesson_id );
		}
	}
}