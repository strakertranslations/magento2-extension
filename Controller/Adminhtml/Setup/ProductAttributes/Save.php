<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Setup\ProductAttributes;

use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;
use Straker\EasyTranslationPlatform\Api\Data\SetupInterface;
use Straker\EasyTranslationPlatform\Model\Error;
use Straker\EasyTranslationPlatform\Logger\Logger;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Backend\App\Action\Context;


class Save extends \Magento\Backend\App\Action
{

    protected $_config;
    protected $_reinitConfig;
    protected $_strakerAPI;
    protected $_setup;
    protected $_errorManager;
    protected $_logger;

    public function __construct(
        Context $context,
        Config $config,
        ReinitableConfigInterface $reinitableConfigInterface,
        StrakerAPIInterface $strakerAPIInterface,
        SetupInterface $setupInterface,
        Error $error,
        Logger $logger
    )
    {
        $this->_config = $config;
        $this->_reinitConfig = $reinitableConfigInterface;
        $this->_strakerAPI = $strakerAPIInterface;
        $this->_setup = $setupInterface;
        $this->_errorManager = $error;
        $this->_logger = $logger;

        parent::__construct($context);
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Straker_EasyTranslationPlatform::save');
    }


    public function execute()
    {

        $data = $this->getRequest()->getParams();

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {

            try {

                $attributes = $this->sortData($data);

                $this->_setup->saveProductAttributes($attributes);

                if($this->_errorManager->_error){

                    $this->_getSession()->setFormData($data);

                    $resultRedirect->setPath('/*/index/');

                    $this->messageManager->addError($this->_errorManager->getErrorMessage());

                }else{

                    $resultRedirect->setPath('*/Jobs/new');

                }

                return $resultRedirect;


            } catch (\Magento\Framework\Exception\LocalizedException $e) {

                $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));

                $this->messageManager->addError($e->getMessage());

            } catch (\RuntimeException $e) {

                $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));

                $this->messageManager->addError($e->getMessage());

            } catch (\Exception $e) {

                $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));

                $this->messageManager->addException($e, __('Something went wrong while saving the product attributes.'));
            }

            $resultRedirect->setPath('/*/index/');

        }

        return $resultRedirect;
    }

    protected function sortData($data)
    {
        $attributes = [];

        if(!empty($data['custom'])){

            asort($data['custom']);

            $attributes['custom'] = implode(",", $data['custom']);
        }

        if(!empty($data['default'])){

            asort($data['default']);

            $attributes['default'] = implode(",", $data['default']);

        }

        return $attributes;
    }
}