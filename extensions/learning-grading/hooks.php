<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

function _filter_fw_ext_learning_grading_user_meta_to_save( $meta ) {
	$meta['courses'] = true;
	$meta['courses-status'] = true;
	$meta['lessons'] = true;
	$meta['lessons-status'] = true;
	return $meta;
}

add_filter( 'fw_ext_learning_student_save_meta', '_filter_fw_ext_learning_grading_user_meta_to_save' );