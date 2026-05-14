<?php
namespace Abbott\MetabolicOrdering\Model\Product\Attribute\Backend;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;

class File extends AbstractBackend
{
    /**
     * @var Filesystem\Driver\File
     */
    protected $file;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $fileUploaderFactory;

    /**
     * Construct
     *
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param Filesystem\Driver\File $file
     * @param UploaderFactory $fileUploaderFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface                         $logger,
        \Magento\Framework\Filesystem                    $filesystem,
        Filesystem\Driver\File                           $file,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->logger = $logger;
    }

/**
 * AfterSave function
 *
 * @param $object
 * @return $this|File
 * @throws FileSystemException
 */
    public function afterSave($object)
    {
        $path = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            'catalog/product/'
        );
        $delete = $object->getData($this->getAttribute()->getName() . '_delete');
        if ($delete) {
            $fileName = $object->getData($this->getAttribute()->getName());
            $object->setData($this->getAttribute()->getName(), '');
            $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
            if ($this->file->isExists($path.$fileName)) {
                $this->file->deleteFile($path.$fileName);
            }
        }
        if (empty($_FILES['product']['tmp_name'][$this->getAttribute()->getName()])) {
            return $this;
        }
        try {
            $uploader = $this->fileUploaderFactory->create(
                [
                    'fileId' => 'product['.$this->getAttribute()->getName().']'
                ]
            );
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($path);
            $object->setData($this->getAttribute()->getName(), $result['file']);
            $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
        } catch (\Exception $e) {
            if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                $this->logger->critical($e);
            }
        }
        return $this;
    }
}
