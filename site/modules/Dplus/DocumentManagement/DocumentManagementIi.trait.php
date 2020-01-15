<?php namespace ProcessWire;

use Propel\Runtime\ActiveQuery\Criteria;
use Purl\Url;

use DocumentFoldersQuery, DocumentFolders;
use DocumentsQuery, Documents;
use SalesHistoryQuery, SalesHistory;
use SalesOrderQuery, SalesOrder;
use PurchaseOrderQuery, PurchaseOrder;

trait DocumentManagementIi {
	private $PREFIX_REF_SO = 'so no.';
	private $ACTIVITY_TYPES_SO = array(
		'sale',
		'ds sale',
		'k use',
		'rga'
	);

	private $PREFIX_REF_PO = 'po no.';
	private $ACTIVITY_TYPES_PO = array(
		'receipt',
		'ds rcpt',
		'fabrcpt',
		'fab use',
		'fabship'
	);

	private $ACTIVITY_TYPES_WIP = array(
		'fin item',
		'prd fin',
		'fabrcpt',
		'prd use'
	);

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

		$this->addHook('Page(pw_template=ii-quotes)::documentsview_quote', function($event) {
			$page      = $event->object;
			$itemID    = $event->arguments(0);
			$qnbr      = $event->arguments(1);
			$url = new Url($this->get_ii_docs_folderURL($itemID, self::TAG_QUOTE));
			$url->query->set('qnbr', $qnbr);
			$event->return = $url->getUrl();
		});

		$this->addHook('Page(pw_template=ii-purchase-history)::documentsview_apinvoice', function($event) {
			$page      = $event->object;
			$itemID    = $event->arguments(0);
			$invnbr    = $event->arguments(1);
			$url = new Url($this->get_ii_docs_folderURL($itemID, self::TAG_APINVOICE));
			$url->query->set('invnbr', $invnbr);
			$event->return = $url->getUrl();
		});

		$this->addHook('Page(pw_template=ii-purchase-orders)::documentsview_purchaseorder', function($event) {
			$page      = $event->object;
			$itemID    = $event->arguments(0);
			$ponbr     = $event->arguments(1);
			$url = new Url($this->get_ii_docs_folderURL($itemID, self::TAG_VENDORPO));
			$url->query->set('ponbr', $ponbr);
			$event->return = $url->getUrl();
		});

		$this->addHook('Page(pw_template=ii-activity)::documentsview_activity', function($event) {
			$page      = $event->object;
			$itemID    = $event->arguments(0);
			$type      = $event->arguments(1);
			$reference = $event->arguments(2);

			$url = new Url($this->get_ii_docs_folderURL($itemID, 'ACT'));
			$url->query->set('type', $type);
			$url->query->set('reference', $reference);
			$event->return = $url->getUrl();
		});

		$this->addHook('Page(pw_template=ii-item)::item_image_exists', function($event) {
			$page     = $event->object;
			$itemID   = $event->arguments(0);
			$event->return = $this->item_image_exists($itemID);
		});

