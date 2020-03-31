<?php
	$html = $modules->get('HtmlWriter');
	$recordlocker = $modules->get('RecordLockerUser');

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

		if (!$typecode->isNew()) {
			/**
			 * Show alert that typecode is locked if
			 *  1. Typecode Isn't new
			 *  2. The typecode has a record lock
			 *  3. Userid does not match the lock
			 * Otherwise if not locked, create lock
			 */
			if ($recordlocker->function_locked($page->codetable, $typecode->code) && !$recordlocker->function_locked_by_user($page->codetable, $typecode->code)) {
				$msg = "$typecode->code is being locked by " . $recordlocker->get_locked_user($page->codetable, $typecode->code);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Typecode $typecode->code is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$page->body .= $html->div('class=mb-3');
			} elseif (!$recordlocker->function_locked($page->codetable, $typecode->code)) {
				$recordlocker->create_lock($page->codetable, $typecode->code);
			}
		}

		if ($configAR->gl_report_type() == 'inventory') {
			$page->body .= $config->twig->render("code-tables/mar/$page->codetable/edit-code-form.twig", ['page' => $page, 'table' => $page->codetable, 'code' => $typecode, 'module_custnotes' => $modules->get('CodeTablesCtmNotes'), 'recordlocker' => $recordlocker]);
		} else {
			$gl_codes = GlCodeQuery::create()->find();
			$page->body .= $config->twig->render("code-tables/mar/$page->codetable/edit-code-form-customer.twig", ['page' => $page, 'table' => $page->codetable, 'code' => $typecode, 'gl_codes' => $gl_codes, 'module_custnotes' => $modules->get('CodeTablesCtmNotes')]);
		}

		$page->body .= $config->twig->render("code-tables/mar/$page->codetable/cust-type-notes-modal.twig", ['page' => $page, 'code' => $typecode, 'recordlocker' => $recordlocker]);
		$page->js   .= $config->twig->render("code-tables/mar/$page->codetable/js.twig", ['page' => $page, 'typecode' => $typecode]);
	} else {
		$page->title = $page->headline = "CTM";
		$recordlocker->remove_lock($page->codetable);
		$page->body .= $config->twig->render("code-tables/mar/$page->codetable/list.twig", ['page' => $page, 'table' => $page->codetable, 'codes' => $module_codetable->get_codes(), 'response' => $session->response_codetable, 'recordlocker' => $recordlocker]);
	}

//$page->body .= $config->twig->render('code-tables/edit-code-modal.twig', ['page' => $page, 'file' => "mar/$page->codetable-form.twig"]);
//$page->js .= $config->twig->render("code-tables/mar/$page->codetable.js.twig", ['page' => $page]);
