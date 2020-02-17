<?php
	if ($input->get->code) {
		$code = $input->get->text('code');
		$page->headline = "Editing $page->title $code";
		$itemgroup = $module_codetable->get_code($code);

		if ($module_codetable->code_exists($code)) {
			$itemgroup = $module_codetable->get_code($code);
		} else {
			$itemgroup = new InvGroupCode();
		}

		$product_line_codes = InvProductLineCodeQuery::create()->find();
		$gl_accounts = GlCodeQuery::create()->find();

		$page->body .= $config->twig->render("code-tables/min/$page->codetable/form.twig", ['page' => $page, 'table' => $page->codetable, 'itemgroup' => $itemgroup, 'product_line_codes' => $product_line_codes, 'gl_accounts' => $gl_accounts]);
        $page->js .= $config->twig->render("code-tables/min/$page->codetable/js.twig", ['page' => $page, 'itemgroup' => $itemgroup]);
	} else {
		$page->body .= $config->twig->render("code-tables/min/$page->codetable/list.twig", ['page' => $page, 'table' => $page->codetable, 'codes' => $module_codetable->get_codes(), 'response' => $session->response_codetable]);
	}
