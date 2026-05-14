<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Api;

use Amasty\Orderattr\Api\Data\FileContentInterface;

interface UploadFileInterface
{
    /**
     * @param FileContentInterface $fileContent
     * @return mixed
     */
    public function upload(FileContentInterface $fileContent): string;
}
