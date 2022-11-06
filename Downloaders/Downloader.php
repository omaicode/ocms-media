<?php

namespace Modules\Media\Downloaders;

interface Downloader
{
    public function getTempFile(string $url): string;
}
