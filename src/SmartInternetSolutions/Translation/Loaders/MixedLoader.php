<?php namespace SmartInternetSolutions\Translation\Loaders;

use Illuminate\Translation\LoaderInterface;
use SmartInternetSolutions\Translation\Loaders\Loader;
use SmartInternetSolutions\Translation\Providers\LanguageProvider as LanguageProvider;
use SmartInternetSolutions\Translation\Providers\LanguageEntryProvider as LanguageEntryProvider;

class MixedLoader extends Loader implements LoaderInterface {

	/**
	 *	The file loader.
	 *	@var \SmartInternetSolutions\Translation\Loaders\FileLoader
	 */
	protected $fileLoader;

	/**
	 *	The database loader.
	 *	@var \SmartInternetSolutions\Translation\Loaders\DatabaseLoader
	 */
	protected $databaseLoader;

	/**
	 * 	Create a new mixed loader instance.
	 *
	 * 	@param  \SmartInternetSolutions\Lang\Providers\LanguageProvider  			$languageProvider
	 * 	@param 	\SmartInternetSolutions\Lang\Providers\LanguageEntryProvider		$languageEntryProvider
	 *	@param 	\Illuminate\Foundation\Application  					$app
	 */
	public function __construct($languageProvider, $languageEntryProvider, $app, $website_id=null)
	{
		parent::__construct($languageProvider, $languageEntryProvider, $app);
		$this->fileLoader 		= new FileLoader($languageProvider, $languageEntryProvider, $app);
		$this->databaseLoader = new DatabaseLoader($languageProvider, $languageEntryProvider, $app, $website_id);
		$this->website_id = $website_id;
	}

	/**
	 * Load the messages strictly for the given locale.
	 *
	 * @param  Language  	$language
	 * @param  string  		$group
	 * @param  string  		$namespace
	 * @return array
	 */
	public function loadRawLocale($locale, $group, $namespace = null)
	{
		$namespace = $namespace ?: '*';
		return array_merge($this->fileLoader->loadRawLocale($locale, $group, $namespace), $this->databaseLoader->loadRawLocale($locale, $group, $namespace));
	}
}