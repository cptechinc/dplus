<?php namespace Controllers\Min\Itm\Xrefs;
// External Libraries, classes
Use Purl\Url as Purl;
// ProcessWire classes, modules
use ProcessWire\Page, ProcessWire\Itm as ItmModel;
// Validators
use Dplus\CodeValidators\Min as MinValidator;
use Dplus\Filters\Min\ItemMaster as ItemMasterFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

use Controllers\Min\Itm\Xrefs as ItmXrefs;
use Controllers\Min\Itm\Xrefs\Cxm;
use Controllers\Min\Itm\Xrefs\Vxm;
use Controllers\Min\Itm\Xrefs\Mxrfe;
use Controllers\Min\Itm\Xrefs\Upcx;
use Controllers\Min\Itm\Xrefs\Kim;

class Controller extends AbstractController {

	public static function xrefs($data) {
		return ItmXrefs::index($data);
	}

	public static function xrefsHandleCRUD($data) {
		return ItmXrefs::handleCRUD($data);
	}

	public static function cxm($data) {
		return Cxm::index($data);
	}

	public static function cxmHandleCRUD($data) {
		return Cxm::handleCRUD($data);
	}

	public static function vxm($data) {
		return Vxm::index($data);
	}

	public static function vxmHandleCRUD($data) {
		return Vxm::handleCRUD($data);
	}

	public static function mxrfe($data) {
		return Mxrfe::index($data);
	}

	public static function mxrfeHandleCRUD($data) {
		return Mxrfe::handleCRUD($data);
	}

	public static function upcx($data) {
		return Upcx::index($data);
	}

	public static function upcxHandleCRUD($data) {
		return Upcx::handleCRUD($data);
	}

	public static function kim($data) {
		return Kim::index($data);
	}

	public static function kimHandleCRUD($data) {
		return Kim::handleCRUD($data);
	}
}
