<?php

class App_Controllers_Base extends MvcCore_Controller
{
	/** @var MvcCoreExt_Auth_Abstract_User */
	protected $user = null;

	public function Init() {
		parent::Init();
		$this->user = MvcCoreExt_Auth::GetInstance()->GetUser();
		SimpleForm::AddCsrfErrorHandler(function (SimpleForm & $form, $errorMsg) {
			MvcCoreExt_Auth_User::ClearFromSession();
			self::Redirect($this->Url(
				'Default:Default',
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
			/** @var $signOutForm MvcCoreExt_Auth_SignOutForm */
			$signOutForm = MvcCoreExt_Auth::GetInstance()->GetForm()
				// initialize fields
				->Init()
				// set signed out url to homepage
				->SetDefaults(array(
					'successUrl' => $this->Url('Default:Default', array('absolute' => TRUE))
				));
			$signOutForm
				// replace sign out <button> tag to sign out <input> tag
				->RemoveField(
					$signOutForm->GetFirstFieldsByClass(SimpleForm_SubmitButton::class)->Name
				)
				->AddField(new SimpleForm_SubmitInput(array(
					'name'		=> 'send',
					'value'		=> 'Sign Out',
					'cssClasses'=> array('text-link')
				))
			);
		}
		$this->view->SignOutForm = $signOutForm;
	}
	private function _preDispatchSetUpBundles () {
		MvcCoreExt_ViewHelpers_Assets::SetGlobalOptions(array(
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