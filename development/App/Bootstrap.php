<?php

namespace App;

class Bootstrap
{
	public static function Init () {

		$app = \MvcCore\Application::GetInstance();


		// Patch core to use extended debug class:
		if (class_exists('\MvcCore\Ext\Debugs\Tracy')) {
			\MvcCore\Ext\Debugs\Tracy::$Editor = 'MSVS2017';
			$app->SetDebugClass('\MvcCore\Ext\Debugs\Tracy');
		}
		\MvcCore\Ext\Debugs\Tracy::Init();


		// Initialize authentication service extension and set custom user class
		\MvcCore\Ext\Auth::GetInstance()
			->SetPasswordHashSalt('s9E56/QH6!a69sJML9aS$6s+')
			->SetUserClass('\MvcCore\Ext\Auths\Users\Database')
			//->SetUserClass('\MvcCore\Ext\Auths\Users\SystemConfig')
			/*->SetTableStructureForDbUsers('users', [
				'id'			=> 'id',
				'userName'		=> 'user_name',
				'passwordHash'	=> 'password_hash',
				'fullName'		=> 'full_name',
			])*/;
		//die(\MvcCore\Ext\Auths\Basics\User::EncodePasswordToHash('demo'));

		
		// Set up application routes (without custom names),
		// defined basicly as `Controller::Action` combination:
		
		/*\MvcCore\Router::GetInstance([
			'Index:Index'			=> '/',
			'CdCollection:Index'	=> '/albums',
			'CdCollection:Create'	=> '/create',
			'CdCollection:Edit'	=> [
				'pattern'			=> "/edit[/<id>]",
				'defaults'		=> [
					'id' => 1
				],
				'constraints'		=> [
					'id' => '\d+'
				],
			],
			'CdCollection:Test'	=> [
				'pattern'			=> '/edit-something[/<id>/<color>][/<sizes_grid*>]',
				'defaults'	=> [
					'id'			=> 1,
					'color'			=> 'red',
					'sizes_grid'	=> '',
				],
				'constraints'		=> [
					'id'			=> '\d+',
					'color'			=> '[a-z]+',
					'sizes_grid'	=> '.*',
				],
			],
		]);*/
		
		
		$app
			->SetRouterClass('\MvcCore\Ext\Routers\MediaAndLocalization');
			//->SetRouterClass('\MvcCore\Ext\Routers\Media');
			//->SetRouterClass('\MvcCore\Ext\Routers\Localization');

		/** @var $router \MvcCore\Ext\Routers\Localization */
		$router = & \MvcCore\Router::GetInstance();
		$router
			->SetDefaultLocalization('en-US')
			->SetAllowedLocalizations('en-US', 'cs-CZ')
			->SetLocalizationEquivalents([
				'en-US'	=> ['en-GB', 'en-CA', 'en-AU'],
				'cs-CZ'	=> ['sk-SK'],
			])
			->SetSessionExpirationSeconds(\MvcCore\Session::EXPIRATION_SECONDS_DAY)
			->SetRouteGetRequestsOnly(FALSE)
			//->SetTrailingSlashBehaviour(1)
			//->SetRedirectFirstRequestToDefault()
			//->SetStricModeBySession(TRUE)
			->SetRoutes([
				'Index:Index'			=> '/',
				'CdCollection:Index'	=> [
					'pattern'	=> [
						'en'	=> '/albums',
						'cs'	=> '/alba'
					],
				],
				'CdCollection:Create'	=> [
					'pattern'	=> [
						'en'	=> '/create',
						'cs'	=> '/vytvorit'
					]
				],
				'CdCollection:Submit'	=> [
					'pattern'	=> [
						'en'	=> '/save',
						'cs'	=> '/ulozit'
					],
					'method'	=> 'POST'
				],
				new \MvcCore\Ext\Routers\Localizations\Route([
					'match'			=> [
						'en'		=> '#^/edit-neco(/(?<id>\d+))?/?$#',
						'cs'		=> '#^/upravit-neco/(?<id>\d+))?/?$#',
					],
					'defaults'		=> [
						'id'		=> 1,
					],
					'constraints'	=> [
						'id'		=> '\d+'
					],
					'redirect'		=> 'CdCollection:Edit'
				]),
				'CdCollection:Edit'	=> [
					/*'pattern'			=> [
						'en'		=> '/edit[/<id>]',
						'cs'		=> '/upravit[/<id>]'
					],*/
					'match'			=> [
						'en'		=> '#^/edit(/(?<id>\d+))?/?$#',
						'cs'		=> '#^/upravit/(?<id>\d+))?/?$#',
					],
					'reverse'			=> [
						'en'		=> '/edit[/<id>]',
						'cs'		=> '/upravit[/<id>]'
					],
					'defaults'		=> [
						'id'		=> 1,
					],
					'constraints'	=> [
						'id'		=> '\d+'
					],
					'absolute'	=> TRUE
				],
				'CdCollection:Test'	=> [
					'pattern'			=> [
						'en'	=> 'https://<module>.%sld%.%tld%/edit-something[/<id>][/<color*>]',
						'cs'	=> '/upravit-neco[/<id>][/<color*>]'
					],
					'defaults'	=> [
						'id'	=> 1,
						'color'	=> 'red',
					],
					'constraints'		=> [
						'id'	=> '\d+',
						'color'	=> '[a-z]+',
					],
				],
			]);

	}
}
