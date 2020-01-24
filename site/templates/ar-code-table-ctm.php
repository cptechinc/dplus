<?php
	$html = $modules->get('HtmlWriter');

	if ($input->get->code) {
		$code = $input->get->text('code');
		$configAR = ConfigArQuery::create()->findOne();

		if ($module_codetable->code_exists($code)) {
			$page->title = $page->headline = "CTM: $code";
			$typecode = $module_codetable->get_code($code);
		} else {
			$page->title = $page->headline = "Create $page->title";
			$typecode = new CustomerTypeCode();

			if ($code != 'new') {
				$typecode->setCode($code);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Type Code '$code' could not be found, use the form to create it"]);
				$page->body .= $html->div('class=mb-3');
			}
		}

		if ($configAR->gl_report_type() == 'inventory') {
			$page->body .= $config->twig->render("code-tables/mar/$page->codetable/edit-code-form.twig", ['page' => $page, 'table' => $page->codetable, 'code' => $typecode, 'module_custnotes' => $modules->get('CodeTablesCtmNotes')]);
		} else {
			$gl_codes = GlCodeQuery::create()->find();
			$page->body .= $config->twig->render("code-tables/mar/$page->codetable/edit-code-form-customer.twig", ['page' => $page, 'table' => $page->codetable, 'code' => $typecode, 'gl_codes' => $gl_codes, 'module_custnotes' => $modules->get('CodeTablesCtmNotes')]);
		}

		$page->body .= $config->twig->render("code-tables/mar/$page->codetable/cust-type-notes-modal.twig", ['page' => $page, 'code' => $typecode]);
		$page->js   .= $config->twig->render("code-tables/mar/$page->codetable/js.twig", ['page' => $page, 'typecode' => $typecode]);
	} else {
		$page->title = $page->headline = "CTM";
		$page->body .= $config->twig->render("code-tables/mar/$page->codetable/list.twig", ['page' => $page, 'table' => $page->codetable, 'codes' => $module_codetable->get_codes(), 'response' => $session->response_codetable]);
	}

//$page->body .= $config->twig->render('code-tables/edit-code-modal.twig', ['page' => $page, 'file' => "mar/$page->codetable-form.twig"]);
//$page->js .= $config->twig->render("code-tables/mar/$page->codetable.js.twig", ['page' => $page]);
