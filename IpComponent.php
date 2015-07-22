<?php
/**
 * Created by PhpStorm.
 * User: dggug
 * Date: 2015/7/16
 * Time: 17:16
 */

namespace iit\api\taobao;

use Exception;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\caching\Cache;
use yii\di\Instance;

class IpComponent extends Component
{
    /**
     * 缓存组件，默认关闭缓存，需要打开缓存请传入缓存组件名，框架默认为`cache`
     * @var \yii\caching\Cache
     */
    public $cache;

    /**
     * 缓存时的前缀Key
     * @var string
     */
    public $cacheKey = 'taobao_ip_api';

    /**
     * 单个IP缓存有效期，默认为1天
     * @var int
     */
    public $cacheDuration = 86400;

    /**
     * 淘宝API地址
     * @var string
     */
    protected $_url = 'http://ip.taobao.com/service/getIpInfo.php?ip=';

    /**
     * 初始化组件，检查参数
     * @throws InvalidConfigException
     */

    public function init()
    {
        parent::init();
        if ($this->cache !== null) {
            $this->cache = Instance::ensure($this->cache, Cache::className());
        }
        if ($this->cache !== null && !$this->cache instanceof Cache) {
            throw new InvalidConfigException("cache must be extends \\yii\\caching\\Cache");
        }
    }

    /**
     * 通过API查询IP信息
     * @param null $ip
     * @return IpData|mixed
     */

    public function get($ip = null)
    {
        $ip = $ip === null ? Yii::$app->request->userIP : $ip;
        $ipData = new IpData();
        $cacheKey = $this->getCacheKey($ip);
        if ($this->cache !== null && $json = $this->cache->get($cacheKey)) {
            $ipData->setAttributes(json_decode($json, true), false);
            return $ipData;
        }
        $context = stream_context_create(['http' => ['timeout' => 1]]);
        $url = $this->_url . $ip;
        try {
            $result = json_decode(file_get_contents($url, 0, $context), true);
        } catch (Exception $e) {
            $result = null;
        }
        if ($result && $result['code'] == 0) {
            $ipData->setAttributes($result['data'], false);
            $this->cache !== null && $this->cache->set($cacheKey, json_encode($ipData), $this->cacheDuration);
        }
        return $ipData;
    }

    /**
     * 通过IP获取缓存的Key
     * @param $ip
     * @return string
     */

    protected function getCacheKey($ip)
    {
        return $cacheKey = $this->cacheKey . $ip;
    }
}