<?php
namespace SDBullion\Nfusions\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
 
class CheckShoppingCartObserver implements ObserverInterface {

	protected $request;
	protected $cacheType;
	protected $catalogSession;
	
	protected $_urlInterface;
	
	public function __construct(
		\Magento\Framework\App\Request\Http $request,
		\Magento\PageCache\Model\Cache\Type $cacheType,
		\Magento\Checkout\Model\Cart $cart,
		\Magento\Catalog\Model\Session $catalogSession,
		\Magento\Framework\UrlInterface $urlInterface
	) {
		$this->_request = $request;
		$this->_cacheType = $cacheType;
		$this->_cart = $cart;
		$this->_catalogSession = $catalogSession;
		
        $this->_urlInterface = $urlInterface;
	}
 
	/**
	 * This is the method that fires when the event runs. 
	 * 
	 * @param Observer $observer
	 */
	public function execute( \Magento\Framework\Event\Observer $observer ) {
		/************** Update cart price with cache price ***************/

		$pageWasRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';
		if($pageWasRefreshed){
			$this->_catalogSession->unsMyValue();
		?>
			<script>
				var urlWithoutHash = document.location.href.replace(location.hash , "" );
				window.location = urlWithoutHash;
			</script>
		<?php
		}
		//echo $proId = (int)$this->_request->getParam('product', 0);die;
		$cacheKey = 'custom_cache_tag';
		$cachedata = $this->_cacheType->load($cacheKey);
		if(!empty($cachedata)) {
			$unserializeCacheData = unserialize($cachedata);
			foreach ($unserializeCacheData as $key=>$value){
				// Check if current product has cache
				//if($proId == $key) {							
					$productInfo = $this->_cart->getQuote()->getAllVisibleItems();
					foreach ($productInfo as $item){
						 //$item_id = $item->getProductId();
						// Ensure that only current added product gets updated with cache price,
						// Not all item products should be updated
						//if($proId == $item_id){
							$this->_catalogSession->setMyValue($value);
							$item->setCustomPrice($this->_catalogSession->getMyValue());
							$item->setOriginalCustomPrice($this->_catalogSession->getMyValue());
							$item->save();
						//}
					}
				//}
			}
			//return $this;
			
		} else {
			//return $this;
		}
		
	}
}