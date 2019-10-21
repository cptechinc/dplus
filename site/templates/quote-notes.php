<?php
	$modules->get('DpagesMqo')->init_mqopage();
	$module_qnotes_crud = $modules->get('QnotesCrud');

	if ($input->requestMethod('POST')) {
		$response = $module_qnotes_crud->process_input_quote($input);
		$page->fullURL->query->remove('linenbr');
		$session->redirect($page->fullURL->getURL());

	} else {
		if ($input->get->qnbr) {
			$qnbr = $input->get->text('qnbr');

			if (QuoteQuery::create()->filterByQuoteid($qnbr)->count()) {
				$quote = QuoteQuery::create()->filterByQuoteid($qnbr)->findOne();
				$quote_items = QuoteDetailQuery::create()->filterByQuoteid($qnbr)->find();
				$page->title = "Quote #$qnbr Notes";
				$page->body = $config->twig->render('quotes/quote/notes/qnotes-page.twig', ['page' => $page, 'qnbr' => $qnbr, 'quote' => $quote, 'items' => $quote_items]);
				$page->body .= $config->twig->render('quotes/quote/notes/add-note-modal.twig', ['page' => $page, 'qnbr' => $qnbr]);
				$config->scripts->append(hash_templatefile('scripts/quotes/quote-notes.js'));
			} else {
				$page->headline = "Quote #$qnbr could not be found";
				$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->headline, 'msg' => "Check if the Quote Number"]);
			}
		} else {
			$page->body = $config->twig->render('quotes/quote/lookup-form.twig', ['page' => $page]);
		}
	}

	include __DIR__ . "/basic-page.php";
