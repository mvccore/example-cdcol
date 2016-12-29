<?php

class App_Views_Helpers_Js extends App_Views_Helpers_Assets
{	
	/**
	 * Whatever Expires header is send over http protocol, 
	 * minimal cache time for external files will be one day from last download
	 * @const string
	 */
	const EXTERNAL_MIN_CACHE_TIME   = 86400;

	/**
	 * See parent class
	 * @var array
	 */
	protected static $optionalAttributes = array('charset', 'defer', 'language', 'src',);
	
	/**
	 * Array with all appended scripts in it's indexed groups throw pimore helper function js()
	 * @var $_scriptsGroupContainer array 
	 */
	protected static $scriptsGroupContainer = array();

	/**
	 * Return headScript object
	 *
	 * Returns headScript helper object; optionally, allows specifying a script
	 * or script file to include.
	 *
	 * @param  string $groupName string identifier
	 * @param  string $mode ''/external
	 * @param  string $spec Script/url
	 * @param  string $placement Append, Prepend, or Set
	 * @param  array $attrs Array of script attributes
	 * @param  string $type Script type and/or array of script attributes
	 * @return $this
	 */
	public function Js (
		$groupName = self::GROUP_NAME_DEFAULT, 
		$src = '',
		$mode = 'Local',
		$action = 'Append', 
		$attrs = array(), 
		$type = 'text/javascript'
	) {
		$this->actualGroupName = $groupName;
		if (!isset(self::$scriptsGroupContainer[$groupName])) {
			self::$scriptsGroupContainer[$groupName] = array();
		}
		
		if ($src) {
			
			$mode = ($mode == 'External') ? 'external' : 'local' ;
			$action = strtolower($action);
			
			switch ($mode) {
				case 'external':
					$this->getActualExternalContentIfNecessary($src);
					$src = $this->getLocalContentSrcPath($src);
					$item = $this->createData($src, $type, $attrs);
					if ('OffsetSet' == $action) {
						$this->offsetSetItem($index, $item);
					} else {
						$this->{$action . 'Item'}($item);
					}
					break;
				case 'local':
				default:
					if (!$this->_isDuplicateScript($src)) {
						$item = $this->createData($src, $type, $attrs);
						if ('OffsetSet' == $action) {
							$this->offsetSetItem($index, $item);
						} else {
							$this->{$action . 'Item'}($item);
						}
					}
					break;
			}
		}

		return $this;
	}

	/**
	 * Overload method access
	 *
	 * Allows the following method calls:
	 * - Append($src, $type = 'text/javascript', $attrs = array())
	 * - OffsetSet($index, $src, $type = 'text/javascript', $attrs = array())
	 * - Prepend($src, $type = 'text/javascript', $attrs = array())
	 * - Set($src, $type = 'text/javascript', $attrs = array())
	 * 
	 * - AppendExternal($url, $type = 'text/javascript', $attrs = array())
	 * - OffsetSetExternal($url, $src, $type = 'text/javascript', $attrs = array())
	 * - PrependExternal($url, $type = 'text/javascript', $attrs = array())
	 * - SetExternal($url, $type = 'text/javascript', $attrs = array())
	 *
	 * @param  string $method
	 * @param  array $args
	 * @return $this
	 * @throws Exception if too few arguments or invalid method
	 */
	public function __call($method, $args)
	{
		if (preg_match('/^(?P<action>Set|(Ap|Pre)pend|OffsetSet)(?P<mode>|External)$/', $method, $matches)) {
			if (count($args) < 1) throw new Exception(sprintf('Method "%s" requires at least one argument', $method));

			$action  = $matches['action'];
			$mode    = $matches['mode'] == 'External' ? 'external' : 'local' ;
			$type    = 'text/javascript';
			$attrs   = array();

			if ('OffsetSet' == $action) {
				$index = array_shift($args);
				if (count($args) < 1) {
					throw new Exception(sprintf('Method "%s" requires at least two arguments, an index and src', $method));
				}
			}

			$src = $args[0];

			if (isset($args[1])) {
				$type = (string) $args[1];
			}
			if (isset($args[2])) {
				$attrs = (array) $args[2];
			}

			switch ($mode) {
				case 'external':
					$this->getActualExternalContentIfNecessary($src);
					$src = $this->getLocalContentSrcPath($src);
					$item = $this->createData($src, $type, $attrs);
					if ('OffsetSet' == $action) {
						$this->OffsetSetItem($index, $item);
					} else {
						$this->{$action . 'Item'}($item);
					}
					break;
				case 'local':
				default:
					if (!$this->_isDuplicateScript($src)) {
						$item = $this->createData($src, $type, $attrs);
						if ('OffsetSet' == $action) {
							$this->OffsetSetItem($index, $item);
						} else {
							$this->{$action . 'Item'}($item);
						}
					}
					break;
			}

			return $this;
		}
	}

	/**
	 * Create data item containing all necessary components of script
	 *
	 * @param  string $src
	 * @param  string $type
	 * @param  array $attributes
	 * @return stdClass
	 */
	protected function createData ($src = '',  $type = '', $attributes = array())
	{
		$data				= new stdClass();
		$attributes['src']	= $src;
		$data->path			= $src;
		$data->src			= $this->url($src);
		$data->type			= $type;
		$data->attributes	= $attributes;
		return $data;
	}

	/**
	 *
	 * @param string $src
	 * @return boolean 
	 */
	protected function getActualExternalContentIfNecessary ($src = '')
	{
		$updated = FALSE;
		
		$notTime = time();
		
		$cachePath = $this->getLocalContentSrcPath($src, TRUE);
		if (file_exists($cachePath)) {
			$cacheFileTime = filemtime($cachePath);
		} else {
			$cacheFileTime = 0;
		}
		
		if ($notTime > $cacheFileTime + self::EXTERNAL_MIN_CACHE_TIME) {
			
			while (TRUE) {
				$newSrc = $this->getPossiblyRedirectedSrc($src);
				if ($newSrc === $src) {
					break;
				} else {
					$src = $newSrc;
				}
			}
			
			$fr = fopen($src, 'r');
			$fw = fopen($cachePath, 'w');
			$bufferLength = 102400; // 100 KB
			$buffer = '';
			while ($buffer = fread($fr, $bufferLength)) {
				fwrite($fw, $buffer);
			}
			fclose($fr);
			fclose($fw);
			
			$updated = TRUE;
		}
		
		return $updated;
	}
	
	protected function getPossiblyRedirectedSrc ($src)
	{
		$fp = fopen($src, 'r');
		$metaData = stream_get_meta_data($fp);
		foreach ($metaData['wrapper_data'] as $response) {
			// Were we redirected? */
			if (strtolower(substr($response, 0, 10)) == 'location: ') {
				// update $src with where we were redirected to
				$src = substr($response, 10);
			}
		}
		return $src;
	}
	
	/**
	 *
	 * @param string $src
	 * @return string
	 */
	protected function getLocalContentSrcPath ($src = '', $full = FALSE)
	{
		$fullPath = $this->getTmpPath() . '/external_javascript_' . md5($src) . '.js';
		if ($full) {
			return $fullPath;
		} else {
			return str_replace($this->getAppRoot(), '', $fullPath);
		}
	}

	/**
	 * Append
	 * @param  string $value
	 * @return void
	 */
	public function AppendItem ($value)
	{
		if (!$this->_isValid($value)) throw new Exception('Invalid argument passed to Append(); please use one of the helper methods, Append() or AppendExternal()');
		self::$scriptsGroupContainer[$this->actualGroupName][] = $value;
	}
	
	/**
	 * Prepend
	 * @param  string $value
	 * @return void
	 */
	public function PrependItem ($value)
	{
		if (!$this->_isValid($value)) throw new Exception('Invalid argument passed to Prepend(); please use one of the helper methods, Prepend() or PrependExternal()');
		array_unshift(self::$scriptsGroupContainer[$this->actualGroupName], $value);
	}

	/**
	 * Set
	 * @param  string $value
	 * @return void
	 */
	public function SetItem ($value)
	{
		if (!$this->_isValid($value)) throw new Exception('Invalid argument passed to Set(); please use one of the helper methods, Set() or SetExternal()');
		self::$scriptsGroupContainer[$this->actualGroupName] = array($value);
	}

	/**
	 * OffsetSet
	 * @param  string|int $index
	 * @param  mixed $value
	 * @return void
	 */
	public function OffsetSetItem ($index, $value)
	{
		if (!$this->_isValid($value)) throw new Exception('Invalid argument passed to OffsetSet(); please use one of the helper methods, OffsetSet() or OffsetSetExternal()');
		
		if (isset(self::$scriptsGroupContainer[$this->actualGroupName][$index])) {
			if ($index > 0) {
				$firstArrPart = array_slice(self::$scriptsGroupContainer, 0, $index);
			} else {
				$firstArrPart = array();
			}
			if ($index + 1 < count(self::$scriptsGroupContainer)) {
				$secondArrPart = array_slice(self::$scriptsGroupContainer, $index);
			} else {
				$secondArrPart = array();
			}
			self::$scriptsGroupContainer = array_merge($firstArrPart, array($value), $secondArrPart);
		} else {
			self::$scriptsGroupContainer[$this->actualGroupName][$index] = $value;
		}
	}

