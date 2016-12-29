<?php

class App_Bootstrap
{
	public static function Init () {
		@Nette_DebugAdapter::Init(TRUE);

		MvcCore::SetRoutes(array(
			'Default::Default'		=> array(
				'pattern'			=> "#^/$#",
				'reverse'			=> '/',
			),
			'CdCollection::Default'	=> array(
				'pattern'			=> "#^/albums$#",
				'reverse'			=> '/albums',
			),
			'CdCollection::Create'	=> array(
				'pattern'			=> "#^/create#",
				'reverse'			=> '/create',
			),
			'CdCollection::Edit'	=> array(
				'pattern'			=> "#^/edit/([0-9]*)#",
				'reverse'			=> '/edit/{%id}',
				'params'			=> array('id' => 0,),
			),
			'Default::NotFound'		=> array(
				'pattern'			=> "#^/(.*)#",
				'reverse'			=> '/{%path}',
				'params'			=> array('path' => '',),
			),
		));
	}
}