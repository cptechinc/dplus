<?php namespace Controllers\Min\Itm\Xrefs;
// Purl URI Library
use Purl\Url as Purl;
// Mvc Controllers
use Controllers\Min\Itm\Base as ItmBase;

class Base extends ItmBase {
	const PERMISSION_ITMP = 'xrefs';

/* =============================================================
	URLs
============================================================= */
	public static function xrefsUrl($itemID) {
		return self::itmUrlFunction($itemID, 'xrefs');
	}

	public static function xrefUrlFunction($itemID, $function = '') {
		$url = new Purl(self::xrefsUrl($itemID));
		if ($function) {
			$url->path->add($function);
		}
		return $url->getUrl();
	}

	public static function xrefUrlUpcx($itemID) {
		return self::xrefUrlFunction($itemID, 'upcx');
	}

	public static function xrefUrlVxm($itemID) {
		return self::xrefUrlFunction($itemID, 'vxm');
	}

	public static function xrefUrlCxm($itemID) {
		return self::xrefUrlFunction($itemID, 'cxm');
	}

	public static function xrefUrlKim($itemID) {
		return self::xrefUrlFunction($itemID, 'kim');
	}

	public static function xrefUrlMxrfe($itemID) {
		return self::xrefUrlFunction($itemID, 'mxrfe');
	}

	public static function xrefUrlSubstitutes($itemID) {
		return self::xrefUrlFunction($itemID, 'substitutes');
	}

	public static function xrefUrlBom($itemID) {
		return self::xrefUrlFunction($itemID, 'bom');
	}

	public static function xrefUrlAddm($itemID) {
		return self::xrefUrlFunction($itemID, 'addm');
	}
}
