<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$manifest = array();

$manifest['name']        = __( 'Learning', 'fw' );
$manifest['description'] = __(
	'This extension adds a Learning module to your theme.'
	.' Using this extension you can add courses, lessons and tests for your users to take.',
	'fw'
);
$manifest['version']     = '1.0.10';
$manifest['display']     = true;
$manifest['standalone']  = true;

$manifest['github_update'] = 'ThemeFuse/Unyson-Learning-Extension';
