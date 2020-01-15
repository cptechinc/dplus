<?php namespace ProcessWire;

use Purl\Url;
use DocumentFoldersQuery, DocumentFolders;
use DocumentsQuery, Documents;
use SalesHistoryQuery, SalesHistory;
use SalesHistoryDetailQuery, SalesHistoryDetail;
use SalesOrderQuery, SalesOrder;
use SalesOrderDetailQuery, SalesOrderDetail;

trait DocumentManagementSalesOrders {
	public function mso_init() {
		$this->addHook('Page(pw_template=sales-order-view)::documentload', function($event) {
			$page = $event->object;
			$folder   = $event->arguments(0);
			$document = $event->arguments(1);
			$ordn     = $event->arguments(2);
			$event->return = $this->get_salesorder_docsURL($ordn, $folder, $document);
		});

		$this->addHook('Page(pw_template=sales-order-documents)::documentload', function($event) {
			$page = $event->object;
			$folder   = $event->arguments(0);
			$document = $event->arguments(1);
			$ordn     = $event->arguments(2);
			$event->return = $this->get_salesorder_docsURL($ordn, $folder, $document);
		});
	}

	/**
	 * Returns URL to Sales Order Documents Page
	 * @param  string $ordn     Sales Order Number
	 * @param  string $folder   Folder
	 * @param  string $document File Name
	 * @return string
	 */
	public function get_salesorder_docsURL($ordn, $folder, $document) {
		$url = new Url($this->wire('pages')->get('pw_template=sales-order-documents')->url);
		$url->query->set('ordn', $ordn);
		$url->query->set('folder', $folder);
		$url->query->set('document', $document);
		return $url->getUrl();
	}

	/**
	 * Return Documents objects
	 * filtered by the tag1, reference1 fields for a Sales Order
	 * @param  string $ordn                      Sales Order Number
	 * @return Documents[]|ObjectCollection
	 */
	public function get_salesorderdocuments($ordn) {
		$documents_master = DocumentsQuery::create();
		$this->filter_salesorderconditions($documents_master, $ordn);
		return $documents_master->find();
	}

	/**
	 * Return the number of Documents
	 * filtered by the tag1, reference1 fields for a Sales Order
	 * @param  string $ordn Sales Order Number
	 * @return int          Number of Sales Order Documents found
	 */
	public function count_salesorderdocuments($ordn) {
		$documents_master = DocumentsQuery::create();
		$this->filter_salesorderconditions($documents_master, $ordn);
		return $documents_master->count();
	}

	/**
	 * Return the number of Documents
	 * filtered by the tag1, reference1 fields for a Sales Order  in History
	 * @param  string $ordn Sales Order Number
	 * @return int          Number of Sales Order Documents found
	 */
	public function get_saleshistorydocuments($ordn) {
		$documents_master = DocumentsQuery::create();
		$this->filter_saleshistoryconditions($documents_master, $ordn);
		return $documents_master->find();
	}

	/**
	 * Return the number of Documents
	 * filtered by the tag1, reference1 fields for a Sales Order in History
	 * @uses self::filter_saleshistoryconditions()
	 *
	 * @param  string $ordn Sales Order Number
	 * @return int          Number of Sales History Documents found
	 */
	public function count_saleshistorydocuments($ordn) {
		$documents_master = DocumentsQuery::create();
		$this->filter_saleshistoryconditions($documents_master, $ordn);
		return $documents_master->count();
	}

	/**
	 * Adds Filter Conditions to the Documents Query
	 * to find Documents associated with a Sales History Order
	 * @param  DocumentsQuery $documents_master Query to add filters to
	 * @param  string         $ordn             Sales Order Number
	 * @return void
	 */
	protected function filter_saleshistoryconditions(DocumentsQuery $documents_master, $ordn) {
		$ordn = SalesOrder::get_paddedordernumber($ordn);
		$q = SalesHistoryDetailQuery::create();
		$q->filterByOrdernumber($ordn);
		$ponbrs = $q->select(SalesHistoryDetail::get_aliasproperty('vendorpo'))->find()->toArray();

		$column_tag = Documents::get_aliasproperty('tag');
		$column_reference1 = Documents::get_aliasproperty('reference1');

		// Create Invoices Filter
		$documents_master->condition('tag_invoices', "Documents.$column_tag = ?", self::TAG_ARINVOICE);
		$documents_master->condition('reference1_invoices', "Documents.$column_reference1 = ?", $ordn);
		$documents_master->combine(array('tag_invoices', 'reference1_invoices'), 'and', 'cond_invoices') ;

		// Create Vendor PO Filter
		$documents_master->condition('tag_vendorpo', "Documents.$column_tag = ?", self::TAG_VENDORPO);
		$documents_master->condition('reference1_vendorpo', "Documents.$column_reference1 IN ?", $ponbrs);
		$documents_master->combine(array('tag_vendorpo', 'reference1_vendorpo'), 'and', 'cond_vendorpo');

		$documents_master->where(array('cond_invoices', 'cond_vendorpo'), 'or');
	}

	/**
	 * Adds Filter Conditions to the Documents Query
	 * to find Documents associated with a Sales Order
	 * @param  DocumentsQuery $documents_master Query to add filters to
	 * @param  string         $ordn             Sales Order Number
	 * @return void
	 */
	protected function filter_salesorderconditions(DocumentsQuery $documents_master, $ordn) {
		$ordn = SalesOrder::get_paddedordernumber($ordn);
		$q = SalesOrderDetailQuery::create();
		$q->filterByOrdernumber($ordn);
		$ponbrs = $q->select(SalesOrderDetail::get_aliasproperty('vendorpo'))->find()->toArray();

		if (!empty($ponbrs)) {
			$column_tag = Documents::get_aliasproperty('tag');
			$column_reference1 = Documents::get_aliasproperty('reference1');

			// Create Sales Orders Filter
			$documents_master->condition('tag_so', "Documents.$column_tag = ?", self::TAG_SALESORDER);
			$documents_master->condition('reference1_so', "Documents.$column_reference1 = ?", $ordn);
			$documents_master->combine(array('tag_so', 'reference1_so'), 'and', 'cond_so') ;

			// Create Vendor PO Filter
			$documents_master->condition('tag_vendorpo', "Documents.$column_tag = ?", self::TAG_VENDORPO);
			$documents_master->condition('reference1_vendorpo', "Documents.$column_reference1 IN ?", $ponbrs);
			$documents_master->combine(array('tag_vendorpo', 'reference1_vendorpo'), 'and', 'cond_vendorpo');

			$documents_master->where(array('cond_so', 'cond_vendorpo'), 'or');
		} else {
			$documents_master->filterByTag(self::TAG_SALESORDER);
			$documents_master->filterByReference1($ordn);
		}
	}
}
