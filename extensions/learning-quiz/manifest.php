<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$manifest = array();

$manifest['standalone'] = true;

$manifest['requirements'] = array(
	'extensions' => array(
		'builder'               => array(),
		'learning-apply-course' => array(),
	)
);
