<?php
	if ($input->get->code) {
		$code = $input->get->text('code');
		$page->headline = "Editing $page->title $code";
		$configAR = ConfigArQuery::create()->findOne();

		if ($configAR->gl_report_type() == 'inventory') {
			$page->body .= $config->twig->render("code-tables/mar/$page->codetable/edit-code-form-inventory.twig", ['page' => $page, 'table' => $page->codetable, 'code' => $module_codetable->get_code($code)]);
		} else {
			$gl_codes = GlCodeQuery::create()->find();
			$page->body .= $config->twig->render("code-tables/mar/$page->codetable/edit-code-form-customer.twig", ['page' => $page, 'table' => $page->codetable, 'code' => $module_codetable->get_code($code), 'gl_codes' => $gl_codes]);
		}
	} else {
		$page->body .= $config->twig->render("code-tables/mar/$page->codetable/list.twig", ['page' => $page, 'table' => $page->codetable, 'codes' => $module_codetable->get_codes(), 'response' => $session->response_codetable]);
	}

//$page->body .= $config->twig->render('code-tables/edit-code-modal.twig', ['page' => $page, 'file' => "mar/$page->codetable-form.twig"]);
//$page->js .= $config->twig->render("code-tables/mar/$page->codetable.js.twig", ['page' => $page]);
