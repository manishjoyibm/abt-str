<?php

declare(strict_types=1);

namespace Abbott\AbbottReport\Model\Queue;

use Abbott\AbbottReport\Api\Data\AbbottExportInfoInterface;
use Magenest\Salesforce\Model\Queue;

class Gratis
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    public $_logger;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    public $_json;
    /**
     * @var \Abbott\AbbottReport\Model\Export\Gratis
     */
    public $exportGratis;
    protected $logger;

    protected $json;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Abbott\AbbottReport\Model\Export\Gratis $exportGratis
    ) {
        $this->_logger = $logger;
        $this->_json = $json;
        $this->exportGratis = $exportGratis;
    }

    /**
     *
     * @param string $data
     * @return string $fileName
     */
    public function exportReport(AbbottExportInfoInterface $exportInfo)
    {
        try {
            $data['to_gratis'] = $exportInfo->getToGratis();
            $data['from_gratis'] = $exportInfo->getFromGratis();
            $data['store_id'] = $exportInfo->getStoreId();
            if ($data['from_gratis'] != "" && $data['to_gratis'] != "") {
                return $this->exportGratis->exportGratisData($data);
            }
        } catch (\Exception $e) {
            $this->_logger->error("GRATS ERROR = " . $e);
        }
    }
}
