<?php

class App_Controllers_Default extends App_Controllers_Base
{
    public function DefaultAction () {
		if ($this->user instanceof App_Models_User) {
			self::Redirect($this->Url('CdCollection:Default'));
		}
		$this->view->Title = 'CD Collection';
		$this->view->User = $this->user;
		$this->view->SignInForm = $this->getSignInFormCustomized();
    }
    public function NotFoundAction () {
		$this->view->Title = "Error 404 - requested page not found.";
		$this->view->Message = $this->request->Params['message'];
    }

	protected function getSignInFormCustomized () {
		// customize sign in form
		/** @var $signInForm MvcCoreExt_Auth_SignInForm */
		$signInForm = MvcCoreExt_Auth::GetInstance()->GetForm()
			// initialize fields
			->Init()
			// set signed in url to albums list
			->SetDefaults(array(
				'successUrl' => $this->Url('CdCollection:Default', array('absolute' => TRUE)),
			));
		// remove username label and create input placeholder text
		$signInForm->GetFirstFieldsByClass(SimpleForm_Text::class, TRUE)
			->SetLabel('')->SetPlaceholder('login');
		// remove password label and create input placeholder text
		$signInForm->GetFirstFieldsByClass(SimpleForm_Password::class)
			->SetLabel('')->SetPlaceholder('password');
		// get submit button and customize submit button inner code
		$signInFormSubmitBtn = $signInForm->GetFirstFieldsByClass(SimpleForm_SubmitButton::class);
		$signInFormSubmitBtn->AddCssClass('button-green')->SetValue(
			'<span><b>' . $signInFormSubmitBtn->GetValue() . '</b></span>'
		);
		return $signInForm;
	}
}