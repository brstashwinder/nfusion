<?php
namespace SDBullion\Nfusions\Block\Adminhtml\Order\Create\Search;
class Grid extends \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid
{
	protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'index' => 'entity_id'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Product'),
                'renderer' => 'Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Product',
                'index' => 'name'
            ]
        );
        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku']);
        $this->addColumn(
            'price',
            [	'sortable' => false,
                'header' => __('Price <a class="refesh_tier" href="javascript:void(0)" onclick="sales_order_create_search_gridJsObject.doFilter()"> Refresh</a>'),
                'column_css_class' => 'price',
                'type' => 'currency',
                'currency_code' => $this->getStore()->getCurrentCurrencyCode(),
                'rate' => $this->getStore()->getBaseCurrency()->getRate($this->getStore()->getCurrentCurrencyCode()),
                'index' => 'price',
                'renderer' => 'Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Price'
            ]
        );

        $this->addColumn(
            'in_products',
            [
                'header' => __('Select'),
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $this->_getSelectedProducts(),
                'index' => 'entity_id',
                'sortable' => false,
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select'
            ]
        );

        $this->addColumn(
            'qty',
            [
                'filter' => false,
                'sortable' => false,
                'header' => __('Quantity'),
                'renderer' => 'Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Qty',
                'name' => 'qty',
                'inline_css' => 'qty',
                'type' => 'input',
                'validate_class' => 'validate-number',
                'index' => 'qty'
            ]
        );

        //return parent::_prepareColumns();
    }
}