<?php namespace Dplus\Docm\Finders\Mar;
// Dplus Model
use DocumentQuery;
use SalesOrder as SoModel;
// Dplus Mso
use Dplus\Mso\So;
// Dplus Docm
use Dplus\Docm\Finders;

/**
 * ArPayment
 * Decorator for DocumentQuery to find Documents in Database related to AR Invoice #, Check #
 */
class ArPayment extends Finder {
	const TAGS = [
		'customer'	=> 'CU',
		'ar-checks' => 'RC'
	];
	protected static $instance;

	/** @return self */
	public static function instance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return Documents related to Invoice #
	 * @param  string $invnbr	Invoice #
	 * @param  string $checknbr Check Number
	 * @return ObjectCollection|Document[]
	 */
	public function find($invnbr, $checknbr = '') {
		$q = $this->query();
		$this->filterPayment($q, SoModel::get_paddedordernumber($invnbr), $checknbr);
		return $q->find();
	}

	/**
	 * Return the number of Documents related to Invoice #
	 * @param  string $invnbr  Invoice #
	 * @param  string $checknbr Check Number
	 * @return int
	 */
	public function count($invnbr, $checknbr = '') {
		$q = $this->query();
		$this->filterPayment($q, SoModel::get_paddedordernumber($invnbr), $checknbr);
		return $q->count();
	}

/* =============================================================
	Query Decorator Functions
============================================================= */
	/**
	 * Add Filter Conditions to the Documents Query
	 * to find Documents associated with an AR Payment
	 * @param  DocumentQuery $q 	   Query
	 * @param  string		 $invnbr   Invoice #
	 * @param  string		 $checknbr Check Number
	 * @return DocumentQuery
	 */
	public function filterPayment(DocumentQuery $q, $invnbr, $checknbr) {
		$finderSo = Finders\Mso\SalesOrder::instance();

		if (empty($checknbr)) {	
			$finderSo->filterSales($q, $invnbr);
			return $q;
		}
		$condtions = [];
		// Add AR Invoice Condition
		$conditions[] = $this->addConditionArInvnbr($q, $invnbr);
		// Get CustID and Related Vendor PO #
		$vendorPOs = $finderSo->getSoDetailVendorPonbrs($invnbr);
		$custID = $this->getInvoiceCustid($invnbr);

		if (empty($custID) && empty($vendorPOs)) {
			return $q;
		}

		// Add Vendor PO Conditions
		if (empty($vendorPOs) === false) {
			$conditions[] = Finders\Mpo\PurchaseOrder::instance()->addConditionPonbr($q, $vendorPOs);
		}
		
		// Add AR Check Filter
		if (empty($custID) === false) {
			$condtions[] = $this->addConditionArChecknbr($q, $invnbr, $checknbr);
		}
		$q->where($conditions, 'or');
		return $q;
	}


	/**
	 * Add Query Condition for AR Invoice # for Ref1
	 * @param  DocumentQuery $q 	  Query
	 * @param  string		 $invnbr  Invoice #
	 * @param  string		 $name	  Condition Name
	 * @return string
	 */
	private function addConditionArInvnbr(DocumentQuery $q, $invnbr, $name = 'cond_invoices') {
		$finderArInvoices = ArInvoice::instance();
		return $finderArInvoices->addConditionInvnbr($q, $invnbr, $name);
	}

	/**
	 * Add Query Condition for Customer's Check Number
	 * @param  DocumentQuery $q
	 * @param  string        $custID    Customer ID
	 * @param  string        $checknbr  Customer's Check Number
	 * @param  string        $name      Condition Name
	 * @return string
	 */
	private function addConditionArChecknbr(DocumentQuery $q, $custID, $checknbr, $name = 'cond_checks') {
		$columns = self::getColumns();
		$q->condition('tag_customer_check', "Document.{$columns->tag} = ?", self::TAGS['customer']);
		$q->condition('reference1_customer_check', "Document.{$columns->reference1} = ?", $custID);
		$q->condition('tag_checks', "Document.{$columns->tag} = ?", self::TAGS['ar-checks']);
		$q->condition('reference2_checks', "Document.{$columns->reference2} = ?", $checknbr);
		$q->combine(array('tag_customer_check', 'reference1_customer_check', 'tag_checks', 'reference2_checks'), 'and', $name);
		return $name;
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Return Customer ID associated with Invoice #
	 * @param  string $invnbr  Invoice #
	 * @return string
	 */
	private function getInvoiceCustid($invnbr) {
		if (So\SalesHistory::instance()->exists($invnbr)) {
			return So\SalesHistory::instance()->custid($invnbr);
		}
		So\SalesOrder::instance()->custid($invnbr);
	}
}
