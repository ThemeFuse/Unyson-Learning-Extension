<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

abstract class FW_Learning_Take_Course {

	/**
	 * @var FW_Extension_Learning_Apply_Course
	 */
	private $parent = null;

	/**
	 * Sometimes the method may not confirm at init that is ready or not, or it will be used or not.
	 * In this case in _init() method set the $is_ready member to false.
	 * If the method will need to have to register latter, you have to use the register_method() method manually;
	 *
	 * @var bool
	 */
	protected $is_ready = true;

	/**
	 * Used in case the class needs to initialize data;
	 *
	 * @return void
	 */
	abstract public function _init();

	/**
	 * Return the method will be used to take the course.
	 *
	 * @param int $course_id
	 *
	 * @return string
	 */
	abstract public function get_method( $course_id );

	/**
	 * Set the priority. Priority is used to understand the importance of the take method, and if there are other method,
	 * to overwrite it or not
	 *
	 * true - priority is important
	 * false - priority is low
	 *
	 * Note: In case you'll set priority true(high), doesn't mean that this method will be used, this will depend on the
	 * order the method was initialised, the las initialised will be used.
	 *
	 * @return bool
	 */
	abstract public function get_priority();

	final public function __construct() {
		$this->parent = fw()->extensions->get( 'learning-apply-course' );
		$this->_init();

		if ( $this->is_ready === true ) {
			$this->register_method();
		}
	}

	final public function register_method() {
		$this->parent->set_take_course_method( $this );
	}

	/**
	 * Confirm that the course was took
	 *
	 * @param int $course_id
	 */
	public final function take_course( $course_id ) {
		do_action( 'fw_ext_learning_student_took_course', $course_id );
	}
}

class FW_Learning_Take_Course_Default_Method extends FW_Learning_Take_Course {

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
	 * @param int $course_id
	 *
	 * @return string
	 */
	public function get_method( $course_id ) {
		if ( $this->learning->is_course() ) {
			$this->take_course( $course_id );
		}

		return '';
	}

	/**
	 * @return bool
	 */
	public function get_priority() {
		return false;
	}
}

new FW_Learning_Take_Course_Default_Method;