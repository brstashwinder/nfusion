<?php
namespace SDBullion\Nfusions\Controller\Pricing;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;
class Index extends \Magento\Framework\App\Action\Action
{
	protected $indexerFactory;
	protected $cacheConfig;
	protected $cacheType;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Indexer\IndexerInterfaceFactory $indexerFactory,
		\Magento\framework\Cache\Config $cacheConfig,
		\Magento\PageCache\Model\Cache\Type $cacheType)
    {
		$this->indexerFactory = $indexerFactory;
		$this->_cacheConfig = $cacheConfig;
		$this->_cacheType = $cacheType;
        return parent::__construct($context);
    }
     
    public function execute()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$objectCurrency = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
		//$currencySymbol = $objectCurrency->getStore()->getBaseCurrencyCode();
		$enabledField = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('sdbullion_nfusions/nfusions_field/enabled_field');
		$token = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('sdbullion_nfusions/nfusions_field/token_field');
		$locktime = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('sdbullion_nfusions/nfusions_field/locktime_field');
		$cacheLifeField = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('sdbullion_nfusions/nfusions_field/cachelife_field');
		//Check if script is run by hosted server itself
		//if ($_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR']) {
		//	die("cannot run!");
		//}
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$selectPriceAttId = "select attribute_id from eav_attribute where attribute_code = 'price'";
		$rowP = $connection->fetchRow($selectPriceAttId);
		$selectSpecialPriceAttId = "select attribute_id from eav_attribute where attribute_code = 'special_price'";
		$rowSP = $connection->fetchRow($selectSpecialPriceAttId);
		$attribute_id_price = $rowP['attribute_id'];
		$attribute_id_sp_price = $rowSP['attribute_id'];
		$time_start = microtime(true);
		$client = new Client(['verify' => false ]);
		echo "<pre>";
		$cacheKey = 'custom_cache_tag';
		$cachedata = $this->_cacheType->load($cacheKey);
		$data = unserialize($cachedata);
		/* foreach($data[1]->RetailTiers as $tiers){
			var_dump($tiers);
		} */
		$new_data =  json_decode(json_encode($data[1]->RetailTiers), true);
		$prices = array_column($new_data, 'Quantity');
		/* $min_array = $new_data[array_search(min($prices), $prices)];
		$max_array = $new_data[array_search(max($prices), $prices)]; */
		var_dump($prices,$new_data);
		die;
		$res = $client->request('GET', 'https://silverdoctors.nfusioncatalog.com/service/price/all?currency=CAD&withretailtiers=true&withwholesaletiers=true&withCost=true&token=&shippingInAsk=true',array('Accept: application/json', 'Content-Type: application/json','Cache-Control: no-cache','Pragma: no-cache'));
		//Decode the API response
		$rateData = json_decode($res->getBody());
		if(count($rateData) > 0){
			$proCount = array();
			$enitity_id = '';
			$cachedarr=array();
			$cacheKey = 'custom_cache_tag';
			$cachedata = $this->_cacheType->load($cacheKey);
			$unCacheData[] = unserialize($cachedata);
			var_dump($unCacheData);
			die;
			foreach($rateData as $k=>$v){
				/*********** Check if sku exist or not and then retrieve entity_id by sku STARTS ************/
				$productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
				$productcheck = $objectManager->get('Magento\Catalog\Model\Product');
				if($productcheck->getIdBySku($v->SKU)) {
				$product = $productRepository->get($v->SKU);
					$rowArray = $product->getData();
					if(!empty($rowArray) and !array_key_exists($rowArray['entity_id'], $unCacheData)){
						$cEntityid = $rowArray['entity_id'];
						$cPrice = $v->Ask;
						$cachedarr[$cEntityid] = $cPrice;
					}
				}
				/*********** Check if sku exist or not and then retrieve entity_id by sku ENDS ************/
				if($v->Ask != ''){
					$select = "Select * FROM catalog_product_entity WHERE sku='".$v->SKU."'";
					$rowArray = $connection->fetchRow($select);
					$enitity_id = $rowArray['entity_id'];
				}			
				if($enitity_id != '') {
					$proCount[$enitity_id] = $enitity_id;
					//Update product price
					$sql = "Update catalog_product_entity_decimal Set value = '".$v->Ask."' where entity_id = $enitity_id AND attribute_id in ($attribute_id_price,$attribute_id_sp_price)";
					$connection->query($sql);
					//Check tier exist or not 
					if(isset($v->RetailTiers)){
						//delete all tier price 
						$sql = "Delete FROM catalog_product_entity_tier_price Where entity_id = $enitity_id";
						$connection->query($sql);
						//Reset tier price 		
						foreach($v->RetailTiers as $tier_k => $tier_v){								
							//insert tier price 
							$sql = "Insert Into catalog_product_entity_tier_price (value_id, entity_id, all_groups, customer_group_id, qty, value, website_id) Values ('', $enitity_id,'1', '0', $tier_v->Quantity, $tier_v->Ask, 0)";
							$connection->query($sql);
							
						}
					}   
				}
			}
			$this->indexerFactory->create()->load('catalog_product_price')->reindexAll();
			/************* Save price in cache STARTS **************/
			if(!empty($cachedarr)){
				$cacheKey = 'custom_cache_tag';
				$this->_cacheType->save(serialize($cachedarr), $cacheKey, [\Magento\PageCache\Model\Cache\Type::CACHE_TAG], 86400);
				$cachedata = $this->_cacheType->load($cacheKey);
				$unCacheData = unserialize($cachedata);
				print_r($unCacheData);
			}
			/************* Save price in cache ENDS **************/
		}
		$time_end = microtime(true);
		$execution_time = ($time_end - $time_start); 
		echo '<br/><b>Total Execution Time:</b> '.$execution_time.' Sec. for <b>'.count($proCount).'</b> products.';
    } 
}