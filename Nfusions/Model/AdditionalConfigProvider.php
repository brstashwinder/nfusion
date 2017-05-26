<?php
namespace SDBullion\Nfusions\Model;
use Magento\Framework\App\Config\ScopeConfigInterface;
class AdditionalConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
	public $_storeManager;
	public $ScopeConfigInterface;
	public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager,ScopeConfigInterface $ScopeConfigInterface) {
		 $this->_storeManager = $storeManager;
		 $this->ScopeConfigInterface = $ScopeConfigInterface;
	}
   public function getConfig()
   {
		$output['url'] = $this->_storeManager->getStore()->getBaseUrl().'sdbullion/pricing/update/';
        return $output;
   }
}