<?php

namespace uarsoftware\deploy\helpers;
use uarsoftware\deploy\action as action;
use uarsoftware\deploy\enum\logLevel as LogLevel;

class Folder extends abstractFolder
{
    public function isFolder($folder) {
        if (!file_exists($folder) OR !is_dir($folder)) {
            return false;
        }

        return true;
    }

    public function createFolder($path) {
        $this->_command->setCommand("mkdir -p {$path}");
        $this->_command->run();

        $this->_log->log(LogLevel::info, "'{$path}' folder created\n");
    }

    public function updateFolderPermission($path, $permission) {
        $ret = chmod($path,$permission);

        if ($ret === true) {
            $oct = decoct($permission);
            $this->_log->log(LogLevel::info, "Permissions set to {$oct} for: {$path}\n");
        } else {
            $this->_log->log(LogLevel::info, "Permissions could not be set for: {$path}\n");
        }
    }

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