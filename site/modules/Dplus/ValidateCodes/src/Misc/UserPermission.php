<?php namespace Dplus\CodeValidators;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Models
use FuncpermQuery, Funcperm;
// ProcessWire
use ProcessWire\WireData;

/**
 * UserPermission
 *
 * Class for Validating User Permissions
 */
class UserPermission extends WireData {
	/**
	 * Return if Function ID is Valid
	 * @param  string|array $code Permission Code
	 * @return bool
	 */
	public function permission($code) {
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
		if ($this->permission($code) === false) {
			return true;
		}
		$q = FuncpermQuery::create();
		$q->select('permission');
		$q->filterByFunction($code);
		$q->filterByLoginid($userID);
		return $q->findOne() == Funcperm::HAS_PERMISSION;
	}
}
