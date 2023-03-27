<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	
	use Dplus\Session\UserMenuPermissions;
	use Controllers\Dplus\Menu;

	$parents = $page->parents("template=dplus-menu");

	foreach ($parents as $parent) {
		$code = $parent->dplus_function ? $parent->dplus_function : $parent->dplus_permission;

		if (UserMenuPermissions::instance()->canAccess($code) === false) {
			$page->body .= Menu::notPermittedDisplay();
			include __DIR__ . "/basic-page.php";
		}
	}

	$page->pw_template = 'formatter-screen';
	include('./dplus-function.php');
