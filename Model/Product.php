<?php
namespace SDBullion\Nfusions\Model;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionFactory;
class Product extends \Magento\Catalog\Model\Product {
	public function getPrice()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$cacheKey = 'custom_cache_tag';
		$cachedata = $objectManager->get('\Magento\PageCache\Model\Cache\Type')->load($cacheKey);
		if(!empty($cachedata)) {
			$unCacheData = unserialize($cachedata);
			foreach ($unCacheData as $key=>$value){
				// Ensure that you are updating only cache price otherwise leave it as default.
				if($this->getID() == $key) {
					$data = array();
					$new_data = json_decode(json_encode($value->RetailTiers), true);
					$prices = array_column($new_data, 'Quantity');
					$min_array = $new_data[array_search(min($prices), $prices)];
					if($min_array['Quantity'] < 10){
						return $min_array['Ask'];
					}	
				}
			}
		}
		if ($this->_calculatePrice || !$this->getData(self::PRICE)) {
            return $this->getPriceModel()->getPrice($this);
        } else {
            return $this->getData(self::PRICE);
        }
    }
	 public function getTierPrice($qty = null)
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$cacheKey = 'custom_cache_tag';
		$cachedata = $objectManager->get('\Magento\PageCache\Model\Cache\Type')->load($cacheKey);
		if(!empty($cachedata)) {
			$unCacheData = unserialize($cachedata);
			foreach ($unCacheData as $key=>$value){
				// Ensure that you are updating only cache price otherwise leave it as default.
				if($this->getID() == $key) {
					$data = array();
					foreach($value->RetailTiers as $key => $nval){
						$data[$key]["website_id"] 		= "1";
						$data[$key]["cust_group"] 		= '32000';
						$data[$key]["price_qty"]		= $nval->Quantity;
						$data[$key]["all_groups"]		= '1';
						$data[$key]["price"]			= $nval->Ask;
						$data[$key]["website_price"]	= $nval->Ask;
					}
					 $this->setData('tier_price',$data);
				}
			}
		}
       return $this->getPriceModel()->getTierPrice($qty, $this);
    }
}