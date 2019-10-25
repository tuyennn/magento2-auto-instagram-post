<?php

namespace GhoSter\AutoInstagramPost\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use GhoSter\AutoInstagramPost\Model\Instagram;
use GhoSter\AutoInstagramPost\Model\Config as InstagramConfig;
use Psr\Log\LoggerInterface;

/**
 * Class TestConnection
 * @package GhoSter\AutoInstagramPost\Controller\Adminhtml\System\Config
 */
class TestConnection extends Action
{

    protected $resultJsonFactory;

    /**
     * @var Instagram
     */
    protected $_instagram;

    /** @var InstagramConfig */
    protected $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $account = [];

    /**
     * TestConnection constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Instagram $instagram
     * @param InstagramConfig $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Instagram $instagram,
        InstagramConfig $config,
        LoggerInterface $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_instagram = $instagram;
        $this->config = $config;
        $this->logger = $logger;
        $this->account = $this->config->getAccountInformation();
        parent::__construct($context);
    }

    /**
     * Collect relations data
     *
     * @return Json
     */
    public function execute()
    {
        $responseData = [];

        /** @var Json $result */
        $result = $this->resultJsonFactory->create();

        try {

            if (!empty($this->account)
                && isset($this->account['username'])
                && isset($this->account['password'])) {

                $this->getInstagram()
                    ->setUser(
                        $this->account['username'],
                        $this->account['password']
                    );

                if ($this->getInstagram()->isLoggedIn()) {
                    $this->getInstagram()->logout();
                }

                $status = $this->getInstagram()->login();

                $responseData = [
                    'success' => $status,
                    'message' => $status ? __('Connection Success') : __('Unauthorized Instagram Account, check your user/password settings')
                ];

            } else {
                $responseData = [
                    'success' => false,
                    'message' => __('Missing Account Information, please check your configuration')
                ];

                return $result->setData($responseData);
            }

        } catch (\Exception $e) {
            $responseData['success'] = false;
            $responseData['message'] = $e->getMessage();
            $this->logger->critical($e);
        }

        return $result->setData($responseData);
    }

    /**
     * @return Instagram
     */
    public function getInstagram(): Instagram
    {
        return $this->_instagram;
    }

    /**
     * Is the user allowed to view the blog post grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('GhoSter_AutoInstagramPost::auto_instagram_post');
    }
}
