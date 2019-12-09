<?php
	if ($input->get->code) {
		$configAR = ConfigArQuery::create()->findOne();
		$code = $input->get->text('code');

		if ($configAR->gl_report_type() == 'inventory') {
			$page->body .= $config->twig->render("code-tables/mar/$table/edit-code-form.twig", ['page' => $page, 'table' => $table, 'code' => $module_codetable->get_code($code)]);
		} else {

		}
	} else {
		$page->body .= $config->twig->render("code-tables/mar/$table/list.twig", ['page' => $page, 'table' => $table, 'codes' => $module_codetable->get_codes(), 'response' => $session->response_codetable]);
	}

//$page->body .= $config->twig->render('code-tables/edit-code-modal.twig', ['page' => $page, 'file' => "mar/$table-form.twig"]);
//$page->js .= $config->twig->render("code-tables/mar/$table.js.twig", ['page' => $page]);