	/**
	 * Create script HTML
	 *
	 * @param  string $type
	 * @param  array $attributes
	 * @param  string $content
	 * @param  string|int $indent
	 * @return string
	 */
	protected function renderItem ($item, $indent)
	{
		$attrString = '';
		if (!empty($item->attributes)) {
			foreach ($item->attributes as $key => $value) {
				if (!in_array($key, self::$optionalAttributes)) continue;
				if ('defer' == $key) {
					$value = 'defer';
				}
				$attrString .= sprintf(' %s="%s"', $key, ($this->autoEscape) ? $this->escape($value) : $value);
			}
		}

		$type = ($this->autoEscape) ? $this->escape($item->type) : $item->type;
		$html  = $indent . '<script type="' . $type . '"' . $attrString . '>';
		if (!empty($item->source)) {
			$useCdata = (bool) $this->useCdata;
			$escapeStart = ($useCdata) ? '//<![CDATA[' : '//<!--';
			$escapeEnd   = ($useCdata) ? '//]]>'       : '//-->';
			$html .= PHP_EOL . $indent . '    ' . $escapeStart . PHP_EOL . $item->source . $indent . '    ' . $escapeEnd . PHP_EOL . $indent;
		}
		$html .= '</script>' . PHP_EOL;

		if (
			isset($item->attributes['conditional'])
			&& !empty($item->attributes['conditional'])
			&& is_string($item->attributes['conditional']))
		{
			$html = $indent . '<!--[if ' . $item->attributes['conditional'] . ']> ' . $html . '<![endif]-->';
		}

		return $html;
	}
	
	/**
	 * Retrieve string representation
	 *
	 * @param  string|int $indent
	 * @return string
	 */
	public function Render ($indent = 0)
	{
		if (count(self::$scriptsGroupContainer[$this->actualGroupName]) === 0) return '';
		
		$minify = self::$globalOptions['jsMinify'];
		$joinTogether = self::$globalOptions['jsJoin'];
		if ($joinTogether) {
			$result = $this->renderItemsTogether($this->actualGroupName, self::$scriptsGroupContainer[$this->actualGroupName], $indent, $minify);
		} else {
			$result = $this->renderItemsSeparately($this->actualGroupName, self::$scriptsGroupContainer[$this->actualGroupName], $indent, $minify);
		}
		
		
		return $result;
	}
	
	protected function renderItemsSeparately ($actualGroupName = '', $items = array(), $indent)
	{
		$indentStr = $this->getIndentString($indent);

		$resultItems = array('<!-- js group begin: ' . $actualGroupName . ' -->' . PHP_EOL);
		foreach ($items as $item) {
			if (!$this->_isValid($item)) continue;
			if (class_exists('Debug') && !Debug::$productionMode) {
				$separator = (strpos($item->src, '?') === FALSE) ? '?' : '&' ;
				$fullPath = $this->getAppRoot() . $item->path;
				if (!file_exists($fullPath)) {
					Debug::log("[App_Views_Helpers_Js] File not found: '$fullPath'.", Debug::ERROR);
					continue;
				}
				$item->src .= $separator . '_fmt=' . date(
					self::FILE_MODIFICATION_DATE_FORMAT,
					intval(filemtime($this->getAppRoot() . $item->path))
				);
				$item->attributes['src'] = $item->src;
			}
			$resultItems[] = $this->renderItem($item, $indentStr);
		}
		$resultItems[] = $indentStr . '<!-- js group end: ' . $actualGroupName . ' -->' . PHP_EOL;

		return $indentStr . implode($this->getSeparator(), $resultItems);
	}
	
	protected function renderItemsTogether ($actualGroupName = '', $items = array(), $indent, $minimized = FALSE)
	{
		// some configurations is not possible to render together and minimized
		list($itemsToRenderMinimized, $itemsToRenderSeparately) = $this->filterItemsForMinimizedRenderingForPossibleMinimizedItems($items);
		
		$indentStr = $this->getIndentString($indent);
		$resultItems = array($indentStr . '<!-- js group begin: ' . $actualGroupName . ' -->');
		
		// process array with groups to minimize
		foreach ($itemsToRenderMinimized as $attrHashKey => $itemsToRender) {
			
			$filesGroupInfo = array();
			foreach ($itemsToRender as $item) {
				$fullPath = $this->getAppRoot() . $item->path;
				if (file_exists($fullPath)) {
					$filesGroupInfo[filemtime($fullPath)] = $item->path;
				}
			}
			
			$tmpFileFullPath = $this->getTmpFileFullPathByPartFilesInfo($filesGroupInfo);
			if (!file_exists($tmpFileFullPath)) {
				$this->writeTempFileByItemsAndTmpFullPath($itemsToRender, $tmpFileFullPath, $minimized);
			}
			
			// render result string by first item attributes
			$firstFileItem = $itemsToRender[0];
			$firstFileItem->path = substr($tmpFileFullPath, strlen($this->getAppRoot()));
			$firstFileItem->attributes['src'] = $this->url($firstFileItem->path);
			$resultItems[] = $this->renderItem((object) $firstFileItem, $indentStr);
		}
		
		// process array with groups, which are not possible to minimize
		foreach ($itemsToRenderSeparately as $attrHashKey => $itemsToRender) {
			foreach ($itemsToRender as $item) {
				$resultItems[] = $this->renderItem((object) $item, $indentStr);
			}
		}
		
		$resultItems[] = $indentStr . '<!-- js group end: ' . $actualGroupName . ' -->';
		
		return $indentStr . implode(PHP_EOL, $resultItems);
	}
	
