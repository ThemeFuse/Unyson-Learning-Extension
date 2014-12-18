<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

abstract class FW_Learning_Complete_Course {

	/**
	 * @var FW_Extension_Learning_Apply_Course
	 */
	protected $parent = null;

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
	 * Return the method will be used to complete the course.
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
		$this->parent->set_complete_course_method( $this );
	}

	/**
	 * Confirm that the course was completed
	 *
	 * @param int $course_id
	 */
	public final function complete_course( $course_id ) {
		do_action( 'fw_ext_learning_student_completed_course', $course_id );
	}

	/**
	 * Check if a specific course post has complete method
	 *
	 * @param int $course_id
	 *
	 * @return bool
	 */
	public function has_method( $course_id ) {
		return true;
	}
}

class FW_Learning_Complete_Course_Default_Method extends FW_Learning_Complete_Course {

	/**
	 * {@inheritdoc}
	 */
	public function _init() {
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_method( $course_id ) {
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_priority() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_method( $course_id ) {
		return false;
	}
}

new FW_Learning_Complete_Course_Default_Method;