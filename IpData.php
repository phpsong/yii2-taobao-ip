<?php
/**
 * Created by PhpStorm.
 * User: dggug
 * Date: 2015/7/17
 * Time: 10:13
 */

namespace iit\api\taobao;


use yii\base\Model;

class IpData extends Model
{
    public $ip;
    public $country;
    public $area;
    public $region;
    public $city;
    public $county;
    public $isp;
    public $area_id;
    public $isp_id;
    public $county_id;
    public $city_id;
    public $region_id;
    public $country_id;
}