<?php
namespace SDBullion\Nfusions\Controller\Pricing;
use Magento\Framework\App\Action\Context;
class Update extends \Magento\Framework\App\Action\Action
{
	protected $resultJsonFactory;
	protected $customerSession;
	protected $cacheType;
	protected $cart;
	protected $resultRedirect;
	public $_storeManager;
	public $ScopeConfigInterface;
	
	public function __construct(Context $context,\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,\Magento\Customer\Model\Session $customerSession,\Magento\PageCache\Model\Cache\Type $cacheType,\Magento\Checkout\Model\Cart $cart,\Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\App\Config\ScopeConfigInterface $ScopeConfigInterface) {
        $this->resultJsonFactory = $resultJsonFactory;
		$this->_cart = $cart;
		$this->_storeManager = $storeManager;
		$this->ScopeConfigInterface = $ScopeConfigInterface;
		$this->customerSession = $customerSession;
		$this->cacheType = $cacheType;
		 $this->resultRedirect = $context->getResultFactory();;
        parent::__construct($context);

    }
    public function execute()
    {	
		$locktime = (int)$this->ScopeConfigInterface->getValue('sdbullion_nfusions/nfusions_field/locktime_field');
        $result = $this->resultJsonFactory->create();
		$save_data = $this->customerSession->getSaved_price();
		if(!empty($save_data) && $save_data['end_time']  > time() ){
			$saved_price['end_time'] = $save_data['end_time'];
			$saved_price['now_time'] = time();
		}
		else{
			$cacheKey = 'custom_cache_tag';
			$cachedata = $this->cacheType->load($cacheKey);
			$unCacheData = unserialize($cachedata);
			$productInfo = $this->_cart->getQuote()->getItemsCollection();
			if(count($productInfo) > 0){
				foreach ($productInfo as $item){
				 $item_id = $item->getProductId();
					$cartData[$item->getId()]['qty'] = $item->getQty();
				}
				$this->_cart->updateItems($cartData)->save();
				$saved_price['end_time'] = time()+ $locktime;
				$saved_price['now_time'] = time();
				$saved_price['saved_cahche'] = $cachedata;
				$this->customerSession->setSaved_price($saved_price);
			}
		}
		$this->customerSession->getMyValue();
		if ($this->getRequest()->isAjax()) 
        {
	        $test=Array
            (
                'end' => $saved_price['end_time'].'000',
                'now' => $saved_price['now_time'].'000'
            );
            return $result->setData($test);
        }
		else{
			  $resultRedirect = $this->resultRedirect->create( $this->resultRedirect::TYPE_REDIRECT);
			  $resultRedirect->setUrl($this->_storeManager->getStore()->getBaseUrl().'checkout/cart/');
			  return $resultRedirect;  
		}
    }
}