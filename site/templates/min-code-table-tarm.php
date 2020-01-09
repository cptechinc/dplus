<?php
	if ($input->get->code) {
		$code = $input->get->text('code');
		$page->headline = "Editing $page->title $code";

        $countries = CountryCodesQuery::create()->find();
		$tariff = $module_codetable->get_code($code);

		$page->body .= $config->twig->render("code-tables/min/$page->codetable/form.twig", ['page' => $page, 'table' => $page->codetable, 'countries' => $countries, 'tariff' => $tariff]);
        $page->js .= $config->twig->render("code-tables/min/$page->codetable/js.twig", ['page' => $page]);
	} else {
		$page->body .= $config->twig->render("code-tables/min/$page->codetable/list.twig", ['page' => $page, 'table' => $page->codetable, 'codes' => $module_codetable->get_codes(), 'response' => $session->response_codetable]);
	}
