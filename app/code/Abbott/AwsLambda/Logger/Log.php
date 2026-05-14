<?php

namespace Abbott\AwsLambda\Logger;

use Abbott\AwsLambda\Helper\Data;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Class Log for AWS Lambda API related information (request, response, etc.) which is used for debug.
 */
class Log
{
    /**
     * @var Data
     */
    protected $helper;
    
    /**
     * @var DirectoryList
     */
    protected $dirList;

    /**
     * @var File
     */
    protected $file;

    /**
     * Constructor
     *
     * @param Data          $helper
     * @param DirectoryList $dirList
     * @param File          $file
     */
    public function __construct(
        Data $helper,
        DirectoryList $dirList,
        File $file
    ) {
        $this->helper = $helper;
        $this->dirList = $dirList;
        $this->file = $file;
    }
    
    /**
     * Logs Aws Lambda Api related information used for debug
     *
     * @param String $file
     * @param String $message
     */
    public function writeLog($message)
    {
        if ($this->helper->enabledDebug()) {
            $path = $this->dirList->getPath('var') . '/log/aws-lambda.log';
            $this->file->filePutContents($path, $message . "\r\n", FILE_APPEND);
        }
    }

    /**
     * @param integer $storeId
     * @return void
     */
    public function setScope($storeId)
    {
        $this->helper->setStoreId($storeId);
    }
}
