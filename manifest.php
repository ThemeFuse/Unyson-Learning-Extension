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
$manifest['version']     = '1.0.12';
$manifest['display']     = true;
$manifest['standalone']  = true;

$manifest['github_update'] = 'ThemeFuse/Unyson-Learning-Extension';

$manifest['github_repo'] = 'https://github.com/ThemeFuse/Unyson-Learning-Extension';
$manifest['uri'] = 'http://manual.unyson.io/en/latest/extension/learning/index.html#content';
$manifest['author'] = 'ThemeFuse';
$manifest['author_uri'] = 'http://themefuse.com/';
