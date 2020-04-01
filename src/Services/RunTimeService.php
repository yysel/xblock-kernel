<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 20-3-25
 * Time: 下午6:17
 */

namespace XBlock\Kernel\Services;


class RunTimeService
{
    static public function openProvider($provider)
    {
        $ser = new $provider(app());
        $ser->boot();
        $ser->register();
    }


    static public function client(String $type = null)
    {
        $client_type = request()->header('client_type');
        if ($type) return $type === $client_type;
        return $client_type;
    }

    /** 取出或者判断传入值与前端调用的路径是否一致
     * @param string $path
     * @return bool | string
     */
    static public function location(String $path = null)
    {
        $location = request()->header('location');
        if ($path) {
            $path = '/' . str_replace('/', "\/", trim($path, '/')) . "\/" . '/';
            return (bool)preg_match($path, '/' . trim($location) . '/');
        }
        return $location;
    }
}