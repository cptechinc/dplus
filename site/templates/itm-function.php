<?php
	$itmp = $modules->get('Itmp');
	if ($user->has_function('itm') && $itmp->is_user_allowed_template($user, $page->pw_template)) {
		include("./dplus-function.php");
	} else {
		$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: ITM $page->name"]);
		include __DIR__ . "/basic-page.php";
	}
