<?php

namespace Culqi\Pago\Controller\Payment;

use Magento\Framework\Controller\ResultFactory;

#[\AllowDynamicProperties]
class Success extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $checkoutSession;
    protected $resultRedirect;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Controller\ResultFactory $result
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->resultRedirect = $result;
        $this->url = $url;
        parent::__construct($context);
    }

    public function execute()
    {
        $page = $this->resultPageFactory->create();
        $block = $page->getLayout()->getBlock('payment.success');

        if ($this->checkoutSession->getSuccess()) {
            $this->checkoutSession->unsSuccess();
            return $page;
        } else {
            $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->url->getUrl('checkout/cart/'));
            return $resultRedirect;
        }
    }
}
