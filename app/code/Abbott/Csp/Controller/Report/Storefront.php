<?php
namespace Abbott\Csp\Controller\Report;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Abbott\Csp\Model\ReportFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Storefront extends Action implements HttpPostActionInterface
{

    private const XML_PATH_REPORT_COLLECTION_ENABLED = 'abbott_csp/general/report_collection';

    protected $jsonFactory;
    protected $reportFactory;
    protected $scopeConfig;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        ReportFactory $reportFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->reportFactory = $reportFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        if (!$this->getConfigValue(self::XML_PATH_REPORT_COLLECTION_ENABLED)) {
            return $result->setData([
                'success' => false, 
                'error' => 'Report collection configuration is disabled'
            ]);
        }
        
        $data = json_decode($this->getRequest()->getContent(), true);

        if (is_array($data) && isset($data['violatedDirective'])) {
            try {
                $log = $this->reportFactory->create();
                $log->setData([
                    'violated_directive' => $data['violatedDirective'],
                    'blocked_uri' => $data['blockedUri'] ?? null,
                    'document_uri' => $data['documentUri'] ?? null,
                    'source_file' => $data['sourceFile'] ?? null,
                    'line_number' => $data['lineNumber'] ?? null,
                    'column_number' => $data['columnNumber'] ?? null
                ]);
                $log->save();

                return $result->setData(['success' => true]);
            } catch (\Exception $e) {
                return $result->setData(['success' => false, 'error' => $e->getMessage()]);
            }
        }

        return $result->setData(['success' => false, 'error' => 'Invalid data']);
    }

    /**
     * Get Config Value
     *
     * @param string $configPath
     * @return mixed
     */
    private function getConfigValue(string $configPath): mixed
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
