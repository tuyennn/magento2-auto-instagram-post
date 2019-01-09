<?php

namespace GhoSter\AutoInstagramPost\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;


class TestConnection extends Action
{

    protected $resultJsonFactory;

    /**
     * @var \GhoSter\AutoInstagramPost\Model\Instagram
     */
    protected $_instagram;

    /**
     * @var \GhoSter\AutoInstagramPost\Helper\Data
     */
    protected $_instagramHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \GhoSter\AutoInstagramPost\Model\Instagram $instagram,
        \GhoSter\AutoInstagramPost\Helper\Data $instagramHelper,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_instagram = $instagram;
        $this->_instagramHelper = $instagramHelper;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * Collect relations data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $responseData = [];

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();

        $account = $this->_instagramHelper->getAccountInformation();

        $instagram = $this->getInstagram();

        try {

            if (!empty($account) && isset($account['username']) && isset($account['password'])) {
                $instagram->setUser($account['username'], $account['password']);
            } else {
                $responseData = [
                    'success' => false,
                    'message' => __('Missing Account Information, please check your configuration')
                ];

                return $result->setData($responseData);
            }

            if (!$instagram->login()) {
                $responseData = [
                    'success' => false,
                    'message' => __('Unauthorized Instagram Account, check your user/password settings')
                ];
                return $result->setData($responseData);
            } else {
                $responseData = [
                    'success' => true,
                    'message' => __('Connection Success')
                ];
            }

        } catch (\Exception $e) {
            $responseData['success'] = false;
            $responseData['message'] = $e->getMessage();
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }


        return $result->setData($responseData);
    }

    /**
     * Get Instagram Client
     *
     * @return \GhoSter\AutoInstagramPost\Model\Instagram
     */
    private function getInstagram()
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
        return $this->_authorization->isAllowed('GhoSter_AutoInstagramPost');
    }
}