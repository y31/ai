<?php
/**
 * Created by PhpStorm.
 * User: hahaxixi2017
 * Date: 2018/8/13
 * Time: 12:26
 */

namespace AI\Baidu\ImageSearch;

use AI\Baidu\Client;
use AI\Common\Exceptions\InvalidArgumentException;

class ImageSearch
{
    protected $client;
    /**
     * @var: 路径
     */
    protected $endPoint;
    /**
     * @var string
     */
    protected $versionUrl = 'image-classify/';
    /**
     * @var : data的参数
     */
    protected $params;

    public function __construct($app)
    {
        $this->client = new Client($app);
    }

    /**
     *  author:HAHAXIXI
     *  created_at: 2018/8/13
     *  updated_at: 2018/8/13
     * @return array|bool
     * @throws  InvalidArgumentException
     *  desc   :    格式检查
     */
    protected function validate()
    {
        // 输入图片签名就不用检查image
        if (isset($this->params['cont_sign'])) {
            return true;
        }
        $imageInfo = self::getImageInfo($this->params['image']);

        //图片格式检查
        if (!in_array($imageInfo['mime'], array('image/jpeg', 'image/png', 'image/bmp'))) {
            throw new InvalidArgumentException('unsupported image format');//SDK109
        }

        //图片长宽检查
        if ($imageInfo['width'] < 15 || $imageInfo['width'] > 4096 || $imageInfo['height'] < 15 || $imageInfo['height'] > 4096) {
            throw new InvalidArgumentException('image length error');//SDK101
        }

        $this->params['image'] = base64_encode($this->params['image']);

        //编码后小于4m
        if (strlen($this->params['image']) >= 4 * 1024 * 1024) {
            throw new InvalidArgumentException('image size error');//SDK100
        }
        return true;
    }

    /**
     * 获取图片信息
     * @param  $content string
     * @return array
     */
    public static function getImageInfo($content)
    {
        $info = getimagesizefromstring($content);
        return [
            'mime' => $info['mime'],
            'width' => $info[0],
            'height' => $info[1],
        ];
    }

    /**
     *  author:HAHAXIXI
     *  created_at: 2017-12-7
     *  updated_at: 2017-12-
     *  desc   :
     */
    public function get()
    {
        $this->validate();
        return $this->client->post($this->endPoint, $this->params);
    }

    /**
     *  author:HAHAXIXI
     *  created_at: 2018-8-13
     *  updated_at: 2018-8-
     * @param string $param
     * @throws  InvalidArgumentException
     * @return $this
     *  desc   :    指定调用的接口
     */
    public function select($param = 'realtime_search/same_hq/add')
    {
        $allowOcrType = require __DIR__ . '/SupportType.php';
        if (!in_array($param, $allowOcrType)) {
            throw new InvalidArgumentException('invalid argument:' . $param);
        }
        if (strpos($param, '/same_hq/') !== false) {
            $this->endPoint = $param;//相同图搜索
        } else {
            $this->endPoint = $this->versionUrl . $param;
        }
        return $this;
    }

    /**
     *  author:HAHAXIXI
     *  created_at: 2017-12-8
     *  updated_at: 2017-12-
     * @param $condition
     * @return $this
     *  desc   :    请求的参数，一般包含 image
     */
    public function where($condition)
    {
        $this->params = $condition;
        return $this;
    }
}