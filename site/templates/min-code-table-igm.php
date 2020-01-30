<?php
	if ($input->get->code) {
		$code = $input->get->text('code');
		$page->headline = "Editing $page->title $code";
		$itemgroup = $module_codetable->get_code($code);

		if ($module_codetable->code_exists($code)) {
			$itemgroup = $module_codetable->get_code($code);
		} else {
			$itemgroup = new ItemGroupCode();
		}

		$page->body .= $config->twig->render("code-tables/min/$page->codetable/form.twig", ['page' => $page, 'table' => $page->codetable, 'itemgroup' => $itemgroup]);
        $page->js .= $config->twig->render("code-tables/min/$page->codetable/js.twig", ['page' => $page, 'itemgroup' => $itemgroup]);
	} else {
		$page->body .= $config->twig->render("code-tables/min/$page->codetable/list.twig", ['page' => $page, 'table' => $page->codetable, 'codes' => $module_codetable->get_codes(), 'response' => $session->response_codetable]);
	}
