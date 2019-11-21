<?php
	$ar_codetables = $modules->get('CodeTablesAr');

	if ($input->requestMethod('POST') || $input->get->action) {
		$rm = strtolower($input->requestMethod());
		$table = $input->$rm->text('table');

		if ($ar_codetables->validate_codetable($table)) {
			$module_codetable = $ar_codetables->get_codetable_module($table);
			$module_codetable->process_input($input);
			$session->redirect($page->get_codetable_viewURL($table), $http301 = false);
		}
	}

	if ($input->get->table) {
		$table = $input->get->text('table');

		if ($ar_codetables->validate_codetable($table)) {
			$module_codetable = $ar_codetables->get_codetable_module($table);
			$page->headline = "$module_codetable->description Table";

			$page->body .= $config->twig->render('code-tables/links-header.twig', ['page' => $page]);

			if ($session->response_codetable) {
				$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_codetable]);
			}
			$page->body .= $config->twig->render("code-tables/mar/$table-list.twig", ['page' => $page, 'table' => $table, 'codes' => $module_codetable->get_codes(), 'response' => $session->response_codetable]);
			$page->body .= $config->twig->render('code-tables/edit-code-modal.twig', ['page' => $page, 'file' => "mar/$table-form.twig"]);
			$page->js .= $config->twig->render("code-tables/mar/$table.js.twig", ['page' => $page]);
		} else {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Code Table Error", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "AR Code Table '$code' does not exist"]);
		}
	} else {
		$session->redirect($page->parent->httpUrl);
	}
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	if ($session->response_codetable) {
		$session->remove('response_codetable');
	}
	include __DIR__ . "/basic-page.php";
