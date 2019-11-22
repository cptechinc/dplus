<?php namespace ProcessWire;

use DocumentFoldersQuery, DocumentFolders;
use DocumentsQuery, Documents;

trait DocumentManagementIi {
	public function ii_init() {
		$this->addHook('Page(pw_template=ii-documents)::documentload', function($event) {
			$page     = $event->object;
			$folder   = $event->arguments(0);
			$document = $event->arguments(1);
			$itemID   = $event->arguments(2);
			$event->return = $this->get_ii_documentURL($itemID, $folder, $document);
		});

		$this->addHook('Page(pw_template=ii-sales-orders|ii-sales-history)::documentsview_salesorder', function($event) {
			$page      = $event->object;
			$itemID    = $event->arguments(0);
			$ordn      = $event->arguments(1);
			$lotserial = $event->arguments(2);
			$url = new Url($this->get_ii_docs_folderURL($itemID, self::TAG_SALESORDER));
			$url->query->set('ordn', $ordn);

			if (SalesHistoryQuery::create()->filterByOrdernumber(SalesOrder::get_paddedordernumber($ordn))->count()) {
				$date = $page->fullURL->query->get('date');
				$url->query->set('date', $date);
				$url->query->set('folder', self::TAG_ARINVOICES);
			}

			$event->return = $url->getUrl();
		});
	}

	/**
	 * Returns URL to the II documents Page
	 * @param  string $itemID Item ID
	 * @param  string $folder Document Management Folder Code
	 * @return string
	 */
	public function get_ii_docs_folderURL($itemID, $folder) {
		$url = new Url($this->wire('pages')->get('pw_template=ii-documents')->url);
		$url->query->set('itemID', $itemID);
		$url->query->set('folder', $folder);
		return $url->getUrl();
	}

	/**
	 * Returns URL to the II documents Page
	 * @param  string $itemID   Item ID
	 * @param  string $folder   Document Management Folder Code
	 * @param  string $document Document Name
	 * @return string
	 */
	public function get_ii_documentURL($itemID, $folder, $document) {
		$url = new Url($this->wire('pages')->get('pw_template=ii-documents')->url);
		$url->query->set('itemID', $itemID);
		$url->query->set('folder', $folder);
		$url->query->set('document', $document);
		return $url->getUrl();
	}

	/**
	 * Return Documents objects filtered by the tag1, reference1 fields for an Item ID
	 * @param  string $itemID                      Item ID
	 * @return Documents[]|ObjectCollection
	 */
	public function get_itemdocuments($itemID) {
		$documents_master = DocumentsQuery::create();
		$documents_master->filterByTag(self::TAG_ITEM);
		$documents_master->filterByReference1($itemID);
		return $documents_master->find();
	}

	/**
	 * Return Dthe number of ocuments found filtered by the tag1, reference1 fields for an Item ID
	 * @param  string $itemID                      Item ID
	 * @return Documents[]|ObjectCollection
	 */
	public function count_itemdocuments($itemID) {
		$documents_master = DocumentsQuery::create();
		$documents_master->filterByTag(self::TAG_ITEM);
		$documents_master->filterByReference1($itemID);
		return $documents_master->count();
	}
}
