<?php

$config = array(
	'sourcesDir'			=> __DIR__ . '/development',
	'releaseFile'			=> __DIR__ . '/release/index.php',
	'excludePatterns'		=> array(
		// Common excludes for every application:
		"^/\.htaccess",			// Apache .rewrite rules
		"^/web.config",			// Microsoft IIS .rewrite rules
		"^/Var/Logs/(.*)$",		// App development logs
		".bak$",				// Anything to backup
		"_references.js",		// Visual Studio JS intellisense 
		// Source static files and libraries to generate minified results (optional):
		"^/Libs/Minify",		// Exclude libraries to minify HTML and CSS
		"^/Libs/JSMin.php",		// Exclude library to minify JS
		// Remove Debug library only after application is successfly packed (optional):
		"^/Libs/Nette"			// https://tracy.nette.org/
	),
	'stringReplacements'	=> array(
		// Before packing - run MvcCore app in single file links mode to generate proper assets in tmp directory
		// after all tmp assets with single file mode are generated - change this boolean back
		'MvcCore::Run(1);'		=> 'MvcCore::Run();',
		// Change SQLite database file location:
		"/../../../database/cdcol-sqllite.db"	=> "/../database/cdcol-sqllite.db",
		// Remove Debug library only after application is successfly packed (optional):
		"Nette_DebugAdapter::Init(MvcCore::GetEnvironment() == 'development');"	=> '',
	),
	'minifyTemplates'		=> 1,// Remove non-conditional comments and whitespaces
	'minifyPhp'				=> 1,// Remove comments and whitespaces
);