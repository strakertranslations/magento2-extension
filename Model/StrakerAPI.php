<?php

namespace Straker\EasyTranslationPlatform\Model;

use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;

use Straker\EasyTranslationPlatform\Helper\ConfigHelper;

use Straker\EasyTranslationPlatform\Logger\Logger;

use Magento\Framework\HTTP\ZendClientFactory;

use Magento\Store\Model\StoreManagerInterface;

use Magento\Config\Model\ResourceModel\Config;

class StrakerAPI extends \Magento\Framework\Model\AbstractModel implements StrakerAPIInterface
{

    protected $_config;

    /**
     * Error codes recollected after each API call
     *
     * @var array
     */
    protected $_callErrors = [];

    protected $_storeId;

    protected $_logger;

    /**
     * Headers for each API call
     *
     * @var array
     */
    protected $_headers = [];

    protected $_options = [];

    public function __construct(
        ConfigHelper $configHelper,
        Config $configModel,
        ZendClientFactory $httpClient,
        Logger $logger,
        StoreManagerInterface $storeManagerInterface
    )
    {
        $this->_configHelper = $configHelper;
        $this->_configModel = $configModel;
        $this->_httpClient = $httpClient;
        $this->_logger = $logger;
        $this->_storeManager = $storeManagerInterface;
//        $this->_storeId = ($this->getStore()) ? $this->getStore() : 0;
//        $this->_init('strakertranslations_easytranslationplatform/api');
//        $this->_headers[] = 'Authorization: Bearer '. Mage::getStoreConfig('straker/general/access_token', $this->_storeId);
//        $this->_headers[] = 'X-Auth-App: '. Mage::getStoreConfig('straker/general/application_key', $this->_storeId);
    }

    protected  function _call($url, $method = 'get', array $request = array(), $raw = false)
    {

        $response = [];

        $retry = 0;

        $httpClient = $this->_httpClient->create();

        switch ($method) {

            case 'post':

                $method =  \Zend_Http_Client::POST;

                $httpClient->setParameterPost($request);

                break;

            case 'get' :

                $method =  \Zend_Http_Client::GET;

                break;
        }

        $httpClient->setUri($url);

        $httpClient->setConfig(['timeout' => 60,'verifypeer'=>0]);

        //$httpClient->setHeaders($this->_headers);

        $httpClient->setMethod($method);


        try {

            $response = $httpClient->request()->getBody();

            $debugData['response'] = $response;

            $this->_logger->debug('response',$debugData);

            return json_decode($response);

        } catch (Exception $e) {

            $debugData['http_error'] = array('error' => $e->getMessage(), 'code' => $e->getCode());

            $this->_logger->debug('error',$debugData);

            throw $e;
        }

        return $response;
    }

    protected function _buildQuery($request)
    {
        return http_build_query($request);
    }

    /**
     * Set array of additional cURL options
     *
     * @param array $options
     * @return Varien_Http_Adapter_Curl
     */
    public function setOptions(array $options = array())
    {
        $this->_options = $options;
        return $this;
    }

    protected  function _debug($debugData){

        // to be added

    }

    protected function _isCallSuccessful($response)
    {
        if (isset($response->code)) {
            return false;
        }

        if (isset($response->success) || isset($response->languages) || isset($response->country)) {
            return true;
        }
        return false;
    }

    /**
     * Handle logical errors
     *
     * @param array $response
     * @throws Mage_Core_Exception
     */
    protected function _handleCallErrors($response)
    {
        if(empty($response)){
            return;
        }
        if (isset($response->message) && strpos($response->message,'Authentication failed') !== false){
            $response->magentoMessage = $response->message;
        }
        return;
// to be added
    }

    protected  function _getRegisterUrl(){

        return $this->_configHelper->getConfig('/api_url/register');
    }

    protected  function _getLanguagesUrl(){
        return $this->_configHelper->getConfig('/api_url/languages');
    }

    protected  function _getCountiresUrl(){

        return $this->_configHelper->getConfig('/api_url/countries');
    }

    protected  function _getTranslateUrl(){
        return Mage::getStoreConfig('straker/api_url/translate');
    }

    protected  function _getQuoteUrl(){
        return Mage::getStoreConfig('straker/api_url/quote');
    }

    protected  function _getPaymentUrl(){
        return Mage::getStoreConfig('straker/api_url/payment');
    }

    protected  function _getSupportUrl(){
        return Mage::getStoreConfig('straker/api_url/support');
    }

    public function callRegister($data){

        return $this->_call($this->_getRegisterUrl(), 'post', $data);
    }

    public function callTranslate($data){
        $this->_headers[] = 'Content-Type:multipart/form-data';
        return $this->_call($this->_getTranslateUrl(), 'post', $data);
    }

    public function callSupport($data){
        return $this->_call($this->_getSupportUrl(), 'post', $data);
    }

    public function getQuote($data){
        return $this->_call($this->_getQuoteUrl().'?'. $this->_buildQuery($data));
    }

    public function getPayment($data){
        return $this->_call($this->_getPaymentUrl().'?'. $this->_buildQuery($data));
    }

    public function getTranslation($data){
        return $this->_call($this->_getTranslateUrl().'?'. $this->_buildQuery($data));
    }

    public function getTranslatedFile($downloadUrl){
        return $this->_call($downloadUrl,'get',array(),true);
    }

    public function getCountries(){

        $result = $this->_call($this->_getCountiresUrl());

        return $result->country;
    }

    public function getLanguages(){
        $result = $this->_call($this->_getLanguagesUrl());
        return $result->languages ? $result->languages : false;
    }

    public function saveAppKey($appKey){

        return $this->_configModel->SaveConfig('straker/general/application_key',$appKey,'default',$this->_storeManager->getWebsite()->getId());
    }

    public function saveAccessToken($accessToken){

        return $this->_configModel->SaveConfig('straker/general/access_token',$accessToken,'default',$this->_storeManager->getWebsite()->getId());
    }
}