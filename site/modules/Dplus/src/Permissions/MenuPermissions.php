<?php namespace Dplus\Permissions;
// ProcessWire
use ProcessWire\WireArray;
use ProcessWire\WireData;
// Pauldro ProcessWire
use Pauldro\ProcessWire\WireArrayWireData;
// Dplus Models
use FuncpermQuery, Funcperm;

/**
 * Validates User's Permissions
 */
class MenuPermissions extends WireData {
	protected static $instance;

	public static function instance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Return if Function ID is Valid
	 * @param  string|array $code Permission Code
	 * @return bool
	 */
	public function permissionExists($code) {
		$q = FuncpermQuery::create();
		$q->filterByFunction($code);
		return boolval($q->count());
	}

	/**
	 * Return if Login ID has Permission
	 * @param  string          $userID  User / Login ID
	 * @param  string|array    $code    Permission Code
	 * @return bool
	 */
	public function userHasPermission($userID, $code) {
		// If Permission code does not exist, allow them access
		if ($this->permissionExists($code) === false) {
			return true;
		}
		$q = FuncpermQuery::create();
		$q->select('permission');
		$q->filterByFunction($code);
		$q->filterByLoginid($userID);
		return $q->findOne() == Funcperm::HAS_PERMISSION;
	}

	/**
	 * Return User's Allowed Permissions
	 * @param  string $user ID
	 * @return array
	 */
	public function userPermissions($userID) {
		$q = FuncpermQuery::create();
		$q->select('function');
		$q->filterByLoginid($userID);
		$q->filterByPermission(Funcperm::HAS_PERMISSION);
		return $q->find()->toArray();
	}

	/**
	 * Return User's Allowed Permissions
	 * @param  string $user ID
	 * @return WireArray
	 */
	public function userPermissionsWireArray($userID) {
		$permissions = $this->userPermissions($userID);
		$permitted = new WireArrayWireData();

		foreach ($permissions as $perm) {
			$p = new WireData();
			$p->code = $perm;
			$permitted->set($perm, $p);
		}
		return $permitted;
	}
}