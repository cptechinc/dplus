<?php namespace Dplus\Docm\Finder\Subsystem\So;
// Dplus Model
use DocumentQuery;
use SalesOrder as SoModel;
use SalesOrderDetailQuery, SalesOrderDetail;
use SalesHistoryDetailQuery, SalesHistoryDetail;
// Dplus Mso
use Dplus\Mso\So;
// Dplus Docm
use Dplus\Docm\Finder\TagRef1;
use Dplus\Docm\Finder as Finders;

/**
 * Finder\Subsystem\Mso\SalesOrder
 * Decorator for DocumentQuery to find Documents in Database related to Sales Order
 */
class SalesOrder extends TagRef1 {
	const TAG = ['SO', 'AR'];

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
	 * Return Documents related to Sales Order number
	 * @param  string $ordn  Sales Order #
	 * @return ObjectCollection|Document[]
	 */
	public function find($ordn) {
		$q = $this->queryBase();
		$this->filterSales($q, SoModel::get_paddedordernumber($ordn));
		return $q->find();
	}

	/**
	 * Return the number of Documents related to Sales Order number
	 * @param  string $ordn  Sales Order #
	 * @return int
	 */
	public function count($ordn) {
		$q = $this->queryBase();
		$this->filterSales($q, SoModel::get_paddedordernumber($ordn));
		return $q->count();
	}

/* =============================================================
	Query Decorator Functions
============================================================= */
	/**
	 * Adds Filter Conditions to the Documents Query
	 * to find Documents associated with a Sales Order
	 * @param  DocumentQuery $q     Query
	 * @param  string        $ordn  Sales Order #
	 * @return DocumentQuery
	 */
	public function filterSales(DocumentQuery $q, $ordn) {
		$this->initColumns();
		$ordn = SoModel::get_paddedordernumber($ordn);

		$conditions = [
			$this->addCondtionSonbrRef1($q, $ordn),
			$this->addCondtionArInvnbrRef1($q, $ordn)
		];
		$vendorPOs =  $this-> getSoDetailVendorPonbrs($ordn);

		if (empty($vendorPOs) === false) {
			$conditions[] = Finders\Subsystem\Po\PurchaseOrder::instance()->addConditionPonbr($q, $vendorPOs);
		}
		$q->where($conditions, 'or');
		return $q;
	}

	/**
	 * Add Query Condition for Sales Order # for Ref1
	 * @param  DocumentQuery $q     Query
	 * @param  string        $ordn  Sales Order #
	 * @param  string        $name  Condition Name
	 * @return string
	 */
	private function addCondtionSonbrRef1(DocumentQuery $q, $ordn, $name = 'cond_so') {
		$columns = self::getColumns();
		$q->condition('tag_so', "Document.{$columns->tag} = ?", self::TAG[0]);
		$q->condition('reference1_so', "Document.{$columns->reference1} = ?", $ordn);
		$q->combine(array('tag_so', 'reference1_so'), 'and', $name) ;
		return $name;
	}

	/**
	 * Add Query Condition for AR Invoice # for Ref1
	 * @param  DocumentQuery $q     Query
	 * @param  string        $ordn  Sales Order #
	 * @param  string        $name  Condition Name
	 * @return string
	 */
	private function addCondtionArInvnbrRef1(DocumentQuery $q, $ordn, $name = 'cond_invoices') {
		$columns = self::getColumns();
		$q->condition('tag_invoices', "Document.{$columns->tag} = ?", self::TAG[1]);
		$q->condition('reference1_invoices', "Document.{$columns->reference1} = ?", $ordn);
		$q->combine(array('tag_invoices', 'reference1_invoices'), 'and', $name);
		return $name;
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Return Vendor POs associated with Sales Order
	 * @param  string $ordn Sales Order #
	 * @return array
	 */
	private function getSoDetailVendorPonbrs($ordn) {
		$q = SalesOrderDetailQuery::create();
		$q->select(SalesOrderDetail::aliasproperty('vendorpo'));

		if (So\SalesHistory::instance()->exists($ordn)) {
			$q = SalesHistoryDetailQuery::create();
			$q->select(SalesHistoryDetail::aliasproperty('vendorpo'));
		}
		$q->filterByOrdernumber($ordn);
		return $q->find()->toArray();
	}
}
