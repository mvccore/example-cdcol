<?php

class App_Views_Helpers_Css extends App_Views_Helpers_Assets
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
	 * Actualy called $_linksGroupContainer index throw pimore helper function css()
	 * @var $actualGroupName string
	 */
	protected $actualGroupName = '';
	
	/**
	 * See parent class
	 *
	 * @var array
	 */
	protected static $optionalAttributes = array('charset', 'href', 'hreflang', 'id', 'media', 'rel', 'rev', 'type', 'title', 'extras');
	
	/**
	 * Array with all appended links in it's indexed groups throw pimore helper function css()
	 * @var $scriptsGroupContainer array 
	 */
	protected static $linksGroupContainer = array();
	
	/**
	 * headLink() - View Helper Method
	 *
	 * Returns current object instance. Optionally, allows passing array of
	 * values to build link.
	 *
	 * @return $this
	 */
	public function Css ($groupName = self::GROUP_NAME_DEFAULT, array $attributes = null, $placement = 'Append')
	{
		$this->actualGroupName = $groupName;
		
		if (!isset(self::$linksGroupContainer[$groupName])) {
			self::$linksGroupContainer[$groupName] = array();
		}
		
		if ($attributes !== NULL) {
			$item = $this->createData($attributes);
			switch ($placement) {
				case 'Set':
					$this->Set($item);
					break;
				case 'Prepend':
					$this->Prepend($item);
					break;
				case 'Append':
				default:
					$this->Append($item);
					break;
			}
		}
		return $this;
	}
	
	/**
	 * Overload method access
	 *
	 * Creates the following virtual methods:
	 * 
	 * - Append($href, $media, $conditionalStylesheet, $extras)
	 * - OffsetSet($index, $href, $media, $conditionalStylesheet, $extras)
	 * - Prepend($href, $media, $conditionalStylesheet, $extras)
	 * - Set($href, $media, $conditionalStylesheet, $extras)
	 * 
	 * - AppendRendered($href, $media, $conditionalStylesheet, $extras)
	 * - OffsetSetRendered($index, $href, $media, $conditionalStylesheet, $extras)
	 * - PrependRendered($href, $media, $conditionalStylesheet, $extras)
	 * - SetRendered($href, $media, $conditionalStylesheet, $extras)
	 * 
	 * - AppendAlt($href, $type, $title, $extras)
	 * - OffsetSetAlt($index, $href, $type, $title, $extras)
	 * - PrependAlt($href, $type, $title, $extras)
	 * - SetAlt($href, $type, $title, $extras)
	 *
	 * @param mixed $method
	 * @param mixed $args
	 * @return $this
	 * @throws Exception if too few arguments or invalid method
	 */
	public function __call($method, $args)
	{
		if (preg_match('/^(?P<action>Set|(Ap|Pre)pend|OffsetSet)(?P<type>|Alt)(?P<render>|Rendered)$/', $method, $matches)) {
			$argc   = count($args);
			$action = $matches['action'];
			$type   = strtolower($matches['type']);
			$render = strtolower($matches['render']) == 'rendered' ? TRUE : FALSE;
			$index  = null;

			if ($action == 'OffsetSet') {
				if ($argc > 0) {
					$index = array_shift($args);
					--$argc;
				}
			}

			if ($argc === 0) throw new Exception(sprintf('%s requires at least one argument', $method));

			if (is_array($args[0])) {
				$item = $this->createData($args[0]);
			} else {
				$dataMethod = ($type == 'alt') ? 'createDataAlternate' : 'createDataStylesheet';
				$item = $this->$dataMethod($args, $render);
			}

			if ($item) {
				if ('OffsetSet' == $action) {
					$this->OffsetSetItem($index, $item);
				} else {
					$this->{$action . 'Item'}($item);
				}
			}

			return $this;
			
		} else {
			throw new Exception('"' . get_class($this) . '" has no method "' . $method . '".');
		}
	}

	/**
	 * Override append
	 *
	 * @param  string $value
	 * @return void
	 */
	public function AppendItem ($value)
	{
		if (!$this->_isValid($value)) throw new Exception('Append() expects a data token; please use one of the custom Append*() methods');
		self::$linksGroupContainer[$this->actualGroupName][] = $value;
	}
	
	/**
	 * Override prepend
	 *
	 * @param  string $value
	 * @return void
	 */
	public function PrependItem ($value)
	{
		if (!$this->_isValid($value)) throw new Exception('Prepend() expects a data token; please use one of the custom Prepend*() methods');
		array_unshift(self::$linksGroupContainer[$this->actualGroupName], $value);
	}

	/**
	 * Override set
	 *
	 * @param  string $value
	 * @return void
	 */
	public function SetItem ($value)
	{
		if (!$this->_isValid($value)) throw new Exception('Set() expects a data token; please use one of the custom Set*() methods');
		self::$linksGroupContainer[$this->actualGroupName] = array($value);
	}

	/**
	 * Override offsetSet
	 *
	 * @param  string|int $index
	 * @param  mixed $value
	 * @return void
	 */
	public function OffsetSetItem ($index, $value)
	{
		if (!$this->_isValid($value)) throw new ExceptionException('OffsetSet() expects a data token; please use one of the custom OffsetSet*() methods');

		if (isset(self::$linksGroupContainer[$this->actualGroupName][$index])) {
			if ($index > 0) {
				$firstArrPart = array_slice(self::$linksGroupContainer, 0, $index);
			} else {
				$firstArrPart = array();
			}
			if ($index + 1 < count(self::$linksGroupContainer)) {
				$secondArrPart = array_slice(self::$linksGroupContainer, $index);
			} else {
				$secondArrPart = array();
			}
			self::$linksGroupContainer = array_merge($firstArrPart, array($value), $secondArrPart);
		} else {
			self::$linksGroupContainer[$this->actualGroupName][$index] = $value;
		}
	}


	/**
	 * Create HTML link element from data item
	 *
	 * @param  stdClass $item
	 * @return string
	 */
	protected function renderItem (stdClass $item)
	{
		$attributes = (array) $item;
		$link       = '<link ';

		foreach (self::$optionalAttributes as $itemKey) {
			if (isset($attributes[$itemKey]) && $itemKey !== 'extras') {
				if ($itemKey == 'href' && isset($attributes['render']) && $attributes['render']) {
					$fullPath = $this->getAppRoot() . $attributes['path'];
					if (file_exists($fullPath)) {
						if ($item->render) $attributes['href'] = $this->url($this->renderFile($attributes['path']));
					} else if (class_exists('Debug')) {
						Debug::log('[App_Views_Helpers_Css] File not found in CSS view rendering process. Path: "' . $fullPath . '".', Debug::ERROR);
					}
				}
				$link .= sprintf('%s="%s" ', $itemKey, ($this->autoEscape) ? $this->escape($attributes[$itemKey]) : $attributes[$itemKey]);
			}
		}
		
		$link .= '/>';
		if (($link == '<link />') || ($link == '<link >')) return '';

		if (isset($attributes['conditionalStylesheet'])
			&& !empty($attributes['conditionalStylesheet'])
			&& is_string($attributes['conditionalStylesheet']))
		{
			$link = '<!--[if ' . $attributes['conditionalStylesheet'] . ']> ' . $link . '<![endif]-->';
		}

		return $link;
	}
	
	/**
	 * Render css file by path as php file
	 * 
	 * @param string $srcPath
	 * @return string
	 */
	protected function renderFile ($srcPath = '')
	{
		$systemConfigHash = md5(json_encode(self::$globalOptions));
		
		$tmpPath = '/rendered_css_' . $systemConfigHash . '_' . trim(str_replace('/', '_', $srcPath), "_");
		$srcFileFullPath = $this->getAppRoot() . $srcPath;
		$tmpFileFullPath = $this->getTmpDir() . $tmpPath;
		
		if (file_exists($srcFileFullPath)) {
			$srcFileModDate = filemtime($srcFileFullPath);
		} else {
			$srcFileModDate = 1;
		}
		
		if (file_exists($tmpFileFullPath)) {
			$tmpFileModDate = filemtime($tmpFileFullPath);
		} else {
			$tmpFileModDate = 0;
		}
		
		if ($srcFileModDate !== FALSE && $tmpFileModDate !== FALSE) {
			if ($srcFileModDate > $tmpFileModDate) {				
				$fileContent = $this->renderFileToCache($srcFileFullPath);
				$fileContent = $this->_convertStylesheetPathsFromRelatives2TmpAbsolutes($fileContent, $srcPath);
				file_put_contents($tmpFileFullPath, $fileContent);
				@chmod($tmpFileFullPath, 0766);
				if (class_exists('Debug')) {
					Debug::log("[App_Views_Helpers_Css] Css file rendered: $tmpFileFullPath", Debug::ERROR);
				}
			}
		}
		
		$resultPath = substr($tmpFileFullPath, strlen($this->getAppRoot()));
		
		return $resultPath;
	}
	
	/**
	 * Render css file by absolute path as php file in module tmp directory with pimcore helpers
	 * 
	 * @param string $absolutePath
	 * @return string
	 */
	protected function renderFileToCache ($absolutePath)
	{
		ob_start();
		try {
			include($absolutePath);
		} catch (Exception $e) {
			if (class_exists('Debug')) {
				Debug::_exceptionHandler($e);
			} else {
				throw $e;
			}
		}
		$result = ob_get_clean();
		return $result;
	}
	
	/**
	 * Render link elements as string
	 *
	 * @param string|int $indent
	 * @return string
	 */
	public function Render ($indent = 0)
	{
		if (count(self::$linksGroupContainer[$this->actualGroupName]) === 0) return '';

		$minify = self::$globalOptions['cssMinify'];
		$joinTogether = self::$globalOptions['cssJoin'];
		if ($joinTogether) {
			$result = $this->renderItemsTogether($this->actualGroupName, self::$linksGroupContainer[$this->actualGroupName], $indent, $minify);
		} else {
			$result = $this->renderItemsSeparately($this->actualGroupName, self::$linksGroupContainer[$this->actualGroupName], $indent, $minify);
		}
		
		return $result;
	}
	
	protected function renderItemsTogether ($actualGroupName = '', $items = array(), $indent, $minimized = '')
	{
		// some configurations is not possible to render together and minimized
		list($itemsToRenderMinimized, $itemsToRenderSeparately) = $this->filterItemsForMinimizedRenderingForPossibleMinimizedItems($items);
		
		$indentStr = $this->getIndentString($indent);
		$resultItems = array('<!-- css group begin: ' . $actualGroupName . ' -->');
		
		// process array with groups to minimize
		foreach ($itemsToRenderMinimized as $attrHashKey => $itemsToRender) {
			$resultItems[] = $this->renderItems($itemsToRender, $indent, $minimized);
		}
		
		// process array with groups, which are not possible to minimize
		foreach ($itemsToRenderSeparately as $attrHashKey => $itemsToRender) {
			foreach ($itemsToRender as $item) {
				$resultItems[] = $this->renderItem((object) $item);
			}
		}
		
		$resultItems[] = '<!-- css group end: ' . $actualGroupName . ' -->';
		
		return $indentStr . implode(PHP_EOL . $indentStr, $resultItems);
	}
	
	protected function getTmpFileFullPathByPartFilesInfo ($filesGroupInfo = array())
	{
		return implode('', array(
			$this->getTmpDir(),
			"/minified_css_",
			md5(
				implode(',', array_values($filesGroupInfo)) . '_' . implode(',', array_keys($filesGroupInfo)) . '_' . self::$globalOptions['cssMinify']
				
			),
			".css"
		));
	}

	protected function renderItems ($itemsToRender = array(), $tmpFileFullPath = '', $minimized = '')
	{
		$resultItem = '';
		
		// complete tmp filename by source filenames and sourfe files modification times
		$filesGroupInfo = array();
		foreach ($itemsToRender as $item) {
			$fullPath = $this->getAppRoot() . $item->href;
			if (file_exists($fullPath)) {
				$filesGroupInfo[filemtime($fullPath)] = $item->href;
			}
		}
		
		$tmpFileFullPath = $this->getTmpFileFullPathByPartFilesInfo($filesGroupInfo);
		
		// check, if the rendered, together completed and minimized file is in tmp cache allready
		if (!file_exists($tmpFileFullPath)) {
			// render items if necessary
			foreach ($itemsToRender as $hashKey => $item) {
				$fullPath = $this->getAppRoot() . $item->path;
				if (file_exists($fullPath)) {
					if ($item->render) $itemsToRender[$hashKey]->path = $this->renderFile($item->path);
				} else if (class_exists('Debug')) {
					Debug::log('[App_Views_Helpers_Css] File not found in CSS view rendering process. Path: "' . $fullPath . '".', Debug::ERROR);
				}
			}
			// load all items and join them together
			$contentStr = '';
			foreach ($itemsToRender as $hashKey => $item) {
				$contentStrings = array();
				$fullPath = $this->getAppRoot() . $item->path;
				if (file_exists($fullPath)) {
					$contentStrings = array(
						PHP_EOL . "/* " . $item->path . " */" . PHP_EOL,
						file_get_contents($fullPath),
					);
					if ($item->type == 'text/css') $contentStrings[1] = $this->_convertStylesheetPathsFromRelatives2TmpAbsolutes($contentStrings[1], $item->path);
				} else {
					$contentStrings = array(
						PHP_EOL . "/* Not found: " . $item->path . " */" . PHP_EOL,
						"",
					);
					if (class_exists('Debug')) {
						Debug::log('[App_Views_Helpers_Css] File not found in CSS view rendering completition process. Path: "' . $fullPath . '".', Debug::ERROR);
					}
				}
				if ($minimized) {
					if (isset($item->extras) && $item->extras['donotminimize'] && $item->extras['donotminimize'] === TRUE) {
					} else {
						$contentStrings[1] = Minify_CSS::minify($contentStrings[1]);
					}
				}
				$contentStrings[1] .= PHP_EOL;
				$contentStr .= $contentStrings[0] . $contentStrings[1];
			}
			
			// save completed tmp file
			$tmpFileFullPathToWrite = (strpos($tmpFileFullPath, '?') !== FALSE) ? strpos($tmpFileFullPath, 0, strpos($tmpFileFullPath, '?')) : $tmpFileFullPath ;
			file_put_contents($tmpFileFullPathToWrite, $contentStr);
			@chmod($tmpFileFullPath, 0766);
			
			//$tmpFileFullPath = substr($tmpFileFullPath, strlen($this->getAppRoot()));
		}
		
		// complete <link> tag with tmp file path in $tmpFileFullPath variable
		$firstItem = array_merge((array) $itemsToRender[0], array());
		if (isset($firstItem['render'])) unset($firstItem['render']);
		$path = substr($tmpFileFullPath, strlen($this->getAppRoot()));
		$firstItem['href'] = $this->url($path);
		$resultItem = $this->renderItem((object) $firstItem);
		
		return $resultItem;
	}

	protected function filterItemsForMinimizedRenderingForPossibleMinimizedItems ($items)
	{
		$itemsToRenderMinimized = array();
		$itemsToRenderSeparately = array(); // some configurations is not possible to render together and minimized
		
		// go for every item to complete existing combinations in attributes
		foreach ($items as $item) {
			$itemArr = array_merge((array) $item, array());
				
			$href = $itemArr['href'];
			unset($itemArr['href'], $itemArr['path']);

			$renderArrayKey = md5(json_encode($itemArr));

			$conditionalStylesheet = $itemArr['conditionalStylesheet'] !== FALSE && $itemArr['conditionalStylesheet'] !== null;
			if ($itemArr['rel'] == 'stylesheet' && $itemArr['type'] == 'text/css' && !$conditionalStylesheet) {
				if (isset($itemsToRenderMinimized[$renderArrayKey])) {
					$itemsToRenderMinimized[$renderArrayKey][] = $item;
				} else {
					$itemsToRenderMinimized[$renderArrayKey] = array($item);
				}
			} else {
				if (isset($itemsToRenderSeparately[$renderArrayKey])) {
					$itemsToRenderSeparately[$renderArrayKey][] = $item;
				} else {
					$itemsToRenderSeparately[$renderArrayKey] = array($item);
				}
			}
		}
		
		return array(
			$itemsToRenderMinimized,
			$itemsToRenderSeparately,
		);
	}
	
	protected function renderItemsSeparately ($actualGroupName = '', $items = array(), $indent)
	{
		$indentStr = $this->getIndentString($indent);
		
		$resultItems = array('<!-- css group begin: ' . $actualGroupName . ' -->');
		foreach ($items as $item) {
			$separator = (strpos($item->href, '?') === FALSE) ? '?' : '&';
			$fileMTimePath = $this->getAppRoot() . $item->path;
			$fileMTime = intval(filemtime($fileMTimePath));
			$item->href .= $separator . '_fmt=' . date(
				self::FILE_MODIFICATION_DATE_FORMAT,
				$fileMTime
			);
			$resultItems[] = $this->renderItem($item);
		}
		$resultItems[] = '<!-- css group end: ' . $actualGroupName . ' -->';	
		
		return $indentStr . implode(PHP_EOL . $indentStr, $resultItems);
	}

	/**
	 * Create data item for stack
	 *
	 * @param  array $attributes
	 * @return stdClass
	 */
	protected function createData(array $attributes)
	{
		$data = (object) $attributes;
		return $data;
	}

	/**
	 * Create item for stylesheet link item
	 *
	 * @param  array $args
	 * @return stdClass|false Returns fals if stylesheet is a duplicate
	 */
	protected function createDataStylesheet(array $args, $render = FALSE)
	{
		$rel                   = 'stylesheet';
		$type                  = 'text/css';
		$media                 = 'screen';
		$conditionalStylesheet = false;
		$path                  = array_shift($args);
		$href                  = $this->url($path);
		
		if ($this->_isDuplicateStylesheet($path)) {
			return false;
		}

		if (0 < count($args)) {
			$media = array_shift($args);
			if(is_array($media)) {
				$media = implode(',', $media);
			} else {
				$media = (string) $media;
			}
		}
		if (0 < count($args)) {
			$conditionalStylesheet = array_shift($args);
			if(!empty($conditionalStylesheet) && is_string($conditionalStylesheet)) {
				$conditionalStylesheet = (string) $conditionalStylesheet;
			} else {
				$conditionalStylesheet = null;
			}
		}
		if(0 < count($args) && is_array($args[0])) {
			$extras = array_shift($args);
			$extras = (array) $extras;
		}

		$attributes = compact('rel', 'type', 'path', 'href', 'media', 'conditionalStylesheet', 'extras', 'render');
		
		return $this->createData($attributes);
	}

	/**
	 * Create item for alternate link item
	 *
	 * @param  array $args
	 * @return stdClass
	 */
	protected function createDataAlternate(array $args, $render = FALSE)
	{
		if (3 > count($args)) throw new ExceptionException(sprintf('Alternate tags require 3 arguments; %s provided', count($args)));

		$rel   = 'alternate';
		$path  = array_shift($args);
		$href  = $this->url($path);
		$type  = array_shift($args);
		$title = array_shift($args);

		if(0 < count($args) && is_array($args[0])) {
			$extras = array_shift($args);
			$extras = (array) $extras;

			if(isset($extras['media']) && is_array($extras['media'])) {
				$extras['media'] = implode(',', $extras['media']);
			}
		}

		$path  = (string) $path;
		$href  = (string) $href;
		$type  = (string) $type;
		$title = (string) $title;

		$attributes = compact('rel', 'path', 'href', 'type', 'title', 'extras', 'render');
		
		return $this->createData($attributes);
	}
	
	/**
	 * Converts all relative paths in all css rules to absolute paths with MvcCore url structures
	 *
	 * @param mixed $fullPathContent css file full path
	 * @param mixed $href css file href value
	 * @return string
	 *
	 */
	private function _convertStylesheetPathsFromRelatives2TmpAbsolutes ($fullPathContent, $href)
	{
		$startLength = strlen($fullPathContent);
	
		//$debug = $href == "/pimcore/static/js/lib/ext-plugins/ux/css/ColumnHeaderGroup.css";
		
		$lastHrefSlashPos = mb_strrpos($href, '/');
		if ($lastHrefSlashPos === FALSE) return $fullPathContent;
		$stylesheetDirectoryRelative = mb_substr($href, 0, $lastHrefSlashPos + 1);
		//if ($debug) yxc($href);
		//if ($debug) yxc($fullPathContent);
		
		// process content for all double dots
		$position = 0;
		while ($position < mb_strlen($fullPathContent)) {
			$doubleDotsPos = mb_strpos($fullPathContent, '../', $position);
			if ($doubleDotsPos === FALSE) break;
			
			
			// make sure that double dot string is in url('') or url("") block
			
			// try to find first occurance of url(" backwards
			$lastUrlBeginStrPos = mb_strrpos(mb_substr($fullPathContent, 0, $doubleDotsPos), 'url(');
			if ($lastUrlBeginStrPos === FALSE) {
				$position = $doubleDotsPos + 3;
				//if ($debug) yxc("next 1");
				continue;
			}
			
			// then check if between that are only [\./ ]
			$beginOfUrlBlockChars = mb_substr($fullPathContent, $lastUrlBeginStrPos + 4, $doubleDotsPos - ($lastUrlBeginStrPos + 4));
			$beginOfUrlBlockChars = preg_replace("#[\./ \"'_\-]#", "", $beginOfUrlBlockChars);
			if (mb_strlen($beginOfUrlBlockChars) > 0) {
				$position = $lastUrlBeginStrPos + 4;
				//if ($debug) yxc("next 2");
				continue;
			}
			
			// try to find first occurance of ")
			$firstUrlEndStrPos = mb_strpos($fullPathContent, ')', $doubleDotsPos);
			if ($firstUrlEndStrPos === FALSE) {
				$position = $doubleDotsPos + 3;
				//if ($debug) yxc("next 3");
				continue;
			}
			
			// then check of between that are only [a-zA-Z\./ ]
			$endOfUrlBlockChars = mb_substr($fullPathContent, $doubleDotsPos + 3, $firstUrlEndStrPos - ($doubleDotsPos + 3));
			$endOfUrlBlockChars = preg_replace("#[a-zA-Z\./ \"'_\-\?\&]#", "", $endOfUrlBlockChars);
			if (mb_strlen($endOfUrlBlockChars) > 0) {
				$position = $firstUrlEndStrPos + 1;
				//if ($debug) yxc("next 4");
				continue;
			}
			
			// if it is not the url block, shift the position and continue
			
			// replace relative path to absolute path
			$lastUrlBeginStrPos += 4;
			$urlSubStr = mb_substr($fullPathContent, $lastUrlBeginStrPos, $firstUrlEndStrPos - $lastUrlBeginStrPos);
			
			// get double or single quotes or no quotes
			$firstStr = mb_substr($urlSubStr, 0, 1);
			$lastStr = mb_substr($urlSubStr, mb_strlen($urlSubStr) - 1, 1);
			if ($firstStr === '"' && $lastStr === '"') {
				$urlSubStr = mb_substr($urlSubStr, 1, mb_strlen($urlSubStr) - 2);
				$quote = '"';
			} else if ($firstStr === "'" && $lastStr === "'") {
				$urlSubStr = mb_substr($urlSubStr, 1, mb_strlen($urlSubStr) - 2);
				$quote = "'";
			} else {
				$quote = '"';
			}
			
			// translate relative to web absolute path
			$trimmedUrlSubStr = ltrim($urlSubStr, './');
			$trimmedPartLength = mb_strlen($urlSubStr) - mb_strlen($trimmedUrlSubStr);
			$trimmedPart = trim(mb_substr($urlSubStr, 0, $trimmedPartLength), '/');
			$subjectRestPath = trim(mb_substr($urlSubStr, $trimmedPartLength), '/');
			
			$urlFullBasePath = str_replace('\\', '/', realpath($this->getAppRoot() . $stylesheetDirectoryRelative . $trimmedPart));
			$urlFullPath = $urlFullBasePath . '/' . $subjectRestPath;
			//xcv(array($this->getAppRoot(), $stylesheetDirectoryRelative, $trimmedPart, $urlFullPath));
			
			// complete stylesheet new path
			$webPath = mb_substr($urlFullPath, mb_strlen($this->getAppRoot()));
			$webPath = $this->url($webPath);
			//if ($debug) yxc($webPath);
			// replace the url part
			$fullPathContent = mb_substr($fullPathContent, 0, $lastUrlBeginStrPos)
				. $quote . $webPath . $quote
				. mb_substr($fullPathContent, $firstUrlEndStrPos);
				
			// shift the position property
			$position = $lastUrlBeginStrPos + mb_strlen($webPath) + 3;
			
			/*if ($debug) {
				yxc(array(
					$fullPathContent,
					$webPath,
					$position
				));
				break;
			}*/
		}
		
		$endLength = mb_strlen($fullPathContent);
		/*if ($endLength !== $startLength) {
			die($fullPathContent);
		}*/
		
		return $fullPathContent;
	}
	
	/**
	 * Is the linked stylesheet a duplicate?
	 *
	 * @param  string $uri
	 * @return bool
	 */
	private function _isDuplicateStylesheet($uri)
	{
		foreach (self::$linksGroupContainer as $linksGroupContainerItem) {
			foreach ($linksGroupContainerItem as $item) {
				if (($item->rel == 'stylesheet') && ($item->href == $uri)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Check if value is valid
	 *
	 * @param  mixed $value
	 * @return boolean
	 */
	private function _isValid($value)
	{
		if (!$value instanceof stdClass) {
			return false;
		}

		$vars         = get_object_vars($value);
		$keys         = array_keys($vars);
		$intersection = array_intersect(self::$optionalAttributes, $keys);
		if (empty($intersection)) {
			return false;
		}

		return true;
	}

}
