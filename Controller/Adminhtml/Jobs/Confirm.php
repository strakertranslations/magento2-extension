<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Jobs;

use Magento\Backend\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;

use Straker\EasyTranslationPlatform\Block\Adminhtml\Job;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Helper\ImportHelper;
use Straker\EasyTranslationPlatform\Model\JobFactory;
use Straker\EasyTranslationPlatform\Logger\Logger;
use Straker\EasyTranslationPlatform\Model\JobStatus;

class Confirm extends \Magento\Backend\App\Action
{

    protected $_jobFactory;
    protected $_logger;
    protected $_storeManager;
    protected $_configHelper;
    protected $_importHelper;

    public function __construct(
        Context $context,
        ConfigHelper $configHelper,
        ImportHelper $importHelper,
        JobFactory $jobFactory,
        Logger $logger,
        StoreManagerInterface $storeManager
    ) {
        $this->_jobFactory = $jobFactory;
        $this->_configHelper = $configHelper;
        $this->_importHelper = $importHelper;
        $this->_logger = $logger;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $job_id = $this->getRequest()->getParam('job_id');

        $resultRedirect = $this->resultRedirectFactory->create();

        $job = $this->_jobFactory->create()->load($job_id);

        $jobMatches = [];

        preg_match("/job_(.*?)_/", $job->getSourceFile(), $jobMatches);

        $jobIds = explode('&', $jobMatches[1]);

        foreach ($jobIds as $job_id) {
            try {
                //should get sub job instance
//                $jobType = $this->_jobFactory->create()->load($job_id)->getJobType();
                $currentJob = $this->_jobFactory->create()->load($job_id);
                $jobType = $currentJob->getJobType();

                $this->_importHelper->create($job_id)->publishTranslatedData();

                //should set status for sub job instance
//                $job->addData(['job_status_id'=>6]);
//                $job->save();
                $currentJob->addData(['job_status_id' => JobStatus::JOB_STATUS_CONFIRMED]);
                $currentJob->save();

                $this->messageManager->addSuccess('Translated '.$jobType.' data has been published for '.$this->_storeManager->getStore($job->getData('target_store_id'))->getName().' store');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());

                $this->_logger->error('error'.__FILE__.' '.__LINE__, [$e]);

                $this->messageManager->addError('Translated data has not been published for '.$this->_storeManager->getStore($job->getData('target_store_id'))->getName().' store');

                $resultRedirect->setPath('*/*/index');

                return $resultRedirect;
            }
        }

        $resultRedirect->setPath('*/*/index');

        return $resultRedirect;
    }
}
