<?php namespace Dplus\Cart;
// Dpluso Models
use CartdetQuery, Cartdet;
// ProcessWire
use ProcessWire\WireData;

class Items extends WireData {
	private $sessionID;

/* =============================================================
	Constructor
============================================================= */
	public function __constructor() {
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
	 * @return CartdetQuery
	 */
	public function query() {
		return CartdetQuery::create();
	}

	/**
	 * Return Query filtered by Sessionid
	 * @return CartdetQuery
	 */
	public function querySessionid() {
		$q = $this->query();
		$q->filterBySessionid($this->sessionID);
		return $q;
	}

/* =============================================================
	Reads
============================================================= */
	/**
	 * Returns if Session has items in the cart
	 * @return bool Does the User's cart have items?
	 */
	public function hasItems() {
		return boolval($this->querySessionid()->count());
	}

	/**
	 * Return Items that are in the cart
	 * @return Cartdet[]|ObjectCollection
	 */
	public function getItems() {
		return $this->querySessionid()->find();
	}

	/**
	 * Return Cartdet
	 * @param  int      $linenbr Line Number
	 * @return Cartdet
	 */
	public function getItemByLine($linenbr = 1) {
		$q = $this->querySessionid();
		$q->filterByLinenbr($linenbr);
		return $q->findOne();
	}
}
