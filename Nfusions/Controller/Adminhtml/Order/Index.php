<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SDBullion\Nfusions\Controller\Adminhtml\Order;

class Index extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Ajax handler to response configuration fieldset of composite product in order
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('id');
        $Product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($productId);
		if($Product->getTierPrice()){
		echo '<div class="last-fieldset" id="catalog_product_composite_configure_fields_grouped">
				<h4>Product Tier Price</h4>
				<div class="product-options">
					<table id="super-product-table" class="data-table admin__table-primary grouped-items-table">
						<thead>
							<tr class="headings">
								<th class="col-id">Qty</th>
								<th class="col-sku">Price</th>
						</thead>
						<tbody>';
						foreach($Product->getTierPrice() as $price){
							echo '<tr class="even"><td class="col-id">'.(int)$price['price_qty'].'</td><td class="col-sku">'.(float)$price['price'].'</td></tr>';
						}
					   echo '</tbody>
					</table>
				</div>
			</div>';
		}
		else{
			echo "Tier Price not Exist...!";
		}
    }
}
