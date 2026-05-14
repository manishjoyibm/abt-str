<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\File;

use Amasty\Orderattr\Api\Data\FileContentInterface;
use Amasty\Orderattr\Api\UploadFileInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;

class Uploader implements UploadFileInterface
{
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var string
     */
    private $basePath;

    public function __construct(
        Filesystem $filesystem,
        string $basePath = 'amasty_checkout'
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $this->basePath = $basePath;
    }

    public function upload(FileContentInterface $fileContent): string
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (!($content = base64_decode($fileContent->getBase64EncodedData()))) {
            throw new LocalizedException(__('Base64 Decode File Error'));
        }

        $name = $fileContent->getNameWithExtension();
        $dispersionPath = \Magento\Framework\File\Uploader::getDispersionPath($name);
        $filePath = $this->basePath . $dispersionPath;
        $absolutePath = $this->mediaDirectory->getAbsolutePath($filePath);

        if (!$this->mediaDirectory->isExist($filePath)) {
            $this->mediaDirectory->create($filePath);
        }

        $filePath .= DIRECTORY_SEPARATOR . $name;
        if ($this->mediaDirectory->isExist($filePath)) {
            $name = \Magento\Framework\File\Uploader::getNewFileName(
                $this->mediaDirectory->getAbsolutePath($absolutePath . DIRECTORY_SEPARATOR . $name)
            );
        }

        $this->mediaDirectory->getDriver()->filePutContents(
            $absolutePath . DIRECTORY_SEPARATOR . $name,
            $content
        );

        return $dispersionPath . DIRECTORY_SEPARATOR . $name;
    }
}
