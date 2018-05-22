<?php

namespace App\Controllers;

use \MvcCore\Ext\Form,
	\MvcCore\Ext\Auth;

class Base extends \MvcCore\Controller
{
	/** @var \MvcCore\Ext\Auth\Basic\Interfaces\IUser */
	protected $user = null;

	public function Init() {
		parent::Init();
		\MvcCore\Ext\Form::AddCsrfErrorHandler(function (\MvcCore\Ext\Form & $form, $errorMsg) {
			\MvcCore\Ext\Auth\Basic\User::LogOut();
			self::Redirect($this->Url(
				'Index:Index',
				array('absolute' => TRUE, 'sourceUrl'	=> rawurlencode($form->ErrorUrl))
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
		// customize sign out form if necessary, set it into view
		$signOutForm = NULL;
		if ($this->user) {
			/** @var $signOutForm \MvcCore\Ext\Auth\Basic\SignOutForm */
			$signOutForm = \MvcCore\Ext\Auth\Basic::GetInstance()->GetForm();
			$signOutForm->SetDefaults(array(// set signed out url to homepage
				'successUrl' => $this->Url('Index:Index', array('absolute' => TRUE))
			));
			$signOutForm->GetFirstFieldsByClass(Form\SubmitButton::class)->AddCssClass('text-link');
		}
		$this->view->SignOutForm = $signOutForm;
	}
	private function _preDispatchSetUpBundles () {
		\MvcCore\Ext\View\Helpers\Assets::SetGlobalOptions(array(
				'cssMinify'	=> 1,
				'cssJoin'	=> 1,
				'jsMinify'	=> 1,
				'jsJoin'	=> 1,
			));
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
