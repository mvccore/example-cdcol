<?php

namespace App\Controllers;

use \MvcCore\Ext\Form;

class Index extends Base
{
    public function IndexAction () {
		if ($this->user instanceof \App\Models\User) {
			self::Redirect($this->Url('CdCollection:'));
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
		/** @var $signInForm \MvcCore\Ext\Auth\SignInForm */
		$signInForm = \MvcCore\Ext\Auth::GetInstance()->GetForm()
			// initialize fields
			->Init()
			// set signed in url to albums list
			->SetDefaults(array(
				'successUrl' => $this->Url('CdCollection:', array('absolute' => TRUE)),
			));
		// remove username label and create input placeholder text
		$signInForm->GetFirstFieldsByClass(Form\Text::class, TRUE)
			->SetLabel('')->SetPlaceholder('login');
		// remove password label and create input placeholder text
		$signInForm->GetFirstFieldsByClass(Form\Password::class)
			->SetLabel('')->SetPlaceholder('password');
		// get submit button and customize submit button inner code
		$signInFormSubmitBtn = $signInForm->GetFirstFieldsByClass(Form\SubmitButton::class);
		$signInFormSubmitBtn->AddCssClass('button-green')->SetValue(
			'<span><b>' . $signInFormSubmitBtn->GetValue() . '</b></span>'
		);
		return $signInForm;
	}
}