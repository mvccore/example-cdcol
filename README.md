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
# load example
composer create-project mvccore/example-cdcol

# go to created directory
cd example-cdcol

# update dependencies for packing
composer update

# go to example development directory
cd development

# update dependencies for application sources
composer update
```

## Build
```shell
sh make.sh
# or Windows:
make.cmd
```
