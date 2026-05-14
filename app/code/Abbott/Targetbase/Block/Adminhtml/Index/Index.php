<?php

namespace Abbott\Targetbase\Block\Adminhtml\Index;

class Index extends \Magento\Backend\Block\Widget\Container
{
    const PRIVATEKEY = 'targetbase_integration/targetbase/targetbase_pgp_private_key';
    const PASSPHRASE = 'targetbase_integration/targetbase/targetbase_pgp_passphrase';
    /**
     * @var \Magento\Framework\Shell
     */
    protected $shell;
    /**
     * @var \Abbott\Targetbase\Model\Exportdata
     */
    protected $exportdata;
    /**
     * @var \Abbott\Targetbase\Model\Exportorderdata
     */
    protected $exportorderdata;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptorInterface;
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;

    /**
     * Index constructor.
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Shell $shell
     * @param \Abbott\Targetbase\Model\Exportdata $exportdata
     * @param \Abbott\Targetbase\Model\Exportorderdata $exportorderdata
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface
     * @param \Magento\Framework\Filesystem\Driver\File $file
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Shell $shell,
        \Abbott\Targetbase\Model\Exportdata $exportdata,
        \Abbott\Targetbase\Model\Exportorderdata $exportorderdata,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface,
        \Magento\Framework\Filesystem\Driver\File $file
    ) {
        $this->shell = $shell;
        $this->exportdata = $exportdata;
        $this->exportorderdata = $exportorderdata;
        $this->_scopeConfig = $scopeConfig;
        $this->directoryList = $directoryList;
        $this->encryptorInterface = $encryptorInterface;
        $this->file = $file;
        parent::__construct($context);
    }

    /**
     * For decrypting Customer File
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function decryptCustomerFile()
    {
        $keyfilepath = $this->getPrivateKey();
        $passphraseEnc = $this->_scopeConfig->getValue(self::PASSPHRASE);
        $passphrase = ($passphraseEnc) ? $this->encryptorInterface->decrypt($passphraseEnc): null ;
        if ($keyfilepath) {
            putenv("GNUPGHOME=/tmp");
            $command = 'gpg --allow-secret-key-import --import ' . $keyfilepath;
            $this->shell->execute($command);
            $customerFile = $this->exportdata->getOldCustomerFilePath();
            $customerFileExist= $this->file->isExists($customerFile);
            if ($customerFileExist) {
                $command = ($passphrase)? "gpg --batch --yes --passphrase ".$passphrase." --decrypt ". $customerFile: "gpg --batch --yes --decrypt " . $customerFile;
                return $this->shell->execute($command);
            } else {
                return "There is no Customer File to decrypt";
            }
        } else {
            return "There is no private key available";
        }
    }
    /**
     * For decrypting Order File
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function decryptOrderFile()
    {
        $keyfilepath = $this->getPrivateKey();
        if ($keyfilepath) {
            $command = 'gpg --allow-secret-key-import --import ' . $keyfilepath;
            $this->shell->execute($command);
            $orderFile = $this->exportorderdata->getOldOrderFilePath();
            $orderFileExist= $this->file->isExists($orderFile);
            if ($orderFileExist) {
                $command = "gpg --batch --yes --decrypt " . $orderFile;
                return $this->shell->execute($command);
            } else {
                return "There is no Order File to decrypt";
            }
        } else {
            return "There is no private key available";
        }
    }
    /**
     * For getting private key
     *
     * @return string|null
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getPrivateKey()
    {
        $privateKeyData = $this->_scopeConfig->getValue(self::PRIVATEKEY);
        if ($privateKeyData) {
            $varPath = $this->directoryList->getPath('var') . '/';
            $keyfilepath = $varPath . 'Targetbase/' . 'targetbase_import-private.key';
            $fileExists = $this->file->isExists($keyfilepath);
            if ($fileExists==0) {
                $keyfile = $this->file->fileOpen($keyfilepath, "w");
                $this->file->fileWrite($keyfile, $privateKeyData);
                $this->file->fileClose($keyfile);
            }
        } else {
            return null;
        }
        return $keyfilepath;
    }
}
