<?php namespace ProcessWire;

/**
 * ProcessHello.info.php
 *
 * Return information about this module.
 *
 * If preferred, you can use a getModuleInfo() method in your module file,
 * or you can use a ModuleName.info.json file (if you prefer JSON definition).
 *
 */
$info = array(
	'title' => __('File Hasher'),
	'version' => 1,
	'summary' => __("Returns Hashes for files"),
	'autoload' => true,
	'singular' => true,
	'author' => 'pauldro',
	'icon' => 'plug'
);
