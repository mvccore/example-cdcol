<?php

class App_Bootstrap
{
	public static function Init () {
		// patch core to use extended debug class
		MvcCore::GetInstance()->SetDebugClass(MvcCoreExt_Tracy::class);

		// use this line only if you want to pack application without JS/CSS/fonts/images
		// inside package and you want to have all those files placed on hard drive manualy. 
		// You can use this variant in modes PHP_PRESERVE_PACKAGE, PHP_PRESERVE_HDD and PHP_STRICT_HDD
		//MvcCoreExt_ViewHelpers_Assets::SetAssetUrlCompletion(FALSE);

		// add another view helper namespace
		MvcCore_View::AddHelpersClassBases('MvcCoreExt_ViewHelpers');
		
		// Initialize authentication service extension and set custom user class
		MvcCoreExt_Auth::GetInstance()->Init()->SetUserClass(App_Models_User::class);
		
		// set up application routes without custom names, defined basicly as Controller::Action
		MvcCore_Router::GetInstance(array(
			'Default:Default'		=> array(
				'pattern'			=> "#^/$#",
				'reverse'			=> '/',
			),
			'CdCollection:Default'	=> array(
				'pattern'			=> "#^/albums$#",
				'reverse'			=> '/albums',
			),
			'CdCollection:Create'	=> array(
				'pattern'			=> "#^/create#",
				'reverse'			=> '/create',
			),
			'CdCollection:Edit'	=> array(
				'pattern'			=> "#^/edit/([0-9]*)#",
				'reverse'			=> '/edit/{%id}',
				'params'			=> array('id' => 0,),
			),
		));
	}
}