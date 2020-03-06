<?php
	$module_useractions      = $modules->get('FilterUserActions');
	$module_useractions_crud = $modules->get('UserActionsCrud');
	$html = $modules->get('HtmlWriter');

	if ($input->requestMethod('POST')) {
		$response = $module_useractions_crud->process_input_create($input);
		$session->response_crud = $response;
		if ($session->response_crud['actionID']) {
			$page->fullURL->path = $pages->get('pw_template=user-actions')->url;
			$page->fullURL->query->set('id', $session->response_crud['actionID']);
		}
		$session->redirect($page->fullURL->getUrl());
	} else {
		$action = new Useractions();

		if ($input->get->type) {
			$action = $module_useractions_crud->create_action_blank_input($input);
			$page->title = "Creating $action->actiontype for";

			if ($action->has_customerlink()) {
				$query_customer = CustomerQuery::create();
				$customer = $query_customer->findOneByCustid($action->customerlink);

				$page->title .= " Customer $customer->name ($action->customerlink)";

				if ($action->has_shiptolink()) {
					$page->title .= " Shipto $action->shiptolink";
				}
			}

			if ($action->has_quotelink()) {
				$page->title .= " Quote # $action->quotelink";
			}

			if ($action->has_salesorderlink()) {
				$page->title .= " Order # $action->salesorderlink";
			}

			if ($action->has_vendorlink()) {
				$page->title .= " Vendor # $action->vendorlink";
			}

			$page->body .= $config->twig->render("user-actions/{$action->actiontype}/create-form.twig", ['page' => $page, 'module_useractions' => $module_useractions, 'crud_useractions' => $module_useractions_crud, $action->actiontype => $action]);
		} else {

		}
	}

	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	$config->scripts->append(hash_templatefile('scripts/user-actions.js'));
	include __DIR__ . "/basic-page.php";
