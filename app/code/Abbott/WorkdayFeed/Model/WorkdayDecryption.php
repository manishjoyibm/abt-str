<?php

namespace Abbott\WorkdayFeed\Model;

use Abbott\WorkdayFeed\Helper\InboundFeedHelper;
use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Shell;
use Psr\Log\LoggerInterface;

class WorkdayDecryption
{
    public $io;
    public $helper;
    /**
     * @var Shell
     */
    protected Shell $shell;

    /**
     * @var DirectoryList
     */
    protected DirectoryList $directoryList;
    /**
     * @var EncryptorInterface
     */
    protected EncryptorInterface $encryptorInterface;
    /**
     * @var File
     */
    protected File $file;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * WorkdayDecryption constructor.
     *
     * @param Shell $shell
     * @param DirectoryList $directoryList
     * @param EncryptorInterface $encryptorInterface
     * @param File $file
     * @param \Magento\Framework\Filesystem\Io\File $io
     * @param InboundFeedHelper $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Shell $shell,
        DirectoryList $directoryList,
        EncryptorInterface $encryptorInterface,
        File $file,
        \Magento\Framework\Filesystem\Io\File $io,
        InboundFeedHelper $helper,
        LoggerInterface $logger
    ) {
        $this->shell = $shell;
        $this->directoryList = $directoryList;
        $this->encryptorInterface = $encryptorInterface;
        $this->file = $file;
        $this->io = $io;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * For decrypting workday file
     *
     * @param InboundFeed $inboundFeed
     * @param string $workdayfilepath
     * @param int $compIndex
     * @return bool
     *
     * @throws FileSystemException
     * @throws LocalizedException|Exception
     */
    public function decryptWorkdayFile(InboundFeed $inboundFeed, string $workdayfilepath, int $compIndex): bool
    {
        try {
            $keyfilepath = $this->getPrivateKey($compIndex);
            $passphrase = $this->getPassphrase($compIndex, $this->helper);
            if ($keyfilepath) {
                putenv("GNUPGHOME=/tmp");
                $command = 'gpg --batch --yes --allow-secret-key-import --import ' . $keyfilepath;
                $this->shell->execute($command);
                $fileExists = $this->file->isExists($workdayfilepath);
                if ($fileExists) {
                    $filePath = $this->io->getPathInfo($workdayfilepath);
                    $filename = $filePath['filename'];
                    $targetFilePath = $this->directoryList->getPath('var') . InboundFeedHelper::FILE_PATH . $filename;
                    $command = ($passphrase)? "gpg --batch --yes --pinentry-mode=loopback --output " . $targetFilePath . " --passphrase ".$passphrase." --decrypt ". $workdayfilepath: "gpg --pinentry-mode=loopback --output " . $targetFilePath . " --decrypt " . $workdayfilepath;
                    try {
                        $this->shell->execute($command);
                    } catch (Exception $e) {
                        $this->logger->error("Workday Decrypt Error: " . $e->getMessage());
                    }
                    if ($this->file->isExists($targetFilePath)) {
                        $inboundFeed->setFileName($filename)->save();
                        return true;
                    } else {
                        $inboundFeed->setMessage("Unable to locate decrypted file")->save();
                    }
                } else {
                    $inboundFeed->setMessage("Unable to locate encrypted file")->save();
                }
            } else {
                $inboundFeed->setMessage("Private key not available")->save();
            }
        } catch (Exception $e) {
            $inboundFeed->setMessage("During decryption : ".$e->getMessage())->save();
        }
        return false;
    }

    /**
     * For getting private key
     *
     * @param int $compIndex
     *
     * @return string|null
     *
     * @throws FileSystemException
     */
    private function getPrivateKey(int $compIndex): ?string
    {
        $privateKeyData = $this->getCompanyPvtKey($compIndex, $this->helper);
        $keyfilepath = "";
        if ($privateKeyData) {
            $keyfilepath = $this->directoryList->getPath('var') .
                InboundFeedHelper::FILE_PATH . $this->getKeyFileName($compIndex);
            $keyfile = $this->file->fileOpen($keyfilepath, "w");
            $this->file->fileWrite($keyfile, $privateKeyData);
            $this->file->fileClose($keyfile);
        }
        return $keyfilepath;
    }

    /**
     * For getting passphrase specific to company
     *
     * @param int $compIndex
     * @param InboundFeedHelper $helper
     *
     * @return string|null
     */
    private function getPassphrase(int $compIndex, InboundFeedHelper $helper): ?string
    {
        switch ($compIndex) {
            case 0:
                $passphrase = $helper->getAbbottPassphrase();
                break;
            case 1:
                $passphrase = $helper->getAbbviePassphrase();
                break;
            case 2:
                $passphrase = $helper->getAlerePassphrase();
                break;
            default:
                $passphrase = "";
                break;
        }
        return $passphrase;
    }

    /**
     * For getting private key specific to company
     *
     * @param int $compIndex
     * @param InboundFeedHelper $helper
     *
     * @return string|null
     */
    private function getCompanyPvtKey(int $compIndex, InboundFeedHelper $helper): ?string
    {
        switch ($compIndex) {
            case 0:
                $pvtkey = $helper->getAbbottPvtKey();
                break;
            case 1:
                $pvtkey = $helper->getAbbviePvtKey();
                break;
            case 2:
                $pvtkey = $helper->getAlerePvtKey();
                break;
            default:
                $pvtkey = "";
                break;
        }
        return $pvtkey;
    }

    /**
     * For getting filename of private key specific to company
     *
     * @param int $compIndex
     *
     * @return string|null
     */
    public function getKeyFileName(int $compIndex): ?string
    {
        switch ($compIndex) {
            case 0:
                $filename = "workday_abbott_import-private.key";
                break;
            case 1:
                $filename = "workday_abbvie_import-private.key";
                break;
            case 2:
                $filename = "workday_alere_import-private.key";
                break;
            default:
                $filename = "";
                break;
        }
        return $filename;
    }
}
