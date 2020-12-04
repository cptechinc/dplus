<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$itm = $modules->get('Itm');
	$itemID = $values->text('itemID');

	if (!$itm->item_exists($itemID)) {
		$session->redirect($page->itmURL($itemID), $http301 = false);
	}

	if (!$values->action) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('items/itm/bread-crumbs.twig', ['page' => $page, 'page_itm' => $page->parent, 'input' => $input]);

		if ($session->response_itm) {
			$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_itm]);
		}
	}
