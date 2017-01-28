<?php

namespace App\Controllers;

use \MvcCore\Ext\Form,
	\MvcCore\Ext\Auth;

class Base extends \MvcCore\Controller
{
	/** @var \MvcCore\Ext\Auth\Virtual\User */
	protected $user = null;

	public function Init() {
		parent::Init();
		$this->user = Auth::GetInstance()->GetUser();
		Form::AddCsrfErrorHandler(function (Form & $form, $errorMsg) {
			Auth\User::ClearFromSession();
			self::Redirect($this->Url(
				'Index:Index',
				array('absolute' => TRUE, 'sourceUrl'	=> urlencode($form->ErrorUrl))
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
			/** @var $signOutForm \MvcCore\Ext\Auth\SignOutForm */
			$signOutForm = Auth::GetInstance()->GetForm()
				// initialize fields
				->Init()
				// set signed out url to homepage
				->SetDefaults(array(
					'successUrl' => $this->Url('Index:Index', array('absolute' => TRUE))
				));
			$signOutForm
				// replace sign out <button> tag to sign out <input> tag
				->RemoveField(
					$signOutForm->GetFirstFieldsByClass(Form\SubmitButton::class)->Name
				)
				->AddField(new Form\SubmitInput(array(
					'name'		=> 'send',
					'value'		=> 'Sign Out',
					'cssClasses'=> array('text-link')
				))
			);
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
			->AppendRendered($static . '/css/fonts.css')
			->AppendRendered($static . '/css/all.css')
			->AppendRendered($static . '/css/button.css');
		$this->view->Js('fixedHead')
			->Append($static . '/js/libs/class.min.js')
			->Append($static . '/js/libs/ajax.min.js')
			->Append($static . '/js/libs/Module.js');
		$this->view->Js('varFoot')
			->Append($static . '/js/Front.js');
	}
}