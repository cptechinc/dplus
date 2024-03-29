<?php
	// NOTE: $page->codetable is a hook Property that points to $page->name

	$ar_codetables = $modules->get('CodeTablesAr');

	if ($input->requestMethod('POST') || $input->get->action) {
		$rm = strtolower($input->requestMethod());
		$action = $input->$rm->text('action');
		$code = $input->$rm->text('code');
		$code = $action == 'update-notes' || $action == 'delete-notes' ? $code : '';

		if ($ar_codetables->validate_codetable($page->codetable)) {
			$module_codetable = $ar_codetables->get_codetable_module($page->codetable);
			$module_codetable->process_input($input);
			$code = $module_codetable->code_exists($code) ? $code : false;

			if ($page->codetable == 'ctm') {
				$action = $input->$rm->text('action');
				if ($action == 'update-notes' || $action == 'update-notes') {
					$url = $page->get_codetable_viewURL($page->codetable, $code);
				} else {
					$url = $page->get_codetable_listURL($page->codetable, $code);
				}
			} else {
				$url = $page->get_codetable_viewURL($page->codetable, $code);;
			}
			$session->redirect($url, $http301 = false);
		}
	}

	if ($ar_codetables->validate_codetable($page->codetable)) {
		$page->focus = $input->get->focus ? $input->get->text('focus') : '';
		$module_codetable = $ar_codetables->get_codetable_module($page->codetable);
		$page->headline = "$module_codetable->description Table";

		$page->body .= $config->twig->render('code-tables/links-header.twig', ['page' => $page, 'input' => $input]);

		if ($session->response_codetable) {
			$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_codetable]);
		}

		if (file_exists(__DIR__."/ar-code-table-$page->codetable.php")) {
			include(__DIR__."/ar-code-table-$page->codetable.php");
		} else {
			$page->body .= $config->twig->render("code-tables/mar/$page->codetable/list.twig", ['page' => $page, 'codes' => $module_codetable->get_codes(), 'response' => $session->response_codetable]);
			$page->body .= $config->twig->render('code-tables/edit-code-modal.twig', ['page' => $page, 'max_length_code' => $module_codetable->get_max_length_code(), 'file' => "mar/$page->codetable/form.twig"]);
			$page->js   .= $config->twig->render("code-tables/mar/$page->codetable/js.twig", ['page' => $page, 'max_length_code' => $module_codetable->get_max_length_code(), 'm_ar' => $module_codetable]);
		}
	} else {
		$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Code Table Error", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "AR Code Table '$page->codetable' does not exist"]);
	}

	$config->scripts->append(Pauldro\ProcessWire\FileHasher::instance()->getHashUrl('scripts/lib/jquery-validate.js'));
	$page->js .= $config->twig->render("code-tables/js.twig", ['page' => $page]);

	if ($session->response_codetable) {
		$session->remove('response_codetable');
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
