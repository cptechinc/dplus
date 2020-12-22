<?php
	use Twig\TwigFilter;
	use Purl\Url;
	use ProcessWire\Regexer;

	$convertdate = new Twig_Function('convertdate', function ($date, $format = 'm/d/Y') {
		$date = date($format, strtotime($date));
		return $date == '11/30/-0001' ? '' : $date;
	});
	$config->twig->addFunction($convertdate);

	$yesno = new Twig_Function('yesorno', function ($trueorfalse) {
		return ($trueorfalse === true || strtoupper($trueorfalse) == 'Y') ? 'yes' : 'no';
	});
	$config->twig->addFunction($yesno);

	$filter = new TwigFilter('currency', function ($money) {
		return number_format($money, 2, '.', ",");
	});
	$config->twig->addFilter($filter);

	$filter = new TwigFilter('round', function ($number) {
		return number_format($number, 4, '.', ",");
	});
	$config->twig->addFilter($filter);

	$filter = new TwigFilter('convertdate', function ($date, $format = 'm/d/Y') {
		$date = date($format, strtotime($date));
		return $date == '11/30/-0001' ? '' : $date;
	});
	$config->twig->addFilter($filter);

	$filter = new TwigFilter('yesorno', function ($trueorfalse) {
		return ($trueorfalse === true || strtoupper($trueorfalse) == 'Y') ? 'yes' : 'no';
	});
	$config->twig->addFilter($filter);

	$filter = new TwigFilter('bool', function ($tf) {
		return boolval($tf);
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('attrJS', function ($string, $jsprepend = true) {
		$replace = array(
			' ' => '+',
			'=' => 'eq',
			'%' => 'per',
			'+' => 'plus'
		);
		$string = str_replace(array_keys($replace), array_values($replace), $string);
		return $jsprepend ? "js-$string" : $string;
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('array_key_exists', function ($array, $key) {
		return array_key_exists($key, $array);
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('phone_us', function ($phone) {
		$regexer = new Regexer;
		return $regexer->phone_us_10($phone);
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('phone_us_ext', function ($phone) {
		$regexer = new Regexer;
		return $regexer->phone_us_ext($phone);
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('phone_us_x', function ($phone) {
		$regexer = new Regexer;
		return $regexer->phone_us_x($phone);
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('base64_encode', function ($str) {
		return base64_encode($str);
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('purl', function ($url) {
		if (strlen($url)) {
			$url = new Url($url);
			return $url->getUrl();
		} else {
			return false;
		}
	});
	$config->twig->addFilter($filter);

	$matches_search = new Twig_Function('matches_search', function ($subject, $query) {
		$regex = "/(".str_replace('-', '\-?', $query).")/i";
		$contains = preg_match($regex, $subject, $matches);

		if ($contains) {
			$highlight = "<span class='highlight'>" . $matches[0] . "</span>";
			return preg_replace($regex, $highlight, $subject);
		}  else {
			return $subject;
		}
	});
	$config->twig->addFunction($matches_search);

	$filter = new Twig_Filter('array_values', function ($array) {
		return array_values($array);
	});
	$config->twig->addFilter($filter);

	$array_keys = new Twig_Function('array_keys', function ($array) {
		return array_keys($array);
	});
	$config->twig->addFunction($array_keys);

	$filter = new Twig_Filter('shorten', function ($string, $length = 0, $append = '') {
		$newstring = substr($string, 0, $length);
		$newstring .= strlen($string) > $length ? $append : '';
		return $newstring;
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('dynamicproperty', function ($object, $property) {
		return $object->$property;
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('urlencode', function ($string) {
		return urlencode($string);
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('objproperty', function ($object, $property) {
		return $object->$property;
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('stripslashes', function ($str) {
		return stripslashes($str);
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('htmlattributes', function ($array) {
		$attrnoval = ['readonly', 'disabled'];
		$attr = [];

		foreach ($array as $key => $value) {
			if (in_array($key, $attrnoval)) {
				$attr[] = $value === true ? $key : '';
			} else {
				$attr[] = "$key=\"$value\"";
			}
		}
		return trim(implode(' ', array_filter($attr)));
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('join2', function ($str, $glue = '') {
		if (is_array($str)) {
			return implode($glue, $str);
		}
		return trim($str);
	});
	$config->twig->addFilter($filter);
