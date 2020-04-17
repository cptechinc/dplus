<?php
	if ($input->get->sysop) {
		$sysopcode = $input->get->text('sysop');
		$page->headline = $page->title = "Listing $page->title for $sysopcode";

		$page->focus = $input->get->focus ? $input->get->text('focus') : '';

		$sysop = $module_codetable->get_sysop($sysopcode);

		$optcodes = $module_codetable->get_codes($sysopcode);

		$page->body .= $config->twig->render("code-tables/map/$page->codetable/list-codes.twig", ['page' => $page, 'table' => $page->codetable, 'code' => $sysop, 'sysop' => $sysop, 'optcodes' => $optcodes, 'response' => $session->response_codetable]);
		$page->body .= $config->twig->render('code-tables/edit-code-modal.twig', ['page' => $page, 'file' => "map/$page->codetable/form.twig", 'max_length_code' => SysopOptionalCode::MAX_LENGTH_CODE]);
		$page->js   .= $config->twig->render("code-tables/map/$page->codetable/js.twig", ['page' => $page, 'sysop' => $sysopcode, 'max_length_code' => SysopOptionalCode::MAX_LENGTH_CODE]);
	} else {
		$page->body .= $config->twig->render("code-tables/map/$page->codetable/list-sysop.twig", ['page' => $page, 'table' => $page->codetable, 'codes' => $module_codetable->get_sysops()]);
	}
