<?php

namespace App;

class Bootstrap
{
	public static function Init () {

		$app = \MvcCore\Application::GetInstance();


		// Patch core to use extended debug class:
		if (class_exists('\MvcCore\Ext\Debugs\Tracy')) {
			\MvcCore\Ext\Debugs\Tracy::$Editor = 'MSVS2017';
			$app->SetDebugClass(\MvcCore\Ext\Debugs\Tracy::class);
		}


		// Initialize authentication service extension and set custom user class
		\MvcCore\Ext\Auth::GetInstance()
			->SetPasswordHashSalt('s9E56/QH6!a69sJML9aS$6s+')
			->SetUserClass(\MvcCore\Ext\Auths\Users\Database::class)
			//->SetUserClass(\MvcCore\Ext\Auths\Users\SystemConfig::class)
			/*->SetTableStructureForDbUsers('users', array(
				'id'			=> 'id',
				'userName'		=> 'user_name',
				'passwordHash'	=> 'password_hash',
				'fullName'		=> 'full_name',
			))*/;
		//die(\MvcCore\Ext\Auths\Basics\User::EncodePasswordToHash('demo'));


		// Set up application routes (without custom names),
		// defined basicly as `Controller::Action` combination:
		\MvcCore\Router::GetInstance([
			'Index:Index'			=> '/',
			'CdCollection:Index'	=> '/albums',
			'CdCollection:Create'	=> '/create',
			'CdCollection:Edit'	=> [
				'pattern'			=> "/edit/<id>",
				'constraints'		=> [
					'id' => '\d+'
				],
			],
		]);

	}
}
