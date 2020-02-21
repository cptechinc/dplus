<?php
	$module_useractions      = $modules->get('FilterUserActions');
	$module_useractions_crud = $modules->get('UserActionsCrud');
	$html = $modules->get('HtmlWriter');

	if ($input->requestMethod('POST')) {
		if ($input->get->returnpage) {
			$session->actionsreturn = $input->get->text('returnpage');
		}
		$response = $module_useractions_crud->process_input_update($input);
		$session->response_crud = $response;
		$page->fullURL->query->remove('returnpage');

		if ($session->response_crud['rescheduled']) {
			$page->fullURL->query->set('id', $session->response_crud['rescheduled']);
		}
		$session->redirect($page->fullURL->getUrl());
	} else {
		if ($input->get->returnpage) {
			$session->actionsreturn = $input->get->text('returnpage');
		}

		if ($input->get->id) {
			$id = $input->get->text('id');
			$page->title_previous = $page->title;
			$query = UseractionsQuery::create();

			if ($query->filterById($id)->count()) {
				$action = $query->findOneById($id);
				$page->title = "Viewing $action->actiontype ID $id";
				$page->body .= $config->twig->render("user-actions/links.twig", ['page' => $page, 'session' => $session]);

				if ($session->response_crud) {
					$response = $session->response_crud;
					$title = $response['error'] ? 'Error!' : 'Success!';
					$alert = $config->twig->render('util/alert.twig', ['type' => $response['notifytype'], 'title' => $title, 'iconclass' => $response['icon'], 'message' => $response['message']]);
					$page->body = $html->div('class=mb-3', $alert);
					$session->remove('response_crud');
				}
				$page->body .= $config->twig->render("user-actions/$action->actiontype.twig", ['page' => $page, 'module_useractions' => $module_useractions, 'crud_useractions' => $module_useractions_crud, $action->actiontype => $action]);
				$config->scripts->append(hash_templatefile('scripts/user-actions.js'));
			} else {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Attention!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Action ID $id Not found"]);
				$page->body .= $html->div('class=form-group', '');
				$page->body .= $html->a("href=$page->url|class=btn btn-secondary", $html->icon('fa fa-list') . ' View Actions List');
			}

		}
	}

	if ($config->json) {
		header('Content-Type: application/json');
		echo $page->body;
	} else {
		if ($page->print) {
			$page->show_title = true;
			echo 'suf';
			include __DIR__ . "/blank-page.php";
		} else {
			include __DIR__ . "/basic-page.php";
		}
	}
