<?php
	$html = $modules->get('HtmlWriter');
	$recordlocker = $modules->get('RecordLockerUser');

	if ($input->get->code) {
		$code = $input->get->text('code');

		if ($module_codetable->code_exists($code)) {
			$page->title = $page->headline = "CRCD: $code";
			$creditcode = $module_codetable->get_code($code);
		} else {
			$page->title = $page->headline = "Create $page->title";
			$creditcode = new CreditCardDigitGet();

			if ($code != 'new') {
				$creditcode->setCode($code);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Type Code '$code' could not be found, use the form to create it"]);
				$page->body .= $html->div('class=mb-3');
			}
		}

		if (!$creditcode->isNew()) {
			/**
			 * Show alert that creditcode is locked if
			 *  1. creditcode Isn't new
			 *  2. The creditcode has a record lock
			 *  3. Userid does not match the lock
			 * Otherwise if not locked, create lock
			 */
			if ($recordlocker->function_locked($page->codetable, $creditcode->code) && !$recordlocker->function_locked_by_user($page->codetable, $creditcode->code)) {
				$msg = "$creditcode->code is being locked by " . $recordlocker->get_locked_user($page->codetable, $creditcode->code);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Terms Code $creditcode->code is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$page->body .= $html->div('class=mb-3');
			} elseif (!$recordlocker->function_locked($page->codetable, $creditcode->code)) {
				$recordlocker->create_lock($page->codetable, $creditcode->code);
			}
		}

        $gl_accounts = GlCodeQuery::create()->find();

		$page->body .= $config->twig->render("code-tables/mar/$page->codetable/form.twig", ['page' => $page, 'table' => $page->codetable, 'code' => $creditcode, 'gl_accounts' => $gl_accounts, 'recordlocker' => $recordlocker]);
		$page->js   .= $config->twig->render("code-tables/mar/$page->codetable/js.twig", ['page' => $page, 'creditcode' => $creditcode]);
	} else {
		$page->title = $page->headline = "Customer Terms Code";
		$recordlocker->remove_lock($page->codetable);
		$page->body .= $config->twig->render("code-tables/mar/$page->codetable/list.twig", ['page' => $page, 'table' => $page->codetable, 'codes' => $module_codetable->get_codes(), 'response' => $session->response_codetable, 'recordlocker' => $recordlocker]);
	}

//$page->body .= $config->twig->render('code-tables/edit-code-modal.twig', ['page' => $page, 'file' => "mar/$page->codetable-form.twig"]);
//$page->js .= $config->twig->render("code-tables/mar/$page->codetable.js.twig", ['page' => $page]);
