<?php
namespace SDBullion\Nfusions\Block\Checkout;
class Countdown extends \Magento\Framework\View\Element\Template {
	public $_storeManager;
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		array $data = []
	) {
		 $this->_storeManager = $context->getStoreManager();
		parent::__construct($context, $data);
	}
	public function get_update_url(){
		return  $this->_storeManager->getStore()->getBaseUrl().'sdbullion/pricing/update/';
	}
}