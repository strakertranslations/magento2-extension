<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Product;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as BackendHelperData;
use Magento\Framework\View\Element\Template;
use Straker\EasyTranslationPlatform\Model;
use Straker\EasyTranslationPlatform\Model\JobFactory;

class Grid extends Extended
{
    protected $_jobFactory;
    /** @var \Straker\EasyTranslationPlatform\Model\Job $_job */
    protected $_job;
    protected $_entityId;
    protected $_jobTypeId = Model\JobType::JOB_TYPE_ATTRIBUTE;


    public function __construct(
        Context $context,
        BackendHelperData $backendHelper,
        JobFactory $jobFactory,
        array $data = []
    ) {
        $this->_jobFactory = $jobFactory;
        parent::__construct($context,$backendHelper, $data);
    }

    public function _construct()
    {
        $requestData = $this->getRequest()->getParams();
        $jobId = $requestData['job_id'];
        $this->_job = $this->_jobFactory->create()->load( $jobId );
        parent::_construct();
    }

    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
        $productCollection = $this->_job->getProductCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('price');
        $this->setCollection($productCollection);
        return parent::_prepareCollection();
    }


    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_product',
            [
                'type' => 'checkbox',
                'name' => 'in_product',
                'align' => 'center',
                'index' => 'entity_id'
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('Product ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('Sku'),
                'index' => 'sku',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'index' => 'price',
                'width' => '50px',
            ]
        );

        $this->addColumn(
            'view',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('View'),
                        'url' => [
                            'base' => '*/*/ViewJob',
                            'params' => [
                                'job_id' => $this->_job->getJobId(),
                                'job_type_id' => $this->_jobTypeId,
                                'job_type_referrer' => Model\JobType::JOB_TYPE_PRODUCT
                            ]
                        ],
                        'field' => 'entity_id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'view',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );
        return parent::_prepareColumns();
    }


    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/*/ViewJob',
            [
                'job_id' => $this->_job->getJobId(),
                'job_type_id' => $this->_jobTypeId,
                'entity_id' => $row->getEntityId(),
                'job_type_referrer' => Model\JobType::JOB_TYPE_PRODUCT
            ]
        );
    }
}
