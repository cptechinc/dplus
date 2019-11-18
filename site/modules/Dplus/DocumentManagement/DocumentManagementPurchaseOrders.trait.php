<?php namespace ProcessWire;

use Purl\Url;
use DocumentFoldersQuery, DocumentFolders;
use DocumentsQuery, Documents;
use PurchaseOrderQuery, PurchaseOrder;
use ApInvoiceQuery, ApInvoice;

trait DocumentManagementPurchaseOrders {
	public function mpo_init() {
		$this->addHook('Page(pw_template=purchase-order-view)::documentload', function($event) {
			$page = $event->object;
			$folder   = $event->arguments(0);
			$document = $event->arguments(1);
			$ponbr    = $event->arguments(2);

			if (PurchaseOrderQuery::create()->filterByPonbr($ponbr)->count()) {
				$event->return = $this->get_purchaseorder_docsURL($ponbr, $folder, $document);
			} elseif (ApInvoiceQuery::create()->filterByInvoicenumber($ponbr)->count()) {
				$event->return = $this->get_apinvoice_docsURL($ponbr, $folder, $document);
			}
		});

		$this->addHook('Page(pw_template=purchase-order-documents)::documentload', function($event) {
			$page = $event->object;
			$folder   = $event->arguments(0);
			$document = $event->arguments(1);
			$ponbr    = $event->arguments(2);

			if (PurchaseOrderQuery::create()->filterByPonbr($ponbr)->count()) {
				$event->return = $this->get_purchaseorder_docsURL($ponbr, $folder, $document);
			} elseif (ApInvoiceQuery::create()->filterByInvoicenumber($ponbr)->count()) {
				$event->return = $this->get_apinvoice_docsURL($ponbr, $folder, $document);
			}
		});
	}

	public function get_purchaseorder_docsURL($ponbr, $folder, $document) {
		$url = new Url($this->wire('pages')->get('pw_template=purchase-order-documents')->url);
		$url->query->set('ponbr', $ponbr);
		$url->query->set('folder', $folder);
		$url->query->set('document', $document);
		return $url->getUrl();
	}

	public function get_apinvoice_docsURL($invnbr, $folder, $document) {
		$url = new Url($this->wire('pages')->get('pw_template=purchase-order-documents')->url);
		$url->query->set('invnbr', $invnbr);
		$url->query->set('folder', $folder);
		$url->query->set('document', $document);
		return $url->getUrl();
	}

	/**
	 * Return Documents objects filtered by the tag1, reference1 fields for a Purchase Order
	 * @param  string $ponbr                      Purchase Order Number
	 * @return Documents[]|ObjectCollection
	 */
	public function get_purchaseorderdocuments($ponbr) {
		$ponbr = PurchaseOrder::get_paddedponumber($ponbr);
		$documents_master = DocumentsQuery::create();
		$documents_master->filterByTag(self::TAG_VENDORPO);
		$documents_master->filterByReference1($ponbr);
		return $documents_master->find();
	}

	/**
	 * Return the number of Documents found filtered by the tag1, reference1 fields for a Purchase Order
	 * @param  string $ponbr Purchase Order Number
	 * @return int          Number of Purchase Order Documents found
	 */
	public function count_purchaseorderdocuments($ponbr) {
		$ponbr = PurchaseOrder::get_paddedponumber($ponbr);
		$documents_master = DocumentsQuery::create();
		$documents_master->filterByTag(self::TAG_VENDORPO);
		$documents_master->filterByReference1($ponbr);
		return $documents_master->count();
	}

	/**
	 * Return the number of Documents found filtered by the tag1, reference1 fields for a Purchase Order
	 * @param  string $invnbr Purchase Order Number
	 * @return int          Number of Purchase Order Documents found
	 */
	public function get_purchasehistorydocuments($invnbr) {
		$documents_master = DocumentsQuery::create();
		$this->filter_purchasehistoryconditions($documents_master, $invnbr);
		return $documents_master->find();
	}

	/**
	 * Return the number of Documents found filtered by the tag1, reference1 fields for a Purchase Order in History
	 * @uses self::filter_purchasehistoryconditions()
	 *
	 * @param  string $invnbr Purchase Order Number
	 * @return int          Number of Purchase History Documents found
	 */
	public function count_purchasehistorydocuments($invnbr) {
		$documents_master = DocumentsQuery::create();
		$this->filter_purchasehistoryconditions($documents_master, $invnbr);
		return $documents_master->count();
	}

	/**
	 * Adds Filter Conditions to the Documents Query to find Documents associated with a Sales History Order
	 * @param  DocumentsQuery $documents_master Query to add filters to
	 * @param  string         $invnbr             Sales Order Number
	 * @return void
	 */
	protected function filter_purchasehistoryconditions(DocumentsQuery $documents_master, $invnbr) {
		$documentmanagement = $this->wire('modules')->get('DocumentManagement');
		$q = ApInvoiceQuery::create();
		$q->filterByInvoicenumber($invnbr);
		$q->select(ApInvoice::get_aliasproperty('ponbr'));
		$ponbr = $q->findOne();

		if ($ponbr) {
			$column_tag = Documents::get_aliasproperty('tag');
			$column_reference1 = Documents::get_aliasproperty('reference1');
			$column_reference2 = Documents::get_aliasproperty('reference2');

			// Create Invoices Filter
			$documents_master->condition('tag_invoices', "Documents.$column_tag = ?", self::TAG_APINVOICE);
			$documents_master->condition('reference1_invoices', "Documents.$column_reference1 = ?", $invnbr);
			$documents_master->combine(array('tag_invoices', 'reference1_invoices'), 'and', 'cond_invoices') ;

			// Create Invoices Filter
			$documents_master->condition('tag_invoices', "Documents.$column_tag = ?", self::TAG_APINVOICE);
			$documents_master->condition('reference2_invoices', "Documents.$column_reference2 = ?", $invnbr);
			$documents_master->combine(array('tag_invoices', 'reference2_invoices'), 'and', 'cond_invoices2') ;

			// Create Vendor PO Filter
			$documents_master->condition('tag_vendorpo', "Documents.$column_tag = ?", self::TAG_VENDORPO);
			$documents_master->condition('reference1_vendorpo', "Documents.$column_reference1 = ?", $ponbr);
			$documents_master->combine(array('tag_vendorpo', 'reference1_vendorpo'), 'and', 'cond_vendorpo');
			$documents_master->where(array('cond_invoices', 'cond_invoices2', 'cond_vendorpo'), 'or');
		} else {
			$documents_master->filterByTag(self::TAG_VENDORPO);
			$column_reference1 = Documents::get_aliasproperty('reference1');
			$column_reference2 = Documents::get_aliasproperty('reference2');

			// Create Invoices Filter
			$documents_master->condition('reference1_invoices', "Documents.$column_reference1 = ?", $invnbr);
			$documents_master->condition('reference2_invoices', "Documents.$column_reference2 = ?", $invnbr);
			$documents_master->combine(array('reference1_invoices', 'reference2_invoices'), 'or', 'cond_invoices');
			$documents_master->where(array('cond_invoices'));
		}
	}
}
