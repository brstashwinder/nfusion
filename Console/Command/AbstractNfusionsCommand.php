<?php
namespace SDBullion\Nfusions\Console\Command;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
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
abstract class AbstractNfusionsCommand extends Command
{
    private $objectManagerFactory;
    private $objectManager;
	protected $indexerFactory;
	protected $cacheConfig;
	protected $cacheType;
	protected $ScopeConfigInterface;
	protected $_eavAttribute;
	protected $token;
	protected $locktime;
	protected $cacheLifeField;
	protected $enabledField;
    public function __construct(ObjectManagerFactory $objectManagerFactory,\Magento\framework\Cache\Config $cacheConfig,
		\Magento\PageCache\Model\Cache\Type $cacheType,ScopeConfigInterface $ScopeConfigInterface,\Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute)
    {
        $this->objectManagerFactory = $objectManagerFactory;
		$this->_cacheConfig = $cacheConfig;
		$this->_cacheType = $cacheType;
		$this->ScopeConfigInterface = $ScopeConfigInterface;
		$this->_eavAttribute = $eavAttribute;
		$this->token = $this->ScopeConfigInterface->getValue('sdbullion_nfusions/nfusions_field/token_field');
		$this->tenant_alias = $this->ScopeConfigInterface->getValue('sdbullion_nfusions/nfusions_field/tenant_alias');
		$this->locktime = $this->ScopeConfigInterface->getValue('sdbullion_nfusions/nfusions_field/locktime_field');
		$this->cacheLifeField = $this->ScopeConfigInterface->getValue('sdbullion_nfusions/nfusions_field/cachelife_field');
		$this->enabledField = $this->ScopeConfigInterface->getValue('sdbullion_nfusions/nfusions_field/enabled_field');
        parent::__construct();
    }
    protected function getObjectManager()
    {
        if (null == $this->objectManager) {
            $area = FrontNameResolver::AREA_CODE;
            $this->objectManager = $this->objectManagerFactory->create($_SERVER);
            /** @var \Magento\Framework\App\State $appState */
            $appState = $this->objectManager->get('Magento\Framework\App\State');
            $appState->setAreaCode($area);
            $configLoader = $this->objectManager->get('Magento\Framework\ObjectManager\ConfigLoaderInterface');
            $this->objectManager->configure($configLoader->load($area));
        }
        return $this->objectManager;
    }
	protected function get_response(){
		 $client = new Client(['verify' => false ]);
		 $res = $client->request('GET', 'https://'.$this->tenant_alias.'.nfusioncatalog.com/service/price/all?currency=CAD&withretailtiers=true&withwholesaletiers=true&withCost=true&token='.$this->token.'&shippingInAsk=true',array('Accept: application/json', 'Content-Type: application/json','Cache-Control: no-cache','Pragma: no-cache'));
		return $rateData = json_decode($res->getBody());
	}
}
