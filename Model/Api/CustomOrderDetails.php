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

        $order_date = new \DateTime($order->getCreatedAt());

        $response['entity_id'] = $order->getEntityId();

        foreach($order->getAllItems() as $item) {

            $productDetail = $this->product->getById($item->getProductId());


            $response['items'][$i]['product_id'] = $item->getProductId();
            $response['items'][$i]['product_name'] = $item->getName();

            $store = $this->_storeManager->getStore();

            $response['items'][$i]['image_url'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' .$productDetail->getImage();
            
            $sprice = $this->specialPrice->get([$productDetail->getSku()]);
            $response['items'][$i]['sold_price'] = $item->getPrice();


            foreach($sprice as $special) {

                $fromdate = new \DateTime($special['price_from']);
                $todate = new \DateTime($special['price_to']);

                if($fromdate <= $order_date && $order_date <= $todate) {
                    $response['items'][$i]['special_price'] = (float) $special['value']; 
                }
            }
            
            
            $response['items'][$i]['product_type'] = $productDetail->getTypeId();
            $response['items'][$i]['sold_qty'] = (int) $item->getQtyOrdered();
            $response['items'][$i]['discount'] = (int) $item->getDiscountAmount();
            
            $i++;
        }


        $shippingaddress = $order->getShippingAddress();
        
        $shippingcity = $shippingaddress->getCity();
        $shippingstreet = $shippingaddress->getStreet();
        $shippingpostcode = $shippingaddress->getPostcode();
        $shippingtelephone = $shippingaddress->getTelephone(); 
        $shippingstate_code = $shippingaddress->getRegionCode();
        
        $response['payment_method'] = $order->getPayment()->getMethod();
        $response['shipping_address']['city'] = $shippingcity;
        $response['shipping_address']['street'] = $shippingstreet;
        $response['shipping_address']['post_code'] = $shippingpostcode;
        $response['shipping_address']['state_code'] = $shippingstate_code;

        $response['shipping_method'] = $order->getShippingDescription();
        $response['shipping_amount'] = (float) $order->getShippingAmount();

        $result = ['success' => 'Order Details', 'message' => $response];        
        return $result;
    }

    



    
}