<?php

namespace Abbott\Sarp2\Cron;

use Aheadworks\Sarp2\Engine\EngineInterface;

class ProcessPayments extends \Aheadworks\Sarp2\Cron\ProcessPayments
{
    public $scopeConfig;
    public $logger;
    public $paymentsList;
    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * \Aheadworks\Sarp2\Engine\Payment\PaymentsList $paymentsList
     * @param EngineInterface $engine
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Aheadworks\Sarp2\Engine\Payment\PaymentsList $paymentsList,
        EngineInterface $engine,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->engine = $engine;
        $this->logger = $logger;
        $this->paymentsList = $paymentsList;
    }

    /**
     * Perform processing of pending payments
     *
     * @return void
     */
    public function execute()
    {
      $this->logger->info('Entered Payment Cron');
      if (!$this->scopeConfig->getValue('aw_sarp2/aw_crons1/pp_enabled',\Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
          return;
      }
      $this->logger->info('Controlled Cron execution starts');
      if ($this->scopeConfig->getValue('aw_sarp2/aw_crons_controlled/control_enabled',\Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
          $idArr = $this->scopeConfig->getValue('aw_sarp2/aw_crons_controlled/profile_ids',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
          $profileIds = explode(",",$idArr);
          if(!empty($profileIds)) {
            $this->processProfiles($profileIds);
          }
          return;
      }
      $this->logger->info('Regular Payment Cron starts');
      $this->engine->processPaymentsForToday();
    }

    /**
     * @param  array $profileIds
     */
    function processProfiles($profileIds) {
        $paymentIds = [];
        foreach ($profileIds as $profileId) {
          $payments = $this->paymentsList->getLastScheduled($profileId);
          foreach ($payments as $payment) {
              $paymentIds[] = $payment->getItemId();
          }
        }
        if(!empty($paymentIds)) {
            $this->engine->processPaymentsForToday($paymentIds);
        }
    }
}