		$this->addHook('Page(pw_template=ii-item)::item_imageURL', function($event) {
			$page     = $event->object;
			$itemID   = $event->arguments(0);
			$event->return = $this->item_imageURL($itemID);
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
	 * Return Documents
	 * filtered by the tag1, reference1 fields for an Item ID
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
	 * Return the number of Documents
	 * filtered by the tag1, reference1 fields for an Item ID
	 * @param  string $itemID                      Item ID
	 * @return int
	 */
	public function count_itemdocuments($itemID) {
		$documents_master = DocumentsQuery::create();
		$documents_master->filterByTag(self::TAG_ITEM);
		$documents_master->filterByReference1($itemID);
		return $documents_master->count();
	}

	/**
	 * Return Documents
	 * filtered by the tag1, reference1 fields for an Item Activity
	 * @param  string $type                  Activity Type
	 * @param  string $reference             Activity Reference (e.g. Po No. 1072)
	 * @return Documents[]|ObjectCollection
	 */
	public function get_itemactivitydocuments($type, $reference) {
		$documents_master = DocumentsQuery::create();
		$this->filter_itemactivitydocuments($documents_master, $type, $reference);
		return $documents_master->find();
	}

	/**
	 * Return the number of Documents
	 * filtered by the tag1, reference1 fields for an Item Activity
	 * @param  string $type                  Activity Type (e.g. receipt)
	 * @param  string $reference             Activity Reference (e.g. Po No. 1072)
	 * @return string
	 */
	public function count_itemactivitydocuments($type, $reference) {
		$documents_master = DocumentsQuery::create();
		$this->filter_itemactivitydocuments($documents_master, $type, $reference);
		return $documents_master->count();
	}

	/**
	 * Add Filter Conditions for Item Activity
	 * @param  DocumentsQuery $documents_master Query to apply filters to
	 * @param  string         $type             Activity Type (e.g. receipt)
	 * @param  string         $reference        Activity Reference (e.g. Po No. 1072)
	 * @return void
	 */
	protected function filter_itemactivitydocuments(DocumentsQuery $documents_master, $type, $reference) {
		$type = strtolower($type);

		if (in_array($type, $this->ACTIVITY_TYPES_SO)) {
			$ref = $this->determine_reference($reference, $this->PREFIX_REF_SO);
			$documents_master->filterByTag(self::TAG_SALESORDER);
			$ref = SalesOrder::get_paddedordernumber($ref);
			$documents_master->filterByReference1($ref);
		} elseif (in_array($type, $this->ACTIVITY_TYPES_PO)) {
			$ref = $this->determine_reference($reference, $this->PREFIX_REF_PO);
			$documents_master->filterByTag(self::TAG_VENDORPO);
			$ref = PurchaseOrder::get_paddedponumber($ref);
			$documents_master->filterByReference1($ref);
		} elseif (in_array($type, $this->ACTIVITY_TYPES_WIP)) {
			$ref = $reference;
			$documents_master->filterByTag(self::TAG_WIP);
			$documents_master->filterByReference1($ref);
		} else {
			$documents_master->filterByTag($type);
		}
	}

	/**
	 * Returns Reference with Prefix Removed
	 * @param  string $reference Activity Reference (e.g. Po No. 1072)
	 * @param  string $strip     Prefix to Remove (e.g. Po No.)
	 * @return string
	 */
	protected function determine_reference($reference, $strip) {
		$ref = str_replace($strip, '', strtolower($reference));
		return trim($ref);
	}

	/**
	 * Returns Documents Query
	 * filtered for Item Images
	 * @param  string $itemID Item ID
	 * @return DocumentsQuery
	 */
	public function get_filter_query_itemimage($itemID) {
		$wildcards = array();
		$like = array();

		foreach (self::EXTENSIONS_IMAGES as $ext) {
			$like[] = 'Documents.Docifilename LIKE ?';
			$wildcards[] = "%.$ext";

		}
		$documents_master = DocumentsQuery::create();
		$documents_master->filterByTag(self::TAG_ITEM);
		$documents_master->filterByReference1($itemID);
		$documents_master->where(implode(' OR ', $like), $wildcards);
		return $documents_master;
	}

	/**
	 * Return if there is an image associated with an Item
	 * @param  string $itemID  Item ID
	 * @return bool
	 */
	public function item_hasimages($itemID) {
		$documents_master = $this->get_filter_query_itemimage($itemID);
		return $documents_master->count();
	}

	/**
	 * Return Item Image Name
	 * @param  string $itemID  Item ID
	 * @return bool
	 */
	public function get_itemimage($itemID) {
		$documents_master = $this->get_filter_query_itemimage($itemID);
		$documents_master->select('Docifilename');
		return $documents_master->findOne();
	}

	/**
	 * Returns if Item Image Exists in the directory or if tehre's one listed
	 * @param  string $itemID  Item ID
	 * @return bool
	 */
	public function item_image_exists($itemID) {
		if ($this->item_hasimages($itemID)) {
			$img = $this->get_itemimage($itemID);
			$file = $this->wire('config')->directory_images.$img;
			return file_exists($file);
		} else {
			return false;
		}
	}

	/**
	 * Returns URL to Item Image
	 * @param  string $itemID  Item ID
	 * @return string
	 */
	public function item_imageURL($itemID) {
		$img = $this->get_itemimage($itemID);
		$url = $this->wire('config')->url_images.$img;
		return $url;
	}
}
