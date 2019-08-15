<?php
	use CustomerQuery, Customer;

	$module_useractions = $modules->get('FilterUserActions');

	if ($input->requestMethod('POST')) {

	} else {
		if ($input->get->id) {
			$id = $input->get->text('id');
			$query = UseractionsQuery::create();
			$action = $query->findOneById($id);
			$page->body .= $config->twig->render("user-actions/$action->actiontype.twig", ['page' => $page, 'module_useractions' => $module_useractions, $action->actiontype => $action]);
		} else {
			$query = $module_useractions->get_actionsquery($input);
			$actions = $query->find();
			$page->body .= $config->twig->render("user-actions/actions-list.twig", ['page' => $page, 'module_useractions' => $module_useractions, 'actions' => $actions]);
		}
	}

	include __DIR__ . "/basic-page.php";
