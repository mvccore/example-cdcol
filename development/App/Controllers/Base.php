<?php

namespace App\Controllers;

class Base extends \MvcCore\Controller
{
	/**
	 * Authenticated user instance is automaticly asigned
	 * by authentication extension before `Controller::Init();`.
	 * @var \MvcCore\Ext\Auths\Basics\IUser
	 */
	protected $user = NULL;

	public function Init() {
		parent::Init();
		// when any CSRF token is outdated or not the same - sign out user by default
		\MvcCore\Ext\Form::AddCsrfErrorHandler(function (\MvcCore\Ext\Form & $form, $errorMsg) {
			\MvcCore\Ext\Auths\User::LogOut();
			self::Redirect($this->Url(
				'Index:Index',
				['absolute' => TRUE, 'sourceUrl'	=> rawurlencode($form->GetErrorUrl())]
			));
		});
	}

	public function PreDispatch () {
		parent::PreDispatch();
		if ($this->viewEnabled) {
			$this->_preDispatchSetUpAuth();
			$this->_preDispatchSetUpBundles();
		}
	}

	private function _preDispatchSetUpAuth () {
		// init user in view
		$this->view->User = $this->user;
		if ($this->user) {
			// set signout form into view, set signedout url to homepage:
			$this->view->SignOutForm = \MvcCore\Ext\Auth::GetInstance()
				->GetSignOutForm()
				->SetValues([
					'successUrl' => $this->Url('Index:Index', ['absolute' => TRUE])
				]);
		}
	}

	private function _preDispatchSetUpBundles () {
		\MvcCore\Ext\Views\Helpers\Assets::SetGlobalOptions([
				'cssMinify'	=> 1,
				'cssJoin'	=> 1,
				'jsMinify'	=> 1,
				'jsJoin'	=> 1,
			]);
		$static = self::$staticPath;
		$this->view->Css('fixedHead')
			->Append($static . '/css/resets.css')
			->Append($static . '/css/old-browsers-warning.css')
			->AppendRendered($static . '/css/fonts.css')
			->AppendRendered($static . '/css/all.css')
			->AppendRendered($static . '/css/forms-and-controls.css')
			->AppendRendered($static . '/css/content-buttons.css')
			->AppendRendered($static . '/css/content-tables.css');
		$this->view->Js('fixedHead')
			->Append($static . '/js/libs/class.min.js')
			->Append($static . '/js/libs/ajax.min.js')
			->Append($static . '/js/libs/Module.js');
		$this->view->Js('varFoot')
			->Append($static . '/js/Front.js');
	}
}
