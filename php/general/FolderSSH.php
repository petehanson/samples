<?php

namespace uarsoftware\deploy\helpers;
use uarsoftware\deploy\action as action;
use uarsoftware\deploy\enum\logLevel as LogLevel;

/**
 * This class handles creation and permission handling of remote folders.
 */
class FolderSSH extends abstractFolder
{
    
    /**
     * Returns true if a folder exists at the remote path specified by $folder
     */
    public function isFolder($folder) {
        $this->_command->setCommand('if test -d "'.$folder.'"; then echo 1; fi');
        $ret = $this->_command->run();

        if ($ret) {
            return true;
        }

        return false;
    }

    /**
     * Creates a folder at the remote path $path
     */
    public function createFolder($path) {
        $this->_command->setCommand("mkdir -p {$path}");
        $this->_command->run();

        $this->_log->log(LogLevel::info, "'{$path}' folder created\n");
    }

    /**
     * Sets folder permissions for the folder at remote path $path.
     * $permissions should be specified in octal (ex: 0777 instead of 777)
     */
    public function updateFolderPermission($path, $permission) {
        $oct = decoct($permission);

        $this->_executor->write("chmod ".$oct." ".$path." \n");
        $this->_log->log(LogLevel::info, "Permissions set to {$oct} for: {$path}\n");
    }

    /**
     * Creates all of the folders listed in an array of remote paths
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