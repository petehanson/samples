<?php

namespace uarsoftware\deploy\action;

class searchreplace extends \uarsoftware\deploy\base {
  
    public $searchString = null;
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

    public function processContents($content) {
        $this->searchString = $this->processSearchString($this->searchString);

        $subject = preg_replace($this->searchString, $this->replaceString, $content);

        if (is_null($subject)) {
            throw new \Exception("An error occurred when trying to use preg_replace().\n");
        }

        return $subject;
    }

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