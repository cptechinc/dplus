<?php
	$modules->get('DpagesMqo')->init_mqopage();
	$module_useractions = $modules->get('FilterUserActions');
	$html = $modules->get('HtmlWriter');

	if ($input->get->qnbr) {
		$qnbr = $input->get->text('qnbr');

		if (QuoteQuery::create()->filterByQuoteid($qnbr)->count()) {
			if ($page->print) {
				$session->redirect($pages->get('pw_template=quote-print')->url."?qnbr=$qnbr");
			}
			$page->title = "Quote #$qnbr";
			$quote = QuoteQuery::create()->filterByQuoteid($qnbr)->findOne();
			$document_management = $modules->get('DocumentManagement');
			$page->listpage = $pages->get('pw_template=quotes');
			$query_useractions = $module_useractions->get_actionsquery($input);
			$actions = $query_useractions->filterByQuotelink($qnbr)->find();
			$module_qnotes = $modules->get('QnotesQuote');

			$page->body .= $config->twig->render("quotes/quote/quote-page.twig", ['page' => $page, 'quote' => $quote, 'document_management' => $document_management]);
			$page->body .= $html->div('class=mb-3');
			$page->body .= $config->twig->render("quotes/quote/quote-items.twig", ['page' => $page, 'quote' => $quote]);
			$page->body .= $html->div('class=mb-3');
			$page->body .= $config->twig->render('quotes/quote/quote-actions.twig', ['page' => $page, 'user' => $user, 'quote' => $quote]);
			$page->body .= $html->div('class=mb-3');
			$page->body .= $config->twig->render("quotes/quote/quote-notes.twig", ['page' => $page, 'qnbr' => $qnbr, 'qnotes_qt' => $module_qnotes]);
			$page->body .= $html->div('class=mb-3');
			$page->body .= $config->twig->render('quotes/quote/quote-documents.twig', ['page' => $page, 'documents' => $document_management->get_quotedocuments($qnbr), 'document_management' => $document_management, 'qnbr' => $qnbr]);
			$page->body .= $html->div('class=mb-3');
			$page->body .= $config->twig->render('quotes/quote/user-actions.twig', ['page' => $page, 'module_useractions' => $module_useractions, 'actions' => $actions, 'qnbr' => $qnbr]);
		} else {
			$page->headline = $page->title = "Quote #$qnbr could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Quote Number is correct"]);
		}
	} else {
		$page->body = $config->twig->render('quotes/quote/lookup-form.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
