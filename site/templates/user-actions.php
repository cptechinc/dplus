<?php
	$module_useractions      = $modules->get('FilterUserActions');
	$module_useractions_crud = $modules->get('UserActionsCrud');
	$html = $modules->get('HtmlWriter');

	if ($input->requestMethod('POST')) {
		include __DIR__ . "/user-action.php";
	} else {
		if ($input->get->returnpage) {
			$session->actionsreturn = $input->get->text('returnpage');
		}

		if ($input->get->id) {
			include __DIR__ . "/user-action.php";
		} else {
			$query = $module_useractions->get_actionsquery($input);

			if ($input->get->orderby) {

			} else {
				$query->orderByDateCreated('DESC');
			}
			$actions = $query->find();

			$page->body .= $config->twig->render("user-actions/list/filter-form.twig", ['page' => $page, 'module_useractions' => $module_useractions, 'actions' => $actions]);
			$page->body .= $config->twig->render("user-actions/actions-list.twig", ['page' => $page, 'module_useractions' => $module_useractions, 'actions' => $actions]);

			if ($page->print) {
				$page->show_title = true;
				include __DIR__ . "/blank-page.php";
			} else {
				include __DIR__ . "/basic-page.php";
			}
		}
	}
