<?php

namespace App;

class Bootstrap {

	/**
	 * @return \MvcCore\Application
	 */
	public static function Init () {

		$app = \MvcCore\Application::GetInstance();


		// Patch core to use extended debug class:
		if (class_exists('MvcCore\Ext\Debugs\Tracy')) {
			\MvcCore\Ext\Debugs\Tracy::$Editor = 'MSVS2019';
			$app->SetDebugClass('MvcCore\Ext\Debugs\Tracy');
		}


		/**
		 * Uncomment this line before generate any assets into temporary directory, before application
		 * packing/building, only if you want to pack application without JS/CSS/fonts/images inside
		 * result PHP package and you want to have all those files placed on hard drive.
		 * You can use this variant in modes `PHP_PRESERVE_PACKAGE`, `PHP_PRESERVE_HDD` and `PHP_STRICT_HDD`.
		 */
		//\MvcCore\Ext\Views\Helpers\Assets::SetAssetUrlCompletion(FALSE);


		// Initialize authentication service extension and set custom user class
		\MvcCore\Ext\Auths\Basic::GetInstance()

			// Set unique password hash:
			->SetPasswordHashSalt('s9E56/QH6.a69sJML9aS6s')

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
			'Index:Index'			=> [
				'match'				=> '#^/(index\.php)?$#',
				'reverse'			=> '/',
			],
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
				'defaults'			=> ['id' => 0,],
				'constraints'		=> ['id' => '\d+'],
			]
		]);

		return $app;
	}
}
