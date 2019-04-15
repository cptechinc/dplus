<?php
    $convertdate = new Twig_Function('convertdate', function ($date, $format = 'm/d/Y') {
		  return date($format, strtotime($date));
    });
    $config->twig->addFunction($convertdate);
