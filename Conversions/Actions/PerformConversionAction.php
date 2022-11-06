<?php

namespace Modules\Media\Conversions\Actions;

use Modules\Media\Conversions\Conversion;
use Modules\Media\Conversions\Events\ConversionHasBeenCompleted;
use Modules\Media\Conversions\Events\ConversionWillStart;
use Modules\Media\Conversions\ImageGenerators\ImageGeneratorFactory;
use Modules\Media\MediaCollections\Filesystem;
use Modules\Media\MediaCollections\Models\Media;
use Modules\Media\ResponsiveImages\ResponsiveImageGenerator;

class PerformConversionAction
{
    public function execute(
        Conversion $conversion,
        Media $media,
        string $copiedOriginalFile
    ) {
        event(new ConversionWillStart($media, $conversion, $copiedOriginalFile));

        $imageGenerator = ImageGeneratorFactory::forMedia($media);

        $copiedOriginalFile = $imageGenerator->convert($copiedOriginalFile, $conversion);

        $manipulationResult = (new PerformManipulationsAction())->execute($media, $conversion, $copiedOriginalFile);

        $newFileName = $conversion->getConversionFile($media);

        $renamedFile = $this->renameInLocalDirectory($manipulationResult, $newFileName);

        if ($conversion->shouldGenerateResponsiveImages()) {
            /** @var ResponsiveImageGenerator $responsiveImageGenerator */
            $responsiveImageGenerator = app(ResponsiveImageGenerator::class);

            $responsiveImageGenerator->generateResponsiveImagesForConversion(
                $media,
                $conversion,
                $renamedFile
            );
        }

        app(Filesystem::class)->copyToMediaLibrary($renamedFile, $media, 'conversions');

        $media->markAsConversionGenerated($conversion->getName());

        event(new ConversionHasBeenCompleted($media, $conversion));
    }

    protected function renameInLocalDirectory(
        string $fileNameWithDirectory,
        string $newFileNameWithoutDirectory
    ): string {
        $targetFile = pathinfo($fileNameWithDirectory, PATHINFO_DIRNAME).'/'.$newFileNameWithoutDirectory;

        rename($fileNameWithDirectory, $targetFile);

        return $targetFile;
    }
}
