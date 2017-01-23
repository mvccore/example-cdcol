# MvcCore - Example - CD Collection
- [**MvcCore**](https://github.com/mvccore/mvccore) classic CD collection CRUD example with default SQLite database with cd albums
- Current SQLite database, MySQL and MSSQL database backups included in `./.database/` dir
- Result is completly portable - `./release/index.php` + `./release/.htaccess`
- Result application currently packed in strict package mode, all packing configurations included in `./.packager/`
- All packing ways are possible to use:
  - PHAR
    - standard PHAR package with whole devel dir content
  - PHP (packed with [**Packager library - mvccore/packager**](https://github.com/mvccore/packager))
    - strict package
      - everything is contained in result `index.php`
      - only `.htaccess` or `web.config` are necessary to use mod_rewrite
    - preserve package
      - result `index.php` file contains PHP files, 
        PHTML templates but no CSS/JS/fonts or images
      - all wrapped file system functions are looking inside 
        package first, then they try to read data from HDD
    - preserve hdd
      - result `index.php` file contains PHP files, 
        PHTML templates but no CSS/JS/fonts or images
      - all wrapped file system functions are looking on HDD first, 
        then they try to read data from package inself
    - strict hdd
      - result `index.php` file contains only PHP files, 
        but PHTML templates, all CSS/JS/fonts and images are on HDD
      - no PHP file system function is wrapped

## Instalation
```shell
# load example
composer create-project mvccore/example-cdcol

# go to project development dir
cd example-cdcol/development

# update dependencies for app development sources
composer update
```

## Build

### 1. Prepare application
- go to `example-cdcol/development`
- clear everything in `./Var/Tmp/`
- change `$app->Run();` to `$app->Run();` in `./index.php`
- visit all aplication routes where are different JS/CSS bundles 
  groups to generate `./Var/Tmp/` content for result app
- run build process

### 2. Build

#### Linux:
```shell
# go to project root dir
cd example-cdcol
# run build process into single PHP file
sh make.sh
```

#### Windows:
```shell
# go to project root dir
cd example-cdcol
# run build process into single PHP file
make.cmd
```

#### Browser:
```shell
# visit script `make-php.php` in your project root directory:
http://localhost/example-cdcol/make-php.php
# now run your result in:
http://localhost/example-cdcol/release/
```
