<?php

class App_Views_Helpers_Assets
{
	/**
	 * Default link group name
	 * @const string
	 */
	const GROUP_NAME_DEFAULT   = 'default';
	
	/**
	 * Date format for ?_fmd param timestamp in admin development mode
	 * @const string
	 */
	const FILE_MODIFICATION_DATE_FORMAT = 'Y-m-d_H-i-s';
	
	/**
	 * Simple app view object
	 * @var MvcCore_View
	 */
	protected $view;
	
	/**
	 * Actualy called $_linksGroupContainer index throw pimore helper function css()
	 * @var $_actualGroupName string
	 */
	protected $actualGroupName = '';
	
	/**
	 * Global options about joining and minifying which can bee overwrited by single settings throw calling for eeample: append() method as another param
	 *
	 * @var array
	 */
	protected static $globalOptions = array(
		'jsJoin'		=> 0,
		'jsMinify'		=> 0,
		'cssJoin'		=> 0,
		'cssMinify'		=> 0,
		'tmpDir'		=> '/tmp',
	);
	
	/**
	 * Valid attributes
	 *
	 * @var array
	 */
	protected static $optionalAttributes = array();
	
	/**
	 * Application root dierctory from request object
	 * @var string
	 */
	protected static $appRoot = '';
	
	/**
	 * Relative path to store joined and minified files from application root dierctory
	 * @var string
	 */
	protected static $tmpDir = '';
	
	/**
	 * Base noncompiled uris path from localhost if necessary
	 * @var string
	 */
	protected static $basePath = null;

	/**
	 * Flag wheter to automatically escape output, must also be
	 * enforced in the child class if __toString/toString is overriden
	 * @var book
	 */
	protected $autoEscape = true;

	/**
	 * Insert a MvcCore_View in each helper constructing
	 */
	public function __construct ($view)
	{
		$this->view = $view;
	}
	
	/**
	 * Set global static options about minifying and joining together which can bee overwrited by single settings throw calling for eeample: append() method as another param
	 *
	 * @param  bool $autoEscape whether or not to auto escape output
	 * @return $this
	 */
	public static function SetGlobalOptions($options = array())
	{
		foreach ($options as $key => $value) {
			self::$globalOptions[$key] = $value;
		}
	}

	/**
	 * Set whether or not auto escaping should be used
	 *
	 * @param  bool $autoEscape whether or not to auto escape output
	 * @return $this
	 */
	public function SetAutoEscape($autoEscape = true)
	{
		$this->autoEscape = ($autoEscape) ? true : false;
		return $this;
	}

	/**
	 * Escape a string
	 *
	 * @param  string $string
	 * @return string
	 */
	protected function escape($string)
	{
		return htmlspecialchars((string) $string, ENT_COMPAT, 'UTF-8');
	}
	
	/**
	 * Creates a MvcCore url - allways from one place
	 *
	 * @param  string $path
	 * 
	 * @return string
	 */
	protected function url($path = '')
	{
		$result = '';
		if (MvcCore::GetCompiled()) {
			$extPos = strrpos($path, '.');
			if ($extPos !== FALSE) {
				$ext = substr($path, $extPos + 1);
				if ($ext == 'js') {
					$result = $this->view->Url('Assets::Js', array('path'=>$path));
				} else if ($ext == 'css') {
					$result = $this->view->Url('Assets::Css', array('path'=>$path));
				} else if (in_array($ext, array('png', 'gif', 'jpg', 'jpeg', 'ico', 'svg'))) {
					$result = $this->view->Url('Assets::Img', array('path'=>$path));
				} else if (in_array($ext, array('eot', 'woff', 'woff2', 'ttf', 'otf', 'svgz'))) {
					$result = $this->view->Url('Assets::Img', array('path'=>$path));
				}
			}
		} else {
			if (is_null(self::$basePath)) {
				self::$basePath = $this->view->GetController()->GetRequest()->basePath;
			}
			$result = self::$basePath . $path;
		}
		return $result;
	}
	
	/**
	 * get indent string
	 *
	 * @param string|int $indent
	 * @return string
	 */
	protected function getIndentString($indent = 0)
	{
		$indentStr = '';
		if (is_numeric($indent)) {
			$indInt = intval($indent);
			if ($indInt > 0) {
				$i = 0;
				while ($i < $indInt) {
					$indentStr .= "\t";
					$i += 1;
				}
			}
		} else if (is_string($indent)) {
			$indentStr = $indent;
		}
		return $indentStr;
	}

	/**
	 * Return and store application document root from controller view request object
	 * @return string
	 */
	protected function getAppRoot()
	{
		if (!self::$appRoot) self::$appRoot = $this->view->GetController()->GetRequest()->appRoot;
		return self::$appRoot;
	}

	/**
	 * Return and store application document root from controller view request object
	 * @return string
	 */
	protected function getTmpDir()
	{
		if (!self::$tmpDir) {
			$tmpDir = $this->getAppRoot() . self::$globalOptions['tmpDir'];
			if (!is_dir($tmpDir)) if (class_exists('Debug') && !Debug::$productionMode) mkdir($tmpDir, 0777, TRUE);
			if (!is_writable($tmpDir)) {
				try {
					@chmod($tmpDir, 0777);
				} catch (Exception $e) {
					throw new Exception('[App_Views_Helpers_Assets] ' . $e->getMessage());
				}
			}
			self::$tmpDir = $tmpDir;
		}
		return self::$tmpDir;
	}
}
