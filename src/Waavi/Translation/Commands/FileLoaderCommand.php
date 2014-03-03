<?php namespace Waavi\Translation\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Waavi\Translation\Providers\LanguageProvider as LanguageProvider;
use Waavi\Translation\Providers\LanguageEntryProvider as LanguageEntryProvider;

class FileLoaderCommand extends Command {

  /**
* The console command name.
*
* @var string
*/
  protected $name = 'translator:load';

  /**
* The console command description.
*
* @var string
*/
  protected $description = "Load language files into the database.";



/**
   *  Create a new mixed loader instance.
   *
   *  @param  \Waavi\Lang\Providers\LanguageProvider        $languageProvider
   *  @param  \Waavi\Lang\Providers\LanguageEntryProvider   $languageEntryProvider
   *  @param  \Illuminate\Foundation\Application            $app
   */
  public function __construct($languageProvider, $languageEntryProvider, $fileLoader,\Models\Website $website)
  {
    parent::__construct();
    $this->website = $website;
    $this->languageProvider       = $languageProvider;
    $this->languageEntryProvider  = $languageEntryProvider;
    $this->fileLoader             = $fileLoader;
    $this->finder                 = new Filesystem();
    $this->path                   = app_path().'/lang';
  }

  /**
   * Execute the console command.
   *
   * @return void
   */
  public function fire()
  {
    $localeDirs = $this->finder->directories($this->path);
    foreach($localeDirs as $localeDir) {
      $locale = str_replace($this->path, '', $localeDir);
      $locale = substr($locale,1);
      $language = $this->languageProvider->findByLocale($locale);

      $websites = $this->website->get(array('id'));
      if ($language) {
        $langFiles = $this->finder->files($localeDir);
        foreach($websites as $website){
          foreach($langFiles as $langFile) {
            $group = str_replace(array($localeDir.'/', '.php'), '', $langFile);
            $lines = $this->fileLoader->loadRawLocale($locale, $group);
            
            $this->languageEntryProvider->loadArray($lines, $language, $group, $website->id, null, $locale == $this->fileLoader->getDefaultLocale());
          }
        }
      }
    }
  }
}
