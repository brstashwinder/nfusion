<?php
namespace SDBullion\Nfusions\Console\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
class SaveProductCommand extends AbstractNfusionsCommand
{
   protected function configure()
   {
       $this->setName('nfusions:save_product');
       $this->setDescription('Save Nfusions Price as base price');
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
			foreach($rateData as $k=>$v){
				$productRepository = $this->getObjectManager()->get('\Magento\Catalog\Model\ProductRepository');
				$productcheck = $this->getObjectManager()->get('Magento\Catalog\Model\Product');
				if($PID = $productcheck->getIdBySku($v->SKU) && !empty($v->Ask)) {
					$output->writeln($v->SKU."s11123 .....");
					$cPrice = $v->Ask;
					$tierPrices = array();
					$tierPriceFactory = $this->getObjectManager()->get('\Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory');
					foreach($v->RetailTiers as $tier_k => $tier_v){								
							$output->writeln($tier_v->Quantity."Quantity .....");
							$output->writeln($tier_v->Ask."Ask .....");
							$tierPrices[] =  array('all_groups'=> '1','cust_group' => '32000','price_qty' =>$tier_v->Quantity,'price' => $tier_v->Ask, 'website_id' => '0');
						}
					$new_data = json_decode(json_encode($v->RetailTiers), true);
					$prices = array_column($new_data, 'Quantity');
					$min_array = $new_data[array_search(min($prices), $prices)];
					$product = $productcheck->load($PID);
					if($min_array['Quantity'] < 10){
						$product->setPrice($min_array['Ask']);
					}					
					$product->setTierPrice($tierPrices);
					$product->save();
					$proCount[$PID] = $PID;
				}
			}
			$this->getObjectManager()->get('Magento\Framework\Indexer\IndexerInterfaceFactory')->create()->load('catalog_product_price')->reindexAll();
			$output->writeln("sadasdasd.....");
		}
		$time_end = microtime(true);
		$output->writeln('Total Execution Time: '.($time_end - $time_start).' Sec. for '.count($proCount).' products.');
    }
}