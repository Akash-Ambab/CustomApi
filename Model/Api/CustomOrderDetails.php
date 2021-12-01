<?php
namespace Ambab\CustomApi\Model\Api;

use Psr\Log\LoggerInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Api\SpecialPriceInterface;
use Magento\Store\Model\StoreManagerInterface;

class CustomOrderDetails
{
    protected $logger;
    protected $orderObj;
    protected $product;
    private $specialPrice;
    protected $_storeManager;
    
    public function __construct ( 
        LoggerInterface $logger, 
        OrderRepository $orderObj,
        ProductRepository $product,
        SpecialPriceInterface $specialPrice,
        StoreManagerInterface $storemanager
    )
    {
        $this->logger = $logger;
        $this->orderObj = $orderObj;
        $this->product = $product;
        $this->specialPrice = $specialPrice;
        $this->_storeManager =  $storemanager;
    }

    public function getOrderDetails($id) {
        // $response = ['success' => false];

        $response = [];
        $i = 0;
        $orderId = $id;
        $order = $this->orderObj->get($orderId);

        $store = $this->_storeManager->getStore();

        $order_date = new \DateTime($order->getCreatedAt());

        $response['entity_id'] = $order->getEntityId();



        foreach($order->getAllItems() as $item) {

            $productDetail = $this->product->getById($item->getProductId());


            $response['items'][$i]['product_id'] = $item->getProductId();
            $response['items'][$i]['product_name'] = $item->getName();

            $response['items'][$i]['image_url'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' .$productDetail->getImage();
            
            $response['items'][$i]['sold_price'] = $item->getPrice();
            $response['items'][$i]['product_price'] = $item->getOriginalPrice();

            if ($item->getPrice() < $item->getOriginalPrice()) {
                $response['items'][$i]['special_price'] = $item->getPrice();
            }
            
            $response['items'][$i]['product_type'] = $productDetail->getTypeId();
            $response['items'][$i]['sold_qty'] = (int) $item->getQtyOrdered();
            $response['items'][$i]['discount'] = $item->getDiscountAmount();
            
            $i++;
        }

        $response['order_value']['subtotal'] = $order->getSubtotal();
        $response['order_value']['shipping_amount'] = $order->getShippingAmount();

        $response['order_value']['discount'] = abs($order->getDiscountAmount());
        $response['order_value']['grand_total'] = $order->getGrandTotal();


        $shippingaddress = $order->getShippingAddress();
        
        $shippingcity = $shippingaddress->getCity();
        $shippingstreet = $shippingaddress->getStreet();
        $shippingpostcode = $shippingaddress->getPostcode();
        $shippingtelephone = $shippingaddress->getTelephone(); 
        $shippingstate_code = $shippingaddress->getRegionCode();
        $shippingcountry = $shippingaddress->getCountryId();

        $fullname = $order->getCustomerFirstname() . " " . $order->getCustomerLastname();

        $response['customer_info']['id'] = $order->getCustomerId();
        $response['customer_info']['name'] = $fullname;
                
        $response['payment_method'] = $order->getPayment()->getMethod();
        $response['shipping_address']['street'] = $shippingstreet;
        $response['shipping_address']['city'] = $shippingcity;
        $response['shipping_address']['post_code'] = $shippingpostcode;
        $response['shipping_address']['state_code'] = $shippingstate_code;
        $response['shipping_address']['country'] = $shippingcountry;

        $response['shipping_method'] = $order->getShippingDescription();

        $result = ['success' => 'Order Details', 'message' => $response];        
        return $result;
    }

    


    
    
}