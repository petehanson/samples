<?php

namespace uarsoftware\deploy\helpers;
use uarsoftware\deploy\action as action;
use uarsoftware\deploy\enum\logLevel as LogLevel;

/**
 * This class handles creation and permission handling of local folders.
 */
class Folder extends abstractFolder
{
    
    /**
     * Returns true if a folder exists at the local path specified by $folder
     */
    public function isFolder($folder) {
        if (!file_exists($folder) OR !is_dir($folder)) {
            return false;
        }

        return true;
    }

    /**
     * Creates a folder at the local path $path
     */
    public function createFolder($path) {
        $this->_command->setCommand("mkdir -p {$path}");
        $this->_command->run();

        $this->_log->log(LogLevel::info, "'{$path}' folder created\n");
    }

    /**
     * Sets folder permissions for the folder at local path $path.
     * $permissions should be specified in octal (ex: 0777 instead of 777)
     */
    public function updateFolderPermission($path, $permission) {
        $ret = chmod($path,$permission);

        if ($ret === true) {
            $oct = decoct($permission);
            $this->_log->log(LogLevel::info, "Permissions set to {$oct} for: {$path}\n");
        } else {
            $this->_log->log(LogLevel::info, "Permissions could not be set for: {$path}\n");
        }
    }

    /**
     * Creates all of the folders listed in an array of local paths
     * passed in $requiredFolders
     */
    public function createRequiredFolders($requiredFolders) {
        if (is_array($requiredFolders) && count($requiredFolders) > 0) {
            foreach ($requiredFolders as $name => $path) {
                if (!$this->isFolder($path)) {
                    $this->createFolder($path);
                    $this->updateFolderPermission($path, self::FULL_PERM);
                }
            }
        }
    }
} 