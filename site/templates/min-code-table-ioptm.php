<?php
	if ($input->get->optcode) {
        $code = $input->get->text('code');
		$optcode = $input->get->text('optcode');
		$page->headline = "Editing $page->title $optcode";
		$sysop = $module_codetable->get_code($optcode);

		if ($module_codetable->code_exists($optcode)) {
			$sysop = $module_codetable->get_code($optcode);
		} else {
			$sysop = new MsaSysopCode();
		}

        $optcode = $module_codetable->get_sysops($code);

		$page->body .= $config->twig->render("code-tables/min/$page->codetable/form.twig", ['page' => $page, 'table' => $page->codetable, 'code' => $code, 'optcode' => $optcode]);
        $page->js .= $config->twig->render("code-tables/min/$page->codetable/js.twig", ['page' => $page, 'sysop' => $sysop]);
	}

    elseif ($input->get->code) {
		$code = $input->get->text('code');
		$page->headline = "Editing $page->title $code";
		$sysop = $module_codetable->get_code($code);

		if ($module_codetable->code_exists($code)) {
			$sysop = $module_codetable->get_code($code);
		} else {
			$sysop = new MsaSysopCode();
		}

        $optcodes = $module_codetable->get_sysops($code);

        $page->body .= $config->twig->render("code-tables/min/$page->codetable/optcode-list.twig", ['page' => $page, 'table' => $page->codetable, 'code' => $code, 'sysop' => $sysop, 'optcodes' => $optcodes]);
		//$page->body .= $config->twig->render("code-tables/min/$page->codetable/form.twig", ['page' => $page, 'table' => $page->codetable, 'sysop' => $sysop]);
        $page->js .= $config->twig->render("code-tables/min/$page->codetable/js.twig", ['page' => $page, 'sysop' => $sysop]);
	} else {
		$page->body .= $config->twig->render("code-tables/min/$page->codetable/list.twig", ['page' => $page, 'table' => $page->codetable, 'codes' => $module_codetable->get_codes(), 'response' => $session->response_codetable]);
	}
