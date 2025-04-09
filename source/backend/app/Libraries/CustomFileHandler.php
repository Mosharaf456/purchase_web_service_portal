<?php

namespace App\Libraries;

use CodeIgniter\Log\Handlers\FileHandler;

class CustomFileHandler extends FileHandler
{
    protected string $customPath = '';
    protected string $customFilename = '';

    public function setPath(string $path)
    {
        $this->customPath = rtrim($path, '/') . '/';
    }

    public function setFilename(string $filename)
    {
        $this->customFilename = $filename;
    }

    protected function determineFile(): string
    {
        $path = $this->customPath ?: $this->path;
        $filename = $this->customFilename ?: 'log-' . date('Y-m-d') . '.log';

        return $path . $filename;
    }
}
