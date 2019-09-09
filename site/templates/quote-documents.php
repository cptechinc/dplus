<?php
	if ($input->get->qnbr) {
		$qnbr = $input->get->text('qnbr');
		$document_management = $modules->get('DocumentManagement');

		if (QuoteQuery::create()->filterByQuoteid($qnbr)->count()) {
			$page->title = "Quote #$qnbr Documents";

			$documents = $document_management->get_quotedocuments($qnbr);

			if ($input->get->document && $input->get->folder) {
				$folder = $input->get->text('folder');
				$filename = $input->get->text('document');
				$document_management->move_document($folder, $filename);

				if ($document_management->is_filewebaccessible($filename)) {
					$session->redirect($config->url_webdocs.$filename);
				}
			}
			$page->body .= $config->twig->render('quotes/quote/quote-documents.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management]);

		} else {
			$page->headline = "Quote #$qnbr could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->headline, 'msg' => "Check if the Order Number is correct or if it is in Sales History"]);
		}
	} else {
		$page->body = $config->twig->render('quotes/quote/lookup-form.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
