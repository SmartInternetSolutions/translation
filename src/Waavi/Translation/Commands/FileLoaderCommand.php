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

    // $options = $this->option();
    // if($options['create']=='true' && array_key_exists('locale',$options) && array_key_exists('name',$options)){
    //  $this->createLanguage();
    // }
    $localeDirs = $this->finder->directories($this->path);
    $websites = $this->website->get(array('id'));
    foreach($localeDirs as $localeDir) {
      $locale = str_replace($this->path, '', $localeDir);
      $locale = substr($locale,1);
      
      foreach($websites as $website){
        $language = $this->languageProvider->findByLocaleAndWebsiteId($locale,$website->id);

        if ($language) {
          $langFiles = $this->finder->allFiles($localeDir);  
          foreach($langFiles as $langFile) {
            //get relative file path and change slashes to make them more consistent
            $relativeFilePath = str_replace('\\','/',$langFile->getRelativePathName());
            //if file exists in admin directory then move to next file
            if(strpos($relativeFilePath,'admin/') !== FALSE) continue;
              $group = str_replace('.php','',$relativeFilePath);
              $lines = $this->fileLoader->loadRawLocale($locale, $group);
              $this->languageEntryProvider->loadArray($lines, $language, $group, $website->id, null, $locale == $this->fileLoader->getDefaultLocale()); 
          }
        }
      }
    }
  }

  protected function createLanguage(){
    if($this->option('website_id') == null){
      $this->error('You must provide a website id as parameter.');
      die();
    }
    
    $this->languageProvider->create(array_only($this->options(),array('locale','name','website_id')));
  }

      /**
     * Get the console command options.
     *
     * @return array
     */
    // protected function getOptions()
    // {
    //     return array(
    //       array('create',null,InputOption::VALUE_OPTIONAL,'If there should be language created in database before entries are loaded (true or false)'),
    //         array('locale', null, InputOption::VALUE_OPTIONAL, 'Name of locale to load eg. en, de'),
    //         array('name', null, InputOption::VALUE_OPTIONAL, 'Full name of locale eg. english, german, french'),
    //         array('website_id', null, InputOption::VALUE_OPTIONAL, 'Id of website to create language for.')
    //     );
    // }
}
