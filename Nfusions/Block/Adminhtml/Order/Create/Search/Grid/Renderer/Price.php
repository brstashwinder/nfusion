<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SDbullion\Nfusions\Block\Adminhtml\Order\Create\Search\Grid\Renderer;

/**
 * Adminhtml sales create order product search grid price column renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Price extends \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Price
{
    public function render(\Magento\Framework\DataObject $row)
    {
		if ($row->getTypeId() == 'downloadable') {
            $row->setPrice($row->getPrice());
        }
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$cacheKey = 'custom_cache_tag';
		$cachedata = $objectManager->get('\Magento\PageCache\Model\Cache\Type')->load($cacheKey);
		if(!empty($cachedata)) {
			$unCacheData = unserialize($cachedata);
			foreach ($unCacheData as $key=>$value){
				if($row->getID() == $key) {
					$data = array();
					$new_data = json_decode(json_encode($value->RetailTiers), true);
					$prices = array_column($new_data, 'Quantity');
					$min_array = $new_data[array_search(min($prices), $prices)];
					
					if($min_array['Quantity'] < 10){
						$row->setPrice($min_array['Ask']);
					}	
				}
			}
		}
		$rendered = parent::render($row);
        $isConfigurable = true;
        $style = $isConfigurable ? '' : 'disabled';
        $prodAttributes = $isConfigurable ? sprintf(
            'list_type = "get_tier_price" product_id = %s',
            $row->getId()
        ) : 'disabled="disabled"';
        return sprintf(
            '<a href="javascript:void(0)" class="action-tier %s" %s>%s</a>',
            $style,
            $prodAttributes,
            __('Tier Price')
        ) . $rendered;
    }
}
