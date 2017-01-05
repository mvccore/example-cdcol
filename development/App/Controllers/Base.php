<?php

class App_Controllers_Base extends MvcCore_Controller
{
	protected static $sessionHashKey = 'hash';
	protected static $sessionErrorsBaseKey = 'form_errors_';

	protected static $staticPath = '/static/';
	protected static $tmpPath = '/Var/Tmp';

	protected $user = null;

	public function Init() {
		parent::Init();
		if (isset($_SESSION['user_name'])) {
			$this->user = App_Models_User::GetByUserName($_SESSION['user_name']);
		}
	}
	public function PreDispatch () {
		parent::PreDispatch();
		if (!$this->ajax && $this->request->params['controller'] !== 'assets') {
			$this->view->User = $this->user;
			App_Views_Helpers_Assets::SetGlobalOptions(array(
				'cssMinify'	=> 1,
				'cssJoin'	=> 1,
				'jsMinify'	=> 1,
				'jsJoin'	=> 1,
				'tmpDir'	=> self::$tmpPath,
				// for PHAR packing - uncomment line bellow to "md5_file"
				//'fileChecking'	=> 'md5_file',
			));
			$this->view->Css('fixedHead')
				->AppendRendered(self::$staticPath . 'css/fonts.css')
				->AppendRendered(self::$staticPath . 'css/all.css')
				->AppendRendered(self::$staticPath . 'css/button.css');
			$this->view->Js('fixedHead')
				->Append(self::$staticPath . 'js/libs/class.min.js')
				->Append(self::$staticPath . 'js/libs/ajax.min.js')
				->Append(self::$staticPath . 'js/libs/Module.js');
			$this->view->Js('varFoot')
				->Append(self::$staticPath . 'js/Front.js');
		}
	}
	protected function redirectToNotFound () {
		self::Redirect($this->url('Default::NotFound'), 404);
	}
	protected function formErrors ($formId = '', $error = '') {
		$sessionKey = self::$sessionErrorsBaseKey . $formId;
		if (!isset($_SESSION[$sessionKey])) {
			$_SESSION[$sessionKey] = array();
		}
		if ($error) {
			$_SESSION[$sessionKey][] = $error;
			$result = $_SESSION[$sessionKey];
		} else {
			$result = array_merge(array(), $_SESSION[$sessionKey]);
			$_SESSION[$sessionKey] = array();
		}
		return $result;
	}
	protected function checkSessionHash () {
		$postedHash = $this->GetParam('csrf', "");
		$sessionHash = self::GetSessionHash();
		$result = $sessionHash == $postedHash;
		self::setUpNewSessionHash();
		return $result;
	}
	protected static function setUpNewSessionHash () {
		$_SESSION[self::$sessionHashKey] = bin2hex(openssl_random_pseudo_bytes(32));
	}
	public static function GetSessionHash () {
		if (!isset($_SESSION[self::$sessionHashKey])) self::setUpNewSessionHash();
		return $_SESSION[self::$sessionHashKey];
	}
}