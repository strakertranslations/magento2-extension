<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Settings;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Straker\EasyTranslationPlatform\Api\Data\SetupInterface;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Logger\Logger;

class DeleteTestStoreView extends Action
{
    protected $_resultJsonFactory;
    protected $_strakerSetup;
    protected $_logger;
    protected $_configHelper;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        SetupInterface $strakerSetup,
        Logger $logger,
        ConfigHelper $configHelper
    ) {
        $this->_strakerSetup = $strakerSetup;
        $this->_logger = $logger;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_configHelper = $configHelper;
        return parent::__construct($context);
    }


    public function execute()
    {
        $result = ['Success' => true];
        $params = $this->getRequest()->getParams();
        $siteMode = $params['siteMode'];
        try{
            $result = $this->_strakerSetup->deleteTestingStoreView($siteMode);
            if($result['Success']){
                $this->messageManager->addSuccessMessage(__('Test store view deleted successfully.'));
                if($result['SiteMode'] == SetupInterface::SITE_MODE_LIVE ){
                    $this->messageManager->addSuccessMessage(__('Live mode enabled.'));
                }else{
                    $this->messageManager->addSuccessMessage(__('Sandbox mode enabled'));
                }
            }else{
                $this->messageManager->addWarningMessage($result['Message']);
            }
        } catch (Exception $e) {
            $result['Success'] = false;
            $message = __($e->getMessage());
            $result['Message'] = $message;
            $this->_logger->error($message);
        }
        $jsonResult = $this->_resultJsonFactory->create();
        return $jsonResult->setData($result);
    }
}
