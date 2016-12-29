<?php

class App_Controllers_Assets extends App_Controllers_Base
{
	protected static $imgExtsAndMimeTypes = array(
		'ico'	=> 'image/x-icon',
		'gif'	=> 'image/gif',
		'png'	=> 'image/png',
		'jpg'	=> 'image/jpg',
		'jpeg'	=> 'image/jpeg',
		'bmp'	=> 'image/bmp',
		'svg'	=> 'image/svg+xml',
	);
	protected $path = '';
	public function PreDispatch() {
		parent::PreDispatch();
		$path = $this->GetParam('path');
		$path = '/' . ltrim(str_replace("..", "", $path), '/');
		if (
			strpos($path, self::$staticPath) !== 0 && 
			strpos($path, self::$tmpPath) !== 0
		) {
			throw new Exception("[App_Controllers_Assets] File path: '$path' is not allowed.");
		}
		$path = $this->request->appRoot . $path;
		if (!file_exists($path)) {
			if (Debug::$productionMode) {
				$this->redirectToNotFound();
			} else {
				throw new Exception("[App_Controllers_Assets] File not found: '$path'.");
			}
		}
		$this->path = $path;
		$this->DisableView();
	}
	public function JsAction () {
		header('Content-Type: text/javascript');
		readfile($this->path);
	}
	public function CssAction () {
		header('Content-Type: text/css');
		readfile($this->path);
	}
	public function ImgAction () {
		$ext = '';
		$lastDotPos = strrpos($this->path, '.');
		if ($lastDotPos !== FALSE) {
			$ext = substr($this->path, $lastDotPos + 1);
		}
		if (isset(self::$imgExtsAndMimeTypes[$ext])) {
			header('Content-Type: ' . self::$imgExtsAndMimeTypes[$ext]);
		}
		readfile($this->path);
	}
}