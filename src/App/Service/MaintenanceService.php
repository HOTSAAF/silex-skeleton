<?php

namespace App\Service;

class MaintenanceService
{
    private $rootPath;
    private $triggerFilePath;

    public function __construct($rootPath)
    {
        $this->rootPath = rtrim($rootPath, '/');
        $this->triggerFilePath = $this->rootPath . '/maintenance.on';
        $this->normalizeFilePath();
    }

    public function isMaintenanceMode()
    {
        return file_exists($this->triggerFilePath);
    }

    private function normalizeFilePath()
    {
        if (is_link($this->triggerFilePath)) {
            $this->triggerFilePath = readlink($this->triggerFilePath);
            // If the link is a relative link, then prepend it with the root
            // path.
            if ($this->triggerFilePath[0] !== '/') {
                $this->triggerFilePath = $this->rootPath . '/' . $this->triggerFilePath;
            }
        }
    }
}
