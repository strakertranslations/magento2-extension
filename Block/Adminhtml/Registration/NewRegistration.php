<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Registration;

class NewRegistration extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    protected $_mode = 'newRegistration';

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry
    )
    {
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * Initialize blog post edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'post_id';
        $this->_blockGroup = 'Straker_EasyTranslationPlatform';
        $this->_controller = 'adminhtml_registration';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Register'));

        $this->buttonList->remove('reset');
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

}