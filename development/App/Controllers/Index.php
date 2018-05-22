<?php

namespace App\Controllers;

use \MvcCore\Ext\Form;

class Index extends Base
{
	public function IndexAction () {
		if ($this->user !== NULL)
			self::Redirect($this->Url('CdCollection:Index'));
		$this->view->Title = 'CD Collection';
		$this->view->User = $this->user;
		$this->view->SignInForm = $this->getSignInForm();
	}
	public function NotFoundAction(){
		$this->ErrorAction();
	}
	public function ErrorAction(){
		$code = $this->response->GetCode();
		$message = $this->request->GetParam('message', '\\a-zA-Z0-9_;, /\-\@\:');
		$message = preg_replace('#`([^`]*)`#', '<code>$1</code>', $message);
		$message = str_replace("\n", '<br />', $message);
		$this->view->Title = "Error $code";
		$this->view->Message = $message;
		$this->Render('error');
	}

	protected function getSignInForm () {
		// you can customize sign in form here:
		/** @var $signInForm \MvcCore\Ext\Auth\Basic\SignInForm */
		$signInForm = \MvcCore\Ext\Auth\Basic::GetInstance()->GetForm();
		// add 'theme' css class to style the form by css stylesheet
		$signInForm->AddCssClass('theme');
		// set signed in url to albums list by default:
		$signInForm->SetDefaults(array(
			'successUrl' => $this->Url('CdCollection:', array('absolute' => TRUE)),
		));
		return $signInForm;
	}
}
