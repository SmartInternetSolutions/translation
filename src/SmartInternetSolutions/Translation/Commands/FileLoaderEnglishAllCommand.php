<?php namespace SmartInternetSolutions\Translation\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use SmartInternetSolutions\Translation\Providers\LanguageProvider as LanguageProvider;
use SmartInternetSolutions\Translation\Providers\LanguageEntryProvider as LanguageEntryProvider;

class FileLoaderEnglishAllCommand extends Command {

  /**
* The console command name.
*
* @var string
*/
  protected $name = 'translator:englishToAll';

  /**
* The console command description.
*
* @var string
*/
  protected $description = "Load every missing english lang lines to every language in database.";



/**
   *  Create a new mixed loader instance.
   *
   *  @param  \SmartInternetSolutions\Lang\Providers\LanguageProvider        $languageProvider
   *  @param  \SmartInternetSolutions\Lang\Providers\LanguageEntryProvider   $languageEntryProvider
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
    $en_locale_path = $this->finder->isDirectory($this->path.'/en') ? $this->path.'/en' : false;
    if($en_locale_path){
      $db_langs = $this->languageProvider->findAll();
      $lang_files = $this->finder->allFiles($en_locale_path);
        foreach($lang_files as $lang_file) {
          //get relative file path and change slashes to make them more consistent
          $relativeFilePath = str_replace('\\','/',$lang_file->getRelativePathName());
          //if file exists in admin directory then move to next file
          if(strpos($relativeFilePath,'admin/') !== FALSE) continue;
          $group = str_replace('.php','',$relativeFilePath);
          $lines = $this->fileLoader->loadRawLocale('en', $group);
          foreach($db_langs as $db_lang){
            //if db_lang locale is en then we will update all entries because its root language, otherwise we will block updating texts (for other languages)
            if($db_lang->locale === 'en'){
              $this->languageEntryProvider->loadArray($lines, $db_lang, $group, $db_lang->website_id, null, true); 
            }
            else{
              $this->languageEntryProvider->loadArray($lines, $db_lang, $group, $db_lang->website_id, null, false,true); 
            }
            
          }
        }
    }  
  }
}
