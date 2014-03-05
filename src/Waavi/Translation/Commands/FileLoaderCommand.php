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
    //find locale directories
    $localeDirs = $this->finder->directories($this->path);
    //if website_id was provided then we will load translations only for this website
    if($website_id = $this->option('website_id')!==null){
      $this->website->where('website_id',$website_id);
    }
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
    protected function getOptions()
    {
        return array(
            array('website_id', null, InputOption::VALUE_OPTIONAL, 'Id of website to create language for.')
        );
    }
}
