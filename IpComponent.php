<?php
/**
 * Created by PhpStorm.
 * User: dggug
 * Date: 2015/7/16
 * Time: 17:16
 */

namespace api\taobao;

use Exception;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\caching\Cache;

class IpComponent extends Component
{
    public $enableCache = true;
    public $cache;
    public $cacheKey = 'taobao_ip_api';
    public $cacheDuration = 86400;
    /**
     * @var \yii\caching\Cache
     */
    private $_cache;
    private $_url = 'http://ip.taobao.com/service/getIpInfo.php?ip=';

    public function init()
    {
        parent::init();
        if ($this->cache === null) {
            $this->_cache = Yii::$app->cache;
        } else {
            $this->_cache = Yii::$app->get($this->cache);
        }
        if (!$this->_cache instanceof Cache) {
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
        $cacheKey = $this->cacheKey . $ip;
        $ipData = new IpData();
        if ($this->enableCache && $json = $this->_cache->get($cacheKey)) {
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
            $this->enableCache && $this->_cache->set($cacheKey, json_encode($ipData), $this->cacheDuration);
        }
        return $ipData;
    }
}