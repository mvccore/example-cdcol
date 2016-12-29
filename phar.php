<?php

include_once("/../../libraries/packager/src/Packager/Phar.php");

$config = array(
	'sourcesDir'	=> __DIR__ . '/development',
	'releaseFile'	=> __DIR__ . '/release/index.php',
	'excludePatterns'		=> array(
		"^/\.hg",
		"^/Libs/Nette",
		"^/Var/Logs/(.*)$",
		"^/\.htaccess",
		"^/\.hgignore",
		"^/web.config",
		"_references.js",
		".bak$",
	),
	'patternReplacements'	=> array(
		//"#([^a-zA-Z0-9_])\@include_once\(([^;]*);#" => '$1',
	),
	'stringReplacements'	=> array(
		'@Nette_DebugAdapter::Init(TRUE);' => '',
	),
	'compressPhp'			=> 1,
);

Packager_Phar::run($config);