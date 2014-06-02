<?php namespace SmartInternetSolutions\Translation\Facades;

use Illuminate\Translation\Translator as LaravelTranslator;

class Translator extends LaravelTranslator {

	/**
	 *	Returns the language provider:
	 *	@return SmartInternetSolutions\Translation\Providers\LanguageProvider
	 */
	public function getLanguageProvider()
	{
		return $this->loader->getLanguageProvider();
	}

	/**
	 *	Returns the language entry provider:
	 *	@return SmartInternetSolutions\Translation\Providers\LanguageEntryProvider
	 */
	public function getLanguageEntryProvider()
	{
		return $this->loader->getLanguageEntryProvider();
	}

}