<?php

class App_Views_Helpers_JsonAttr
{
	protected $lastResult = '';
	/**
	 * Convert any php value to json format, which is available to use in html attribute
	 * @param $object mixed
	 */
	public function JsonAttr ($object = NULL)
	{
		$this->lastResult = rawurlencode(
			json_encode($object, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)
		);
		return $this;
	}
	
	public function __toString ()
	{
		return $this->lastResult;
	}
}