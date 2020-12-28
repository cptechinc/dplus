<?php
	$itmp = $modules->get('Itmp');
	$page->title = "Item Maintenance Permissions";

	if ($values->action) {
		$itmp->process_input($input);
		$session->redirect($page->itmpURL($values->text('loginID')), $http301);
	}

	if ($itmp->has_response()) {
		$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $itmp->response()]);
	}

	$page->body .= $config->twig->render('min/itmp/page.twig', ['page' => $page, 'itmp' => $itmp]);
	$page->js   .= $config->twig->render('min/itmp/js.twig', ['page' => $page, 'itmp' => $itmp]);
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	include __DIR__ . "/basic-page.php";
