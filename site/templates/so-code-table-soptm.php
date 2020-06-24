<?php
	if ($input->get->sysop) {
		$sysopcode = $input->get->text('sysop');
		$page->headline = $page->title = "Listing $page->title for $sysopcode";
		$sysop = $module_codetable->get_sysop($sysopcode);

		$optcodes = $module_codetable->get_codes($sysopcode);

		$page->body .= $config->twig->render("code-tables/mso/$page->codetable/list-codes.twig", ['page' => $page, 'table' => $page->codetable, 'code' => $sysop, 'sysop' => $sysop, 'optcodes' => $optcodes, 'response' => $session->response_codetable]);
		$page->body .= $config->twig->render('code-tables/edit-code-modal.twig', ['page' => $page, 'file' => "mso/$page->codetable/form.twig", 'sysop' => $sysopcode, 'max_length_code' => SysopOptionalCode::MAX_LENGTH_CODE]);
		$page->js   .= $config->twig->render("code-tables/mso/$page->codetable/js.twig", ['page' => $page, 'sysop' => $sysopcode, 'max_length_code' => SysopOptionalCode::MAX_LENGTH_CODE, 'm_soptm' => $module_codetable]);
	} else {
		$page->body .= $config->twig->render("code-tables/mso/$page->codetable/list-sysop.twig", ['page' => $page, 'table' => $page->codetable, 'codes' => $module_codetable->get_sysops()]);
	}
