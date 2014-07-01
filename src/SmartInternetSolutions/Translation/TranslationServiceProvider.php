<?php namespace SmartInternetSolutions\Translation;

use Illuminate\Translation\TranslationServiceProvider as LaravelTranslationServiceProvider;
use SmartInternetSolutions\Translation\Facades\Translator;
use SmartInternetSolutions\Translation\Loaders\FileLoader;
use SmartInternetSolutions\Translation\Loaders\DatabaseLoader;
use SmartInternetSolutions\Translation\Loaders\MixedLoader;
use SmartInternetSolutions\Translation\Providers\LanguageProvider;
use SmartInternetSolutions\Translation\Providers\LanguageEntryProvider;

class TranslationServiceProvider extends LaravelTranslationServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->package('SmartInternetSolutions/translation', 'SmartInternetSolutions/translation', __DIR__.'/../..');

		$this->registerLoader();
		$this->registerCommands();
        
        if (!isset($this->app['translator'])) {
            $this->app['translator'] = $this->app->share(function($app)
            {
                $loader = $app['translation.loader'];

                // When registering the translator component, we'll need to set the default
                // locale as well as the fallback locale. So, we'll grab the application
                // configuration so we can easily get both of these values from there.
                $locale = $app['config']['app.locale'];

                return new Translator($loader, $locale);
            });
        }
	}

	/**
	 * Register the translation line loader.
	 *
	 * @return void
	 */
	protected function registerLoader()
	{
        if (!isset($this->app['translation.loader'])) {
            $this->app['translation.loader'] = $this->app->share(function($app) {
                $languageProvider 	= new LanguageProvider($app['config']['SmartInternetSolutions/translation::language.model']);
                $langEntryProvider 	= new LanguageEntryProvider($app['config']['SmartInternetSolutions/translation::language_entry.model']);
                $website_id = null;
                $mode = $app['config']['SmartInternetSolutions/translation::mode'];

                if ($mode == 'auto' || empty($mode)){
                    $mode = ($app['config']['app.debug'] ? 'mixed' : 'database');
                }

                switch ($mode) {
                    case 'mixed':
                        return new MixedLoader($languageProvider, $langEntryProvider, $app, $website_id);

                    default: case 'filesystem':
                        return new FileLoader($languageProvider, $langEntryProvider, $app);

                    case 'database':
                        return new DatabaseLoader($languageProvider, $langEntryProvider, $app, $website_id);
                }
            });
        }
	}

	/**
     * register commands
     * 
	 * @return void
	 */
	public function registerCommands()
	{
		$this->app['translator.load'] = $this->app->share(function ($app) {
			$languageProvider 	= new LanguageProvider($app['config']['SmartInternetSolutions/translation::language.model']);
			$langEntryProvider 	= new LanguageEntryProvider($app['config']['SmartInternetSolutions/translation::language_entry.model']);
			$fileLoader 		= new FileLoader($languageProvider, $langEntryProvider, $app);
			$website = new \Models\Website;

			return new Commands\FileLoaderCommand($languageProvider, $langEntryProvider, $fileLoader,$website);
		});

		$this->commands('translator.load');
        
		$this->app['translator.loadEnglishAll'] = $this->app->share(function ($app) {
			$languageProvider 	= new LanguageProvider($app['config']['SmartInternetSolutions/translation::language.model']);
			$langEntryProvider 	= new LanguageEntryProvider($app['config']['SmartInternetSolutions/translation::language_entry.model']);
			$fileLoader 		= new FileLoader($languageProvider, $langEntryProvider, $app);
			$website = new \Models\Website;

			return new Commands\FileLoaderEnglishAllCommand($languageProvider, $langEntryProvider, $fileLoader,$website);
		});
        
		$this->commands('translator.loadEnglishAll');
        
        $this->app['translator.find'] = $this->app->share(function () {
            return new Commands\FindTranslationUsages;
        });
        
		$this->commands('translator.find');
	}
	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('translator', 'translation.loader');
	}

}
