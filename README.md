# MvcCore - Example - CD Collection
MvcCore classic CD collection CRUD example with default SQLite database with cd albums.
MySQL and MSSQL database backups included. Result application is packed in strict package mode,
yo it's completly portable, ou can find it as index.php in release directory.

## Features
- all packing ways are possible to use:
	- PHAR
	- PHP
		- strict package (currently used for packed app in result dir)
		- strict hdd
		- preserve package
		- preserve hdd

## Instalation
```shell
# load project
composer create-project mvccore/example-cdcol

# go to project development directory
cd example-cdcol/development

# update dependencies for application development sources
composer update
```

## Build
# Linux build:
```shell
# go to project root dir
cd example-cdcol
# run build process into single PHP file
sh make.sh
```

# Windows build:
```shell
# go to project root dir
cd example-cdcol
# run build process into single PHP file
make.cmd
```

# Browser build:
```shell
# visit script `make-php.php` in your project root directory:
http://localhost/example-cdcol/make-php.php
# now run your result in:
http://localhost/example-cdcol/release/
```