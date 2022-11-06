<?php

namespace Modules\Media\Conversions\ImageGenerators;

use Illuminate\Support\Collection;
use Modules\Media\Conversions\Conversion;

class Pdf extends ImageGenerator
{
    public function convert(string $file, Conversion $conversion = null): string
    {
        $imageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.jpg';

        $pageNumber = $conversion ? $conversion->getPdfPageNumber() : 1;

        (new \Omaicode\PdfToImage\Pdf($file))->setPage($pageNumber)->saveImage($imageFile);

        return $imageFile;
    }

    public function requirementsAreInstalled(): bool
    {
        if (! class_exists('Imagick')) {
            return false;
        }

        if (! class_exists('\\Omaicode\\PdfToImage\\Pdf')) {
            return false;
        }

        return true;
    }

    public function supportedExtensions(): Collection
    {
        return collect('pdf');
    }

    public function supportedMimeTypes(): Collection
    {
        return collect(['application/pdf']);
    }
}
