<?php

include_once("/../../libraries/packager/src/Packager/Php.php");

$config = array(
	'sourcesDir'	=> __DIR__ . '/development',
	'releaseFile'	=> __DIR__ . '/release/index.php',
	'includeFirst'	=> array(
		/*
		'/Libs/Nette/Debug/IDebugPanel.php',
		'/Libs/Nette/Debug/DebugPanel.php',
		'/Libs/Nette/Debug/Debug.php',
		*/
		'/Libs/',
		'/App/MvcCore.php',
		'/App/Controllers/Base.php',
		'/App/',
	),
	'excludePatterns'		=> array(
		"^/\.hg",
		"^/Libs/startup.php$",
		"^/Libs/Nette",
		"^/Var/Logs/(.*)$",
		"^/\.htaccess",
		"^/\.hgignore",
		"^/web.config",
		"_references.js",
		".bak$",
	),
	'patternReplacements'	=> array(
		"#([^a-zA-Z0-9_])\@include_once\(([^;]*);#" => '$1',
	),
	'stringReplacements'	=> array(
		'@Nette_DebugAdapter::Init(TRUE);' => '',
	),
	'compressPhp'			=> 1,
);

Packager_Php::run($config);