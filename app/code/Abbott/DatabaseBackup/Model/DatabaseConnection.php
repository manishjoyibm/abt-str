<?php

namespace Abbott\DatabaseBackup\Model;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;

class DatabaseConnection
{
    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * DatabaseConnection constructor.
     *
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(DeploymentConfig $deploymentConfig)
    {
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Get Database name
     *
     * @return array|mixed|string|null
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function getDatabaseName(): mixed
    {
        return $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT
            . '/' . ConfigOptionsListConstants::KEY_NAME);
    }

    /**
     * Get User Name
     *
     * @return array|mixed|string|null
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function getUserName()
    {
        return $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT
            . '/' . ConfigOptionsListConstants::KEY_USER);
    }

    /**
     * Get Password
     *
     * @return array|mixed|string|null
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function getPassword()
    {
        return $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT
            . '/' . ConfigOptionsListConstants::KEY_PASSWORD);
    }

   /**
    * Get Host Name
    *
    * @return array|mixed|string|null
    * @throws FileSystemException
    * @throws RuntimeException
    */
    public function getHostName()
    {
        return $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT
            . '/' . ConfigOptionsListConstants::KEY_HOST);
    }

    /**
     * Get Port
     *
     * @return array|mixed|string|null
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function getPort()
    {
        return $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT
            . '/' . ConfigOptionsListConstants::KEY_PORT);
    }
}
