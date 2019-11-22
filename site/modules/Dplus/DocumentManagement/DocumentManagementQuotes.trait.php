<?php namespace ProcessWire;

use Purl\Url;

use DocumentFoldersQuery, DocumentFolders;
use DocumentsQuery, Documents;

trait DocumentManagementQuotes {
	public function mqo_init() {
		$this->addHook('Page(pw_template=quote-view)::documentload', function($event) {
			$page = $event->object;
			$folder   = $event->arguments(0);
			$document = $event->arguments(1);
			$qnbr     = $event->arguments(2);
			$event->return = $this->get_quote_docsURL($qnbr, $folder, $document);
		});

		$this->addHook('Page(pw_template=quote-documents)::documentload', function($event) {
			$page = $event->object;
			$folder   = $event->arguments(0);
			$document = $event->arguments(1);
			$qnbr     = $event->arguments(2);
			$event->return = $this->get_quote_docsURL($qnbr, $folder, $document);
		});
	}

	public function get_quote_docsURL($qnbr, $folder, $document) {
		$url = new Url($this->wire('pages')->get('pw_template=quote-documents')->url);
		$url->query->set('qnbr', $qnbr);
		$url->query->set('folder', $folder);
		$url->query->set('document', $document);
		return $url->getUrl();
	}

	/**
	 * Return Documents objects filtered by the tag1, reference1 fields for a Sales Order
	 * @param  string $qnbr                      Quote Number
	 * @return Documents[]|ObjectCollection
	 */
	public function get_quotedocuments($qnbr) {
		$documents_master = DocumentsQuery::create();
		$documents_master->filterByTag(self::TAG_QUOTE);
		$documents_master->filterByReference1($qnbr);
		return $documents_master->find();
	}

	/**
	 * Return the number of Documents found filtered by the tag1, reference1 fields for a Sales Order
	 * @param  string $qnbr Quote Number
	 * @return int          Number of Sales Order Documents found
	 */
	public function count_quotedocuments($qnbr) {
		$documents_master = DocumentsQuery::create();
		$documents_master->filterByTag(self::TAG_QUOTE);
		$documents_master->filterByReference1($qnbr);
		return $documents_master->count();
	}
}
