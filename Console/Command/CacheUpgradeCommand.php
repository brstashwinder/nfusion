<?php
namespace SDBullion\Nfusions\Console\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
class CacheUpgradeCommand extends AbstractNfusionsCommand
{

   protected function configure()
   {
       $this->setName('nfusions:cache_upgrade');
       $this->setDescription('Create and Update Nfusions Price Cache ');
   }
   protected function execute(InputInterface $input, OutputInterface $output)
   {
		$output->writeln("===>Start cron functionality...");
		$attribute_id_price = $this->_eavAttribute->getIdByCode('catalog_product', 'price');
		$attribute_id_sp_price = $this->_eavAttribute->getIdByCode('catalog_product', 'special_price');
		$time_start = microtime(true);
		$output->writeln("Geting response from APi.....");
		$rateData = $this->get_response();
		$output->writeln("response received from APi.....");
		if(count($rateData) > 0){
			$proCount = array();
			$cachedarr=array();
			$cacheKey = 'custom_cache_tag';
			$cachedata = $this->_cacheType->load($cacheKey);
			$unCacheData[] = unserialize($cachedata);
			foreach($rateData as $k=>$v){
				$productRepository = $this->getObjectManager()->get('\Magento\Catalog\Model\ProductRepository');
				$productcheck = $this->getObjectManager()->get('Magento\Catalog\Model\Product');
				if($PID = $productcheck->getIdBySku($v->SKU) && !empty($v->Ask)) {
					$output->writeln($v->SKU."s11123 .....");
					$v->Ask = '90';
					$cachedarr[$PID] = $v;
					$proCount[$PID] = $PID;
				}
			}
			$ids = array_keys($this->getObjectManager()->get('Magento\Framework\Indexer\ConfigInterface')->getIndexers());
			foreach ($ids as $id) {
				$output->writeln($id);
				$this->getObjectManager()->get('Magento\Framework\Indexer\IndexerInterfaceFactory')->create()->load($id)->reindexAll();
			}
			$output->writeln("sadasdasd.....");
			if(!empty($cachedarr)){
				$this->_cacheType->save(serialize($cachedarr), $cacheKey, [\Magento\PageCache\Model\Cache\Type::CACHE_TAG], 86400);
			}
		}
		$time_end = microtime(true);
		$output->writeln('Total Execution Time: '.($time_end - $time_start).' Sec. for '.count($proCount).' products.');
    }
}