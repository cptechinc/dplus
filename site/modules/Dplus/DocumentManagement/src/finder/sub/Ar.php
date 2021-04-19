<?php namespace Dplus\DocManagement\Finders;
// Purl
use Purl\Url;
// Propel
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use DocumentFolderQuery, DocumentFolder;
use DocumentQuery, Document;
use SalesOrderQuery, SalesOrder;
// ProcessWire
use ProcessWire\WireData;
// Dplus Validators
use Dplus\CodeValidators\Mpo as MpoValidator;

/**
 * Accounts Receivable Document Finder
 *
 * Decorator for DocumentQuery to find AR Related Documents in Database
 */
class Ar extends Finder {
/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return Documents objects filtered by the tag1, reference1 fields for an AR Invoice
	 * @param  string $invnbr               AR Invoice Number
	 * @return Documents[]|ObjectCollection
	 */
	public function getDocumentsInvoice($invnbr) {
		$invnbr = SalesOrder::get_paddedordernumber($invnbr);
		$q = $this->docQuery();
		$q->filterByTag(self::TAG_ARINVOICE);
		$q->filterByReference1($invnbr);
		return $q->find();
	}

	/**
	 * Return the number of Documents found filtered by the tag1, reference1 fields for an AR Invoice
	 * @param  string $invnbr AR Invoice Number
	 * @return int            Number of Sales Order Documents found
	 */
	public function countDocumentsInvoice($invnbr) {
		$invnbr = SalesOrder::get_paddedordernumber($invnbr);
		$q = $this->docQuery();
		$q->filterByTag(self::TAG_ARINVOICE);
		$q->filterByReference1($invnbr);
		return $q->count();
	}

	/**
	 * Return Documents objects filtered by the tag1, reference1 fields for an AR Payment
	 * @param  string $invnbr               AR Invoice Number
	 * @param  string $checknbr             Customer Check Number
	 * @return Documents[]|ObjectCollection
	 */
	public function getDocumentsPayment($invnbr, $checknbr = '') {
		$invnbr = SalesOrder::get_paddedordernumber($invnbr);
		$q = $this->docQuery();
		$this->addConditionPayment($q, $invnbr, $checknbr);
		return $q->find();
	}

	/**
	 * Return the number of Documents found filtered by the tag1, reference1 fields for an AR Payment
	 * @param  string $invnbr    AR Invoice Number
	 * @param  string $checknbr  Customer Check Number
	 * @return int               Number of Sales Order Documents found
	 */
	public function countDocumentsPayment($invnbr, $checknbr = '') {
		$invnbr = SalesOrder::get_paddedordernumber($invnbr);
		$q = $this->docQuery();
		$this->addConditionPayment($q, $invnbr, $checknbr);
		return $q->count();
	}

/* =============================================================
	Query Filtering (Decorations) Functions
============================================================= */
	/**
	 * Adds Filters to the Query for AR Payment
	 * @param  DocumentQuery $q Query to apply filters to
	 * @param  string         $invnbr           AP Invoice Number
	 * @param  string         $checknbr         Check Number if provided
	 * @return string
	 */
	public function addConditionPayment(DocumentQuery $q, $invnbr, $checknbr = '') {
		$this->initColumns();
		$conditions = array();

		$finderSo = new DocFinders\SalesOrder();
		$finderSo->initColumns();

		if (empty($checknbr)) {
			$finderSo->filterSales($q, $invnbr);
			return $q;
		}

		// Create Invoice Filter
		$conditions[] = $this->addConditionInvoices($q, $invnbr);
		// Create Vendor PO Filter
		if ($finderSo->doesOrderHavePos($ordn)) {
			$cond = $finderSo->filterSalesVendorpo($q, $ordn);

			if ($cond) {
				$conditions[] = $cond;
			}
		}

		if ($this->invoiceHasCustid($invnbr)) {
			// Create Customer Filter
			$conditions[] = $this->addConditionCustomer($q, $invnbr);
			// Create Customer Checks Filter
			$conditions[] = $this->addConditionArCheck($q, $invnbr, $checknbr);
		}
		$q->where($conditions, 'or');
	}

	/**
	 * Add Invoice Condition to Document Query
	 * @param  DocumentQuery $q
	 * @param  string        $invnbr     Invoice Number
	 * @return string
	 */
	protected function addConditionInvoices(DocumentQuery $q, $invnbr) {
		$name = 'cond_invoices';
		$q->condition('tag_invoices', "Document.{$this->columns->tag} = ?", self::TAG_ARINVOICE);
		$q->condition('reference1_invoices', "Document.{$this->columns->reference1} = ?", $invnbr);
		$q->combine(array('tag_invoices', 'reference1_invoices'), 'and', $name);
		return $name;
	}

	/**
	 * Add Customer Condition to Document Query
	 * @param  DocumentQuery $q
	 * @param  string        $invnbr     Invoice Number
	 * @return string
	 */
	protected function addConditionCustomer(DocumentQuery $q, $invnbr) {
		$name = 'cond_customer';
		$custID = $this->getInvoiceCustid($invnbr);
		$q->condition('tag_customer', "Document.{$this->columns->tag} = ?", self::TAG_CUSTOMER);
		$q->condition('reference1_customer', "Document.{$this->columns->reference1} = ?", $custID);
		$q->combine(array('tag_customer', 'reference1_customer'), 'and', $name);
		return $name;
	}

	/**
	 * Add Vendor Condition to Document Query
	 * @param  DocumentQuery $q
	 * @param  string        $invnbr     Invoice Number
	 * @param  string        $checknbr   Check Number
	 * @return string
	 */
	protected function addConditionArCheck(DocumentQuery $q, $invnbr, $checknbr) {
		$name = 'cond_checks';
		$custID = $this->getInvoiceCustid($invnbr);
		$q->condition('tag_customer_check', "Document.{$this->columns->tag} = ?", self::TAG_CUSTOMER);
		$q->condition('reference1_customer_check', "Document.{$this->columns->reference1} = ?", $custID);
		$q->condition('tag_checks', "Document.{$this->columns->tag} = ?", self::TAG_AR_CHECKS);
		$q->condition('reference2_checks', "Document.{$this->columns->reference2} = ?", $checknbr);
		$q->combine(array('tag_customer_check', 'reference1_customer_check', 'tag_checks', 'reference2_checks'), 'and', $name);
		return $name;
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Return Cust ID for Invoice #
	 * @param  string $ordn Sales Order Number
	 * @var string
	 */
	protected function getInvoiceCustid($ordn) {
		$ordn = SalesOrder::get_paddedordernumber($ordn);
		$q = SalesHistoryQuery::create();
		$q->select(SalesHistory::aliasproperty('custid'));
		$q->filterByOrdernumber($ordn);
		return $q->findOne();
	}

	/**
	 * Return if Invoice has Customer ID
	 * @param  string $ordn Sales Order Number
	 * @return boolval
	 */
	public function invoiceHasCustid($ordn) {
		$ordn = SalesOrder::get_paddedordernumber($ordn);
		$q = SalesHistoryQuery::create();
		$q->select(SalesHistory::aliasproperty('custid'));
		$q->filterByOrdernumber($ordn);
		return boolval($q->findOne());
	}
}
