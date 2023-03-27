<?php 
	use ProcessWire\Config;
	/**
	 * _init.config.php
	 * Initialize necessary Config Properties, if they are not defined
	 * 
	 */

	 // Used for Hiding Functions / Menus
	if ($config->has('hideFunctions') === false) {
		$config->hideFunctions = new Config();
		$config->hideFunctions->dev = ['map'];
		$config->hideFunctions->cmp = ['mar'];
	}