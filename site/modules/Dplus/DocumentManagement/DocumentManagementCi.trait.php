<?php namespace ProcessWire;

use DocumentFoldersQuery, DocumentFolders;
use DocumentsQuery, Documents;

trait DocumentManagementCi {
	public function ci_init() {
		$this->addHook('Page(pw_template=ci-documents)::documentload', function($event) {
			$page = $event->object;
			$folder   = $event->arguments(0);
			$document = $event->arguments(1);
			$custID   = $event->arguments(2);
			$event->return = $this->get_ci_documentURL($custID, $folder, $document);
		});

		$this->addHook('Page(pw_template=ci-sales-orders|ci-sales-history|ci-customer-po)::documentsview_salesorder', function($event) {
			$page    = $event->object;
			$custID  = $event->arguments(0);
			$ordn    = $event->arguments(1);
			$url = new Url($this->wire('pages')->get('pw_template=ci-documents')->url);
			$url->query->set('custID', $custID);
			$url->query->set('folder', self::TAG_SALESORDER);
			$url = new Url(get_ci_docs_folderURL($custID, self::TAG_SALESORDER));
			$url->query->set('ordn', $ordn);

			if (SalesHistoryQuery::create()->filterByOrdernumber(SalesOrder::get_paddedordernumber($ordn))->count()) {
				$date = $page->fullURL->query->get('date');
				$url->query->set('date', $date);
				$url->query->set('folder', self::TAG_ARINVOICES);
			}

			$event->return = $url->getUrl();
		});

		$this->addHook('Page(pw_template=ci-quotes)::documentsview_quote', function($event) {
			$page    = $event->object;
			$custID  = $event->arguments(0);
			$qnbr    = $event->arguments(1);
			$url = new Url(get_ci_docs_folderURL($custID, self::TAG_QUOTE));
			$url->query->set('qnbr', $qnbr);
			$event->return = $url->getUrl();
		});
	}

	/**
	 * Returns URL to the CI documents Page
	 * @param  string $custID Customer ID
	 * @param  string $folder Document Management Folder Code
	 * @return string
	 */
	public function get_ci_docs_folderURL($custID, $folder) {
		$url = new Url($this->wire('pages')->get('pw_template=ci-documents')->url);
		$url->query->set('custID', $custID);
		$url->query->set('folder', $folder);
		return $url->getUrl();
	}

	/**
	 * Returns URL to the CI documents Page
	 * @param  string $custID   Customer ID
	 * @param  string $folder   Document Management Folder Code
	 * @param  string $document Document Name
	 * @return string
	 */
	public function get_ci_documentURL($custID, $folder, $document) {
		$url = new Url($this->wire('pages')->get('pw_template=ci-documents')->url);
		$url->query->set('custID', $custID);
		$url->query->set('folder', $folder);
		$url->query->set('document', $document);
		return $url->getUrl();
	}
}
