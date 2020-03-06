<?php
	$module_useractions      = $modules->get('FilterUserActions');
	$module_useractions_crud = $modules->get('UserActionsCrud');
	$html = $modules->get('HtmlWriter');

	$query = $module_useractions->get_actionsquery($input);
	$actions = $query->find();
	$custID = $input->get->text('custID');
	$query_customer = CustomerQuery::create();
	$customer = $query_customer->findOneByCustid($custID);
	$page->title = "Viewing Actions for $customer->name";

	$page->body .= $config->twig->render("user-actions/actions-list.twig", ['page' => $page, 'module_useractions' => $module_useractions, 'actions' => $actions]);

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
