<?php namespace SmartInternetSolutions\Translation\Loaders;

use Illuminate\Translation\LoaderInterface;
use SmartInternetSolutions\Translation\Loaders\Loader;
use SmartInternetSolutions\Translation\Providers\LanguageProvider as LanguageProvider;
use SmartInternetSolutions\Translation\Providers\LanguageEntryProvider as LanguageEntryProvider;

class DatabaseLoader extends Loader implements LoaderInterface {

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
		$langArray 	= array();
		$namespace = $namespace ?: '*';
		$language 	= $this->languageProvider->findByLocaleAndWebsiteId($locale,$this->website_id);
		if ($language) {
			$entries = $language->entries()->where('website_id','=',$this->website_id)->where('group', '=', $group)->where('namespace', '=', $namespace)->get();
			if ($entries) {
				foreach($entries as $entry) {
					array_set($langArray, $entry->item, $entry->text);
				}
			}
		}
		return $langArray;
	}
}