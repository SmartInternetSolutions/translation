<?php namespace SmartInternetSolutions\Translation\Commands;

use Symfony\Component\Finder\Finder;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class FindTranslationUsages extends Command {
    protected $name = 'translator:find';
    
    protected $description = 'Finds every usage of any translation key.';

    /** @var \Illuminate\Foundation\Application  */
    protected $app;
    
    /** @var \Illuminate\Filesystem\Filesystem  */
    protected $files;

    public function __construct() {
        parent::__construct();
        
        $this->files = \App::make('files');
    }

    protected function getOptions() {
        return array(
            array('untranslated-only', null, InputOption::VALUE_NONE, 'Dumps only translation keys which are not translated')
        );
    }
    
    public function fire() { // taken from https://github.com/barryvdh/laravel-translation-manager/blob/master/src/Barryvdh/TranslationManager/Manager.php

        $path = \App::make('path');
        
        $keys = array();
        $functions = array('trans', 'trans_choice', 'Lang::get', 'Lang::choice', 'Lang::trans', 'Lang::transChoice', '@lang', '@choice');
        $pattern =                              // See http://regexr.com/392hu
            "(".implode('|', $functions) .")".  // Must start with one of the functions
            "\(".                               // Match opening parenthese
            "[\'\"]".                           // Match " or '
            "(".                                // Start a new group to match:
                "[a-zA-Z0-9_-]+".               // Must start with group
                "([\/.][^\1)]+)+".              // Be followed by one or more items/keys (CR: added / here)
            ")".                                // Close group
            "[\'\"]".                           // Closing quote
            "[\),]";                            // Close parentheses or new parameter

        // Find all PHP + Twig files in the app folder, except for storage
        $finder = new Finder();
        $finder->in($path)->exclude('storage')->name('*.php')->name('*.twig')->files();

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            // Search the current file for the pattern
            if (preg_match_all("/$pattern/iU", $file->getContents(), $matches)) {
                // Get all matches
                foreach ($matches[2] as $key) {
                    if (!$this->option('untranslated-only') || \Lang::get($key) === $key) {
                        $keys[] = $key;
                    }
                }
            }
        }
        
        // Remove duplicates
        $keys = array_unique($keys);

        // Sort
        sort($keys);
        
        // Add the translations to the database, if not existing.
        foreach ($keys as $key) {
            // Split the group and item
            $this->line($key);
        }
    }
}