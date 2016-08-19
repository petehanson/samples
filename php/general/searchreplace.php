<?php

namespace uarsoftware\deploy\action;

/**
 * This class allows for a regular-expression based find + replace on strings
 * and files.
 */
class searchreplace extends \uarsoftware\deploy\base {
  
    /**
     * The regular expression to search for in the contents being updated.
     * This string may omit delimiters, or may use self::DELIMITER as its
     * delimiter.  The character self::DELIMITER must be escaped if used
     * within the string.
     */
    public $searchString = null;
    
    /**
     * The replacement text - will replace any substrings matching the
     * searchString.
     */
    public $replaceString = null;
    
    const DELIMITER = '/';

    public function __construct($searchString, $replaceString) {

        $this->setSearchString($searchString);
        $this->setReplaceString($replaceString);
    }

    public function setSearchString($searchString) {
        $this->searchString = $searchString;
    }

    public function setReplaceString($replaceString) {
        $this->replaceString = $replaceString;
    }

    /**
     * Performs the find/replace within the contents of a file and writes
     * the modified file back to the filesystem.
     *
     * @param $file String containing the filesystem path to a file
     */
    public function updateFile($file) {
        if (!file_exists($file) || !is_file($file)) {
            throw new \Exception("File '". $file ."' does not exists or is not a file\n");
        } else {
            if (!is_writable($file)) {
                throw new \Exception("File '".$file."' is not writable\n");
            }

            $content = file_get_contents($file);
            $contentProcessed = $this->processContents($content);

            file_put_contents($file, $contentProcessed);
        }
    }

    /**
     * Performs the file/replace within a string and returns the modified result
     *
     * @param $content The string to find/replace within
     */
    public function processContents($content) {
        $this->searchString = $this->processSearchString($this->searchString);

        $subject = preg_replace($this->searchString, $this->replaceString, $content);

        if (is_null($subject)) {
            throw new \Exception("An error occurred when trying to use preg_replace().\n");
        }

        return $subject;
    }

    /**
     * Prepare the regular expression find string by giving it delimiters if
     * if doesn't already have them.
     *
     * @param $string a regular expression which may or may not have delimiters already
     */
    public function processSearchString($string) {
        $strLen = strlen($string);
        $firstChar = substr($string, 0, 1);
        $lastChar = substr($string, $strLen-1, 1);

        if ($firstChar != self::DELIMITER) {
            $string = self::DELIMITER.$string;
        }
        if ($lastChar != self::DELIMITER) {
            $string = $string.self::DELIMITER;
        }

        return $string;
    }
}