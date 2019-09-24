<?php
	$modules->get('MqoPages')->init_mqopage();
	$module_useractions = $modules->get('FilterUserActions');
	$html = $modules->get('HtmlWriter');
	$barcoder = $modules->get('BarcodeMaker');

	if ($input->get->qnbr) {
		$qnbr = $input->get->text('qnbr');

		if (QuoteQuery::create()->filterByQuoteid($qnbr)->count()) {
			$page->title = "Viewing Quote #$qnbr";
			$document_management = $modules->get('DocumentManagement');
			$quote = QuoteQuery::create()->filterByQuoteid($qnbr)->findOne();
			$page->listpage = $pages->get('pw_template=quotes');

			$query_useractions = $module_useractions->get_actionsquery($input);
			$actions = $query_useractions->filterByQuotelink($qnbr)->find();

			if ($page->print) {
				$dpluscustomer = $pages->get('/config/customer/');
				$page->body .= $config->twig->render("quotes/quote/print/quote-header.twig", ['page' => $page, 'quote' => $quote, 'barcoder' => $barcoder, 'dpluscustomer' => $config->customer]);
				$page->body .= $html->div('class=clearfix mb-4');
				$page->body .= $config->twig->render("quotes/quote/print/quote-items.twig", ['page' => $page, 'quote' => $quote]);
				$page->body .= $config->twig->render("quotes/quote/print/quote-totals.twig", ['page' => $page, 'quote' => $quote]);
			} else {
				$page->body .= $config->twig->render("quotes/quote/quote-page.twig", ['page' => $page, 'quote' => $quote, 'document_management' => $document_management]);
				$page->body .= $html->div('class=mb-3');
				$page->body .= $config->twig->render("quotes/quote/quote-items.twig", ['page' => $page, 'quote' => $quote]);
				$page->body .= $html->div('class=mb-3');
				$page->body .= $config->twig->render('quotes/quote/quote-actions.twig', ['page' => $page, 'user' => $user, 'quote' => $quote]);
				$page->body .= $html->div('class=mb-3');
				$page->body .= $config->twig->render("quotes/quote/quote-notes.twig", ['page' => $page, 'quote' => $quote, 'notes' => $quote->get_notes()]);
				$page->body .= $config->twig->render('quotes/quote/notes/add-note-modal.twig', ['page' => $page, 'qnbr' => $qnbr]);
				$config->scripts->append(hash_templatefile('scripts/quotes/quote-notes.js'));
				$page->body .= $html->div('class=mb-3');
				$page->body .= $config->twig->render('quotes/quote/quote-documents.twig', ['page' => $page, 'documents' => $document_management->get_quotedocuments($qnbr), 'document_management' => $document_management, 'qnbr' => $qnbr]);
				$page->body .= $html->div('class=mb-3');
				$page->body .= $config->twig->render('quotes/quote/user-actions.twig', ['page' => $page, 'module_useractions' => $module_useractions, 'actions' => $actions, 'qnbr' => $qnbr]);
			}

		} else {
			$page->headline = $page->title = "Quote #$qnbr could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Quote Number is correct"]);
		}
	} else {
		$page->body = $config->twig->render('quotes/quote/lookup-form.twig', ['page' => $page]);
	}

	if ($page->print) {
		$page->show_title = true;

		if (!$page->is_pdf()) {
			$page->show_title = false;
			$pdfmaker = $modules->get('PdfMaker');
			$pdfmaker->set_fileID("quote-$quote->quotenumber");
			$pdfmaker->set_filetype('quote');
			$pdfmaker->set_url($page->get_pdfURL());
			$pdfmaker->generate_pdf();
		} else {
			$page->show_title = false;
		}

		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
