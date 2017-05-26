<?php
namespace SDBullion\Nfusions\Plugin;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;

class Product
{
	protected $cacheType;
	protected $request;
	protected $cart;
	protected $customerSession;
	protected $catalogSession;
    public function __construct(
		\Magento\Framework\App\Request\Http $request,
		\Magento\Checkout\Model\Cart $cart,
		\Magento\PageCache\Model\Cache\Type $cacheType,
		\Magento\Checkout\Model\Session $catalogSession,
		\Magento\Customer\Model\Session $customerSession)
    {
		$this->request = $request; 
		$this->customerSession = $customerSession;		
		$this->_cart = $cart;
		$this->_cacheType = $cacheType;
		$this->_catalogSession = $catalogSession;
    }
	/*********** Update price all over the site but rembember it will not update the price in quote ***********/
    public function afterGetPrice(\Magento\Catalog\Api\Data\ProductInterface $subject, $result)
    {
		$proId = $subject->getId();
		$save_data = $this->customerSession->getSaved_price();
		if(!empty($save_data) && $save_data['end_time']  > time() ){
			$cachedata = $save_data['saved_cahche'];
		}
		else{
			$cacheKey = 'custom_cache_tag';
			$cachedata = $this->_cacheType->load($cacheKey);
		}
		if(!empty($cachedata)) {
			$unCacheData = unserialize($cachedata);
			foreach ($unCacheData as $key=>$value){
				// Ensure that you are updating only cache price otherwise leave it as default.
				if($proId == $key) {
					$data = array();
					foreach($value->RetailTiers as $key => $nval){
						$data[$key]["website_id"] 		= "1";
						$data[$key]["cust_group"] 		= '32000';
						$data[$key]["price_qty"]		= $nval->Quantity;
						$data[$key]["price"]			= $nval->Ask;
						$data[$key]["website_price"]	= $nval->Ask;
					}
					$subject->setTier_price($data);
					$new_data = json_decode(json_encode($value->RetailTiers), true);
					$prices = array_column($new_data, 'Quantity');
					$min_array = $new_data[array_search(min($prices), $prices)];
					if($min_array['Quantity'] < 10){
						return $min_array['Ask'];
					}else{
						return $result;
					}	
				}else{
					return $result;
				}
			}
		} else{
			return $result;
		}
    }
}