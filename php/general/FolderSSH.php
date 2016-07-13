<?php

namespace uarsoftware\deploy\helpers;
use uarsoftware\deploy\action as action;
use uarsoftware\deploy\enum\logLevel as LogLevel;

class FolderSSH extends abstractFolder
{
    private $_sshPrompt = 'lucas.campanella@ip-10-116-150-205:~$';

    public function isFolder($folder) {
        $this->_command->setCommand('if test -d "'.$folder.'"; then echo 1; fi');
        $ret = $this->_command->run();

        if ($ret) {
            return true;
        }

        return false;
    }

    public function createFolder($path) {
        $this->_command->setCommand("mkdir -p {$path}");
        $this->_command->run();

        $this->_log->log(LogLevel::info, "'{$path}' folder created\n");
    }

    public function updateFolderPermission($path, $permission) {
        $oct = decoct($permission);

        $this->_executor->read($this->_sshPrompt);
        $this->_executor->write("chmod ".$oct." ".$path." \n");
        $this->_executor->read($this->_sshPrompt);

        $this->_log->log(LogLevel::info, "Permissions set to {$oct} for: {$path}\n");
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