<?php
	// Dplus Classes
	use Dplus\Session\UserMenuPermissions;

	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);

	$pageMenuPerm = $page->dplus_function ? $page->dplus_function : $page->dplus_permission;

	foreach ($page->parents('template=dplus-menu|warehouse-menu') as $parent) {
		$code = $parent->dplus_function ? $parent->dplus_function : $parent->dplus_permission;

		if (empty($code) === false && UserMenuPermissions::instance()->canAccess($code) === false) {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: $pageMenuPerm"]);
			include('./basic-page.php');
			return false;
		}
	}

	if (empty($pageMenuPerm) === false && UserMenuPermissions::instance()->canAccess($pageMenuPerm ) === false) {
		$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: $pageMenuPerm"]);
		include('./basic-page.php');
		return false;
	}


	if (file_exists(__DIR__."/whse-print-item-label-$config->company.php")) {
		include(__DIR__."/whse-print-item-label-$config->company.php");
	} else {
		include(__DIR__."/whse-print-item-label-default.php");
	}
