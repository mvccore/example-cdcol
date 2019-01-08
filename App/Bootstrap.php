<?php

namespace App;

class Bootstrap
{
	public static function Init ()
	{
		$app = \MvcCore\Application::GetInstance();


		// Patch core to use extended debug class:
		if (class_exists('MvcCore\Ext\Debugs\Tracy')) {
			\MvcCore\Ext\Debugs\Tracy::$Editor = 'MSVS2017';
			$app->SetDebugClass('MvcCore\Ext\Debugs\Tracy');
		}


		// Initialize authentication service extension and set custom user class
		\MvcCore\Ext\Auths\Basic::GetInstance()

			// Set unique password hash:
			->SetPasswordHashSalt('s9E56/QH6!a69sJML9aS$6s+')
			
			// To use credentials from system config file:
			//->SetUserClass('MvcCore\Ext\Auths\Basics\Users\SystemConfig')
			
			// To use credentials from database:
			->SetUserClass('MvcCore\Ext\Auths\Basics\Users\Database')

			// To describe basic credentials database structure
			/*->SetTableStructureForDbUsers('users', [
				'id'			=> 'id',
				'userName'		=> 'user_name',
				'passwordHash'	=> 'password_hash',
				'fullName'		=> 'full_name',
			])*/;
		
		// Display db password hash value by unique password hash for desired user name:
		//die(\MvcCore\Ext\Auths\Basics\User::EncodePasswordToHash('demo'));

		
		// Set up application routes (without custom names),
		// defined basically as `Controller::Action` combinations:
		\MvcCore\Router::GetInstance([
			'Index:Index'			=> '/[index.php]',
			'CdCollection:Index'	=> '/albums',
			'CdCollection:Create'	=> '/create',
			'CdCollection:Submit'	=> [
				'pattern'			=> '/save',
				'method'			=> 'POST'
			],
			'CdCollection:Edit'		=> [
				//'pattern'			=> '/edit[/<id>]',
				'match'				=> '#^/edit(/(?<id>\d+))?/?$#',
				'reverse'			=> '/edit[/<id>]',
				'defaults'			=> ['id' => 1,],
				'constraints'		=> ['id' => '\d+'],
			]
		]);
	}
}
