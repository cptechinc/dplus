<?php
	use Twig\TwigFilter;

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

	$filter = new TwigFilter('convertdate', function ($date, $format = 'm/d/Y') {
		$date = date($format, strtotime($date));
		return $date == '11/30/-0001' ? '' : $date;
	});
	$config->twig->addFilter($filter);

	$filter = new TwigFilter('yesorno', function ($trueorfalse) {
		return ($trueorfalse === true || strtoupper($trueorfalse) == 'Y') ? 'yes' : 'no';
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('attrJS', function ($string) {
		return "js-$string";
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('array_key_exists', function ($array, $key) {
		return array_key_exists($key, $array);
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('phone_us', function ($phone) {
		$numbers_only = preg_replace("/[^\d]/", "", $phone);
		return preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $numbers_only);
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('base64_encode', function ($str) {
		return base64_encode($str);
	});
	$config->twig->addFilter($filter);
