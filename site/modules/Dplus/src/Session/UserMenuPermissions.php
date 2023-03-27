<?php namespace Dplus\Session;
// ProcessWire
use ProcessWire\WireData;
// Pauldro ProcessWire
use Pauldro\ProcessWire\WireArrayWireData;


/**
 * UserMenuPermissions
 * Handles retrieval of UserMenuPermissions record for current User
 * 
 * @static   UserMenuPermissions  instance()  Return Instance
 */
class UserMenuPermissions extends WireData {
	const NAMESPACE = 'menu-permissions';

	private static $instance;


	public static function instance() {
		if (empty(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function reload() {
		$list = $this->generateList();
		$this->setList($list);
		return $this;
	}

	/**
	 * Return Permission List
	 * @return WireArrayWireData
	 */
	public function list() {
		$rawList = $this->getListRaw();

		if (empty($rawList) === false) {
			$list = WireArrayWireData::fromArray($rawList);
			return $list;
		}
		
		$list = $this->generateList();
		$this->setList($list);
		return $list;
	}

	/**
	 * Create List, filter functions based on dev, configs
	 * @return WireArrayWireData
	 */
	public function generateList() {
		$user   = $this->user;
		$config = $this->config;

		$permittedFunctions = new WireArrayWireData();
		$permittedFunctions->setArray($user->dplusPermissions->getArray());

		// Remove functions that are in development, and user isn't logged in PW
		if ($config->hideFunctions->dev && $user->isLoggedin() === false) { 
			$permittedFunctions->not('code=' . implode('|', $config->hideFunctions->dev));
		}

		// Remove functions hidden for this company
		if ($config->hideFunctions->cmp) {
			$permittedFunctions->not('code=' . implode('|', $config->hideFunctions->cmp));
		}
		return $permittedFunctions;
	}

	/**
	 * Return if User can Access function
	 * @param  string $code
	 * @return bool
	 */
	public function canAccess($code) {
		$list = $this->list();
		return $list->has($code);
	}

/* =============================================================
	Session
============================================================= */
	/**
	 * Save Permissions to Session
	 * @param WireArrayWireData $list
	 */
	public function setList(WireArrayWireData $list) {
		$this->wire('session')->setFor('user', self::NAMESPACE, $list->toArray());
	}

	/**
	 * Return Session Permission List
	 * @return WireArrayWireData
	 */
	public function getListRaw() {
		return $this->wire('session')->getFor('user', self::NAMESPACE);
	}

	/**
	 * Return Session Permission List
	 * @return WireArrayWireData
	 */
	public function getList() {
		$list = WireArrayWireData::fromArray($this->getListRaw());
		return $list;
	}

	/**
	 * Delete Session Permission List
	 */
	public function deleteList() {
		$this->wire('session')->removeFor('user', self::NAMESPACE);
	}
}