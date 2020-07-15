<?php
namespace Culqi\Pago\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;

class OrderPlaceAfter implements ObserverInterface
{
    private $url;
    protected $response;
    protected $order;
    protected $checkoutSession;
    protected $helper;
    
    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Sales\Model\Order $order,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Culqi\Pago\Helper\Data $helper
    ) {
        $this->response = $response;
        $this->url = $url;
        $this->order = $order;
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $fieldTitle = $this->helper->getConfig('payment/culqi/title');
        $orderId = $observer->getEvent()->getOrderIds();

        $orders = $this->order->load($orderId);
        $payment = $orders->getPayment();
        $method = $payment->getMethodInstance();
        $methodTitle = $method->getTitle();

        // onlyForCulqi
        if ($methodTitle == $fieldTitle) {
               
            $ordernid=$orders->getRealOrderId();
           
            $items =$orders->getAllItems();
            //product Names
            $productNames = [];
            foreach ($items as $item) {
                $productNames[]= $item->getName();
            }
            
            if (count($productNames)>1) {
                $description = $ordernid;
            } else {
                $description = $productNames[0];
            }

            $total=$orders->getGrandTotal();

            $currency_code = $orders->getOrderCurrencyCode();

            $custFirstName= $orders->getCustomerFirstname();
            $custLastName= $orders->getCustomerLastname();
            $customer_email= $orders->getCustomerEmail();

            $storeName = $orders->getStoreName();

            $billingaddress=$orders->getBillingAddress();
            $billingcity=$billingaddress->getCity();
            $billingstreet=$billingaddress->getStreet();
            $billingtelephone=$billingaddress->getTelephone();

            $activeMultiPay = $this->helper->getConfig('payment/culqi/active_multipayment');

            // Set our variables in order to create Order and Charge in Culqi
            $this->checkoutSession->setAmount($total);
            $this->checkoutSession->setCurrencyCode($currency_code);
            $this->checkoutSession->setDescription($description);
            $this->checkoutSession->setStoreName($storeName);
            $this->checkoutSession->setFirstName($custFirstName);
            $this->checkoutSession->setLastName($custLastName);
            $this->checkoutSession->setPhoneNumber($billingtelephone);
            $this->checkoutSession->setEmail($customer_email);
            $this->checkoutSession->setOrderId($ordernid);
            $this->checkoutSession->setBillingCity($billingcity);
            $this->checkoutSession->setBillingStreet($billingstreet);
            $this->checkoutSession->setCountryCode('PE');
            $this->checkoutSession->setActiveMultiPay($activeMultiPay);
            
            $customRedirectionUrl = $this->url->getUrl('pago/payment/redirect');
            $this->response->setRedirect($customRedirectionUrl)->sendResponse();
        }
    }
}
