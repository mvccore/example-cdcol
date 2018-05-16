<?php

namespace App;

class Bootstrap
{
	public static function Init () {

		$app = \MvcCore\Application::GetInstance();

		// patch core to use extended debug class
		if (class_exists('\MvcCore\Ext\Debug\Tracy')) {
			\MvcCore\Ext\Debug\Tracy::$Editor = 'MSVS2017';
			$app->SetDebugClass(\MvcCore\Ext\Debug\Tracy::class);
		}


		// Initialize authentication service extension and set custom user class
		\MvcCore\Ext\Auth::GetInstance()
			->SetPasswordHashSalt('!fa*RN+twe65ts1E9$8z16d')
			->SetUserClass(\MvcCore\Ext\Auth\Users\Database::class)
			/*->SetTableStructureForDbUsers('users', array(
				'id'			=> 'id',
				'userName'		=> 'user_name',
				'passwordHash'	=> 'password_hash',
				'fullName'		=> 'full_name',
			))*/;

		// set up application routes without custom names, defined basicly as Controller::Action
		\MvcCore\Router::GetInstance(array(
			'Index:Index'			=> '/',
			'CdCollection:Index'	=> '/albums',
			'CdCollection:Create'	=> '/create',
			'CdCollection:Edit'	=> array(
				'pattern'			=> "/edit/<id>",
				'constraints'		=> array(
					'id' => '\d'
				),
			),
		));
	}
}
