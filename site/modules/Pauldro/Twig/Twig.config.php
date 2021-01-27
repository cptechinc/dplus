<?php

$rootPath = self::wire('config')->paths->root;
$config = array(
	'path' => array(
		'type'  => 'text',
		'label' => 'File Path to Twig',
		'description' => "File Path from Root",
		'minlength' => 1,
		'required' => true,
		'value' => 'site/templates/twig/',
		'notes' => "Path from " . $rootPath
	),
	'cachepath' => array(
		'type'  => 'text',
		'label' => 'File Path to Twig Cache',
		'description' => "File Path from Root",
		'minlength' => 1,
		'required' => true,
		'value' => 'site/templates/twig/cache',
		'notes' => "Path from " . $rootPath
	),
	'debug' => array(
		'type'  => 'checkbox',
		'label' => 'Debug?',
		'description' => "Debug mode for TWig",
		'minlength' => 1,
		'required' => false,
		'value' => 1,
		'notes' => ""
	),
);