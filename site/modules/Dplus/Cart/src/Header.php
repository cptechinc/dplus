<?php namespace Dplus\Cart;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
// Dpluso Models
use CarthedQuery, Carthed;
// ProcessWire
use ProcessWire\WireData;
// Dplus Code Validators
use Dplus\CodeValidators as Validators;

class Header extends WireData {
	private $sessionID;

/* =============================================================
	Constructor
============================================================= */
	public function __construct() {
		$this->sessionID = session_id();
	}

/* =============================================================
	Setters
============================================================= */
	/**
	 * Set Session ID
	 * @param string $sessionID
	 */
	public function setSessionid($sessionID) {
		$this->sessionID = $sessionID;
	}

/* =============================================================
	Query Getters
============================================================= */
	/**
	 * Return Base Query
	 * @return CarthedQuery
	 */
	public function query() {
		return CarthedQuery::create();
	}

	/**
	 * Return Query filtered by Sessionid
	 * @return CarthedQuery
	 */
	public function querySessionid() {
		$q = $this->query();
		$q->filterBySessionid($this->sessionID);
		return $q;
	}

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return New Cart Header
	 * @return Carthed
	 */
	public function new() {
		$c = new Carthed();
		$c->setSessionid($this->sessionID);
		return $c;
	}

/* =============================================================
	Read Functions
============================================================= */
	public function exists() {
		$q = $this->querySessionid();
		return boolval($q->count());
	}

	public function header() {
		return $this->querySessionid()->findOne();
	}

	/**
	 * Returns if Cart Customer has been defined
	 * @return bool
	 */
	public function hasCustid() {
		$q = $this->querySessionid();
		$q->select('custid');
		$q->filterByCustid('', Criteria::NOT_EQUAL);
		return boolval($q->count());
	}

	/**
	 * Returns Customer ID
	 * @return string
	 */
	public function getCustid() {
		$q = $this->querySessionid();
		$q->select('custid');
		return $q->findOne();
	}

	/**
	 * Returns if Cart  Customer Shipto ID has been defined
	 * @return bool
	 */
	public function hasShiptoid() {
		$q = $this->querySessionid();
		$q->select('shiptoid');
		$q->filterByShiptoid('', Criteria::NOT_EQUAL);
		return boolval($q->count());
	}

	/**
	 * Returns  Customer Shipto ID
	 * @return string
	 */
	public function getShiptoid() {
		$q = $this->querySessionid();
		$q->select('shiptoid');
		return $q->findOne();
	}

/* =============================================================
	Update Functions
============================================================= */
	/**
	 * Sets Cart Customer ID
	 * @return string
	 */
	public function setCustid($custID) {
		$cart = $this->new();
		$this->exists();
		if ($this->exists()) {
			$cart = $this->header();
		}
		$validate = new Validators\Mar();
		if ($validate->custid($custID)) {
			$cart->setCustid($custID);
		}
		return boolval($cart->save());
	}

	/**
	 * Sets Cart Customer Shipto ID
	 * @return string
	 */
	public function setShiptoid($shiptoID) {
		$cart = $this->new();

		if ($this->exists()) {
			$cart = $this->header();
		}
		$cart->setShiptoid($shiptoID);
		return boolval($cart->save());
	}
}
