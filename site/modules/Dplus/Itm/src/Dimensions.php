<?php namespace Dplus\Min\Inmain\Itm;

class Dimensions extends WireData {
	const MODEL              = 'ItmDimension';
	const MODEL_KEY          = 'itemid';
	const DESCRIPTION        = 'Item Dimensions';
	const DESCRIPTION_RECORD = 'Item Dimensions';
	const RESPONSE_TEMPLATE  = 'Item {itemid} Dimensions {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'itm';

}