	protected function getTmpFileFullPathByPartFilesInfo ($filesGroupInfo = array())
	{
		return implode('', array(
			$this->getTmpDir(),
			"/minified_js_",
			md5(
				implode(',', $filesGroupInfo) . '_' . implode(',', array_keys($filesGroupInfo)) . '_' . self::$globalOptions['jsMinify']
			),
			".js"
		));
	}
	
	protected function writeTempFileByItemsAndTmpFullPath ($items = array(), $tmpFileFullPath = '', $minimized)
	{
		$contentStr = array();
		foreach ($items as $item) {
			$fullPath = $this->getAppRoot() . $item->path;
			if (file_exists($fullPath)) {
				$contentStr[] = array(
					"/* " . $item->path . " */",
					file_get_contents($fullPath),
				);
			} else {
				throw new Exception('[App_Views_Helpers_Css] File not found in JS view rendering process. Path: "' . $fullPath . '".');
			}
		}
		
		if ($minimized) {
			if (isset($item->attributes['donotminimize']) && $item->attributes['donotminimize']) {
			} else {
				foreach ($contentStr as $key => $item) {
					$contentStr[$key] = array(
						$item[0],
						$this->minify($item[1]),
					);
				}
			}
		}
		
		$content = '';
		foreach ($contentStr as $key => $item) {
			$content .= $item[0] . PHP_EOL . $item[1] . PHP_EOL;
		}
		
		@file_put_contents($tmpFileFullPath, $content);
		@chmod($tmpFileFullPath, 0766);
		
		return TRUE;
	}
	
	protected function minify ($js)
	{
		try {
			$js = JSMin::minify($js);
		} catch (Exception $e) {
			$msg = "[App_Views_Helpers_Css] Unable to minify javascript.";
			if (class_exists('Debug')) {
				Debug::log($msg, Debug::ERROR);
				Debug::_exceptionHandler($e);
			} else {
				throw $e;
			}
		}
		return $js;
	}
	
	protected function filterItemsForMinimizedRenderingForPossibleMinimizedItems ($items)
	{
		$itemsToRenderMinimized = array();
		$itemsToRenderSeparately = array(); // some configurations is not possible to render together and minimized
		
		// go for every item to complete existing combinations in attributes
		
		foreach ($items as $item) {
			$itemArr = array_merge((array) $item, array());
			
			$src = $itemArr['src'];
			unset($itemArr['src']);

			$attributesClone = (isset($itemArr['attributes']) && is_array($itemArr['attributes'])) ? array_merge($itemArr['attributes'], array()) : array() ;
			if (isset($attributesClone['src'])) unset($attributesClone['src']);
			unset($itemArr['attributes']);
			unset($itemArr['path']);
			
			$renderArrayKey = md5(json_encode($itemArr) . '_' . json_encode($attributesClone));

			if ($itemArr['type'] == 'text/javascript' && (!isset($attributesClone['conditional']) || $attributesClone['conditional'] === FALSE)) {
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
	
	
	/**
	 * Is the script provided valid?
	 *
	 * @param  mixed $value
	 * @param  string $method
	 * @return bool
	 */
	private function _isValid($value)
	{
		if ((!$value instanceof stdClass)
			|| !isset($value->type)
			|| (!isset($value->src) && !isset($value->attributes)))
		{
			return false;
		}

		return true;
	}

	/**
	 * Is script source path duplicate?
	 *
	 * @param  string $uri
	 * @return bool
	 */
	private function _isDuplicateScript ($src)
	{
		foreach (self::$scriptsGroupContainer as $scriptsGroupContainerItem) {
			foreach ($scriptsGroupContainerItem as $item) {
				if ($item->src == $src) {
					return true;
				}
			}
		}
		return false;
	}
	
}
