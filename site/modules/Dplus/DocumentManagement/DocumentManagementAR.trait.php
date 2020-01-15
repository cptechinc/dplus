<?php namespace ProcessWire;

use Purl\Url;

use DocumentFoldersQuery, DocumentFolders;
use DocumentsQuery, Documents;
use SalesOrderQuery, SalesOrder;
use SalesHistoryQuery, SalesHistory;
use SalesHistoryDetailQuery, SalesHistoryDetail;

trait DocumentManagementAR {
	public function get_arinvoice_docsURL($invnbr, $folder, $document) {
		$url = new Url($this->wire('pages')->get('pw_template=ci-documents')->url);
		$url->query->set('invnbr', $invnbr);
		$url->query->set('folder', $folder);
		$url->query->set('document', $document);
		return $url->getUrl();
	}

	/**
	 * Return Documents objects filtered by the tag1, reference1 fields for an AR Invoice
	 * @param  string $invnbr               AR Invoice Number
	 * @return Documents[]|ObjectCollection
	 */
	public function get_arinvoicedocuments($invnbr) {
		$invnbr = SalesOrder::get_paddedordernumber($invnbr);
		$documents_master = DocumentsQuery::create();
		$documents_master->filterByTag(self::TAG_ARINVOICE);
		$documents_master->filterByReference1($invnbr);
		return $documents_master->find();
	}

	/**
	 * Return the number of Documents found filtered by the tag1, reference1 fields for an AR Invoice
	 * @param  string $invnbr AR Invoice Number
	 * @return int            Number of Sales Order Documents found
	 */
	public function count_arinvoicedocuments($invnbr) {
		$invnbr = SalesOrder::get_paddedordernumber($invnbr);
		$documents_master = DocumentsQuery::create();
		$documents_master->filterByTag(self::TAG_ARINVOICE);
		$documents_master->filterByReference1($invnbr);
		return $documents_master->count();
	}

	/**
	 * Return Documents objects filtered by the tag1, reference1 fields for an AR Payment
	 * @param  string $invnbr               AR Invoice Number
	 * @param  string $checknbr             Customer Check Number
	 * @return Documents[]|ObjectCollection
	 */
	public function get_arpaymentdocuments($invnbr, $checknbr = '') {
		$invnbr = SalesOrder::get_paddedordernumber($invnbr);
		$documents_master = DocumentsQuery::create();
		$this->filter_arpayment_conditions($documents_master, $invnbr, $checknbr);
		return $documents_master->find();
	}

	/**
	 * Return the number of Documents found filtered by the tag1, reference1 fields for an AR Payment
	 * @param  string $invnbr    AR Invoice Number
	 * @param  string $checknbr  Customer Check Number
	 * @return int               Number of Sales Order Documents found
	 */
	public function count_arpaymentdocuments($invnbr, $checknbr = '') {
		$invnbr = SalesOrder::get_paddedordernumber($invnbr);
		$documents_master = DocumentsQuery::create();
		$this->filter_arpayment_conditions($documents_master, $invnbr, $checknbr);
		return $documents_master->count();
	}

	/**
	 * Adds Filters to the Query for AR Payment
	 * @param  DocumentsQuery $documents_master Query to apply filters to
	 * @param  string         $invnbr           AP Invoice Number
	 * @param  string         $checknbr         Check Number if provided
	 * @return string
	 */
	public function filter_arpayment_conditions(DocumentsQuery $documents_master, $invnbr, $checknbr = '') {
		$column_tag = Documents::get_aliasproperty('tag');
		$column_reference1 = Documents::get_aliasproperty('reference1');
		$column_reference2 = Documents::get_aliasproperty('reference2');

		if ($checknbr) {
			$q = SalesHistoryDetailQuery::create();
			$q->filterByOrdernumber($invnbr);
			$ponbrs = $q->select(SalesHistoryDetail::get_aliasproperty('vendorpo'))->find()->toArray();
			$custID = SalesHistoryQuery::create()->select(SalesHistory::get_aliasproperty('custid'))->findOne();

			$column_tag = Documents::get_aliasproperty('tag');
			$column_reference1 = Documents::get_aliasproperty('reference1');

			// Create Invoices Filter
			$documents_master->condition('tag_invoices', "Documents.$column_tag = ?", self::TAG_ARINVOICE);
			$documents_master->condition('reference1_invoices', "Documents.$column_reference1 = ?", $invnbr);
			$documents_master->combine(array('tag_invoices', 'reference1_invoices'), 'and', 'cond_invoices');

			// Create Vendor PO Filter
			$documents_master->condition('tag_vendorpo', "Documents.$column_tag = ?", self::TAG_VENDORPO);
			$documents_master->condition('reference1_vendorpo', "Documents.$column_reference1 IN ?", $ponbrs);
			$documents_master->combine(array('tag_vendorpo', 'reference1_vendorpo'), 'and', 'cond_vendorpo');

			// Create Customer Filter
			$documents_master->condition('tag_customer', "Documents.$column_tag = ?", self::TAG_CUSTOMER);
			$documents_master->condition('reference1_customer', "Documents.$column_reference1 = ?", $custID);
			$documents_master->combine(array('tag_customer', 'reference1_customer'), 'and', 'cond_customer');

			// Create Customer Checks Filter
			$documents_master->condition('tag_customer_check', "Documents.$column_tag = ?", self::TAG_CUSTOMER);
			$documents_master->condition('reference1_customer_check', "Documents.$column_reference1 = ?", $custID);
			$documents_master->condition('tag_checks', "Documents.$column_tag = ?", self::TAG_AR_CHECKS);
			$documents_master->condition('reference2_checks', "Documents.$column_reference2 = ?", $checknbr);
			$documents_master->combine(array('tag_customer_check', 'reference1_customer_check', 'tag_checks', 'reference2_checks'), 'and', 'cond_checks');

			// Combine and Apply Filters
			$documents_master->where(array('cond_invoices', 'cond_vendorpo', 'cond_customer', 'cond_checks'), 'or');
		} else {
			$documents_master->filterByTag(self::TAG_ARINVOICE);
			$documents_master->filterByReference1($invnbr);
		}
	}
}
