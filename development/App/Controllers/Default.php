<?php

class App_Controllers_Default extends App_Controllers_Base
{
    public function DefaultAction () {
		$this->view->Title = 'CD collection';
		$this->view->Errors = $this->formErrors('login');
		$this->view->User = $this->user;
    }
	public function LoginAction () {
		$result = FALSE;
		if ($this->checkSessionHash()) {
			$userName = $this->GetParam("username");
			$password = $this->GetParam("password", "a-zA-Z0-9_/\-\.\*\#\?\!\(\)\[\]\{\}\&\$\@\%\=\+\^\~\:\;");
			$user = App_Models_User::GetByUserName($userName);
			if (!$user) {
				$this->formErrors('login', 'Wrong user name.');
			} else if ($user->PasswordHash == App_Models_User::HashPassword($password)) {
				$_SESSION['user_name'] = $user->UserName;
				$result = TRUE;
			} else {
				$this->formErrors('login', 'Wrong password.');
			}
		}
		if ($result) {
			self::Redirect($this->Url('CdCollection::Default'));
		} else {
			sleep(3);
			self::Redirect($this->Url('Default::Default'));
		}
	}
	public function LogoutAction () {
		$result = FALSE;
		if ($this->checkSessionHash()) {
			unset($_SESSION['user_name']);
			$result = TRUE;
		}
		if ($result) {
			self::Redirect($this->Url('Default::Default'));
		} else {
			sleep(3);
			self::Redirect($this->Url('CdCollection::Default'));
		}
	}
    public function NotFoundAction () {
    }
}