<?php

/**
 * Created by Mrhoang.
 * User: mrhoang
 * Date: 9/24/2016
 * Time: 9:41 AM
 */
class Firewall
{
    public static function detect()
    {
        //$ip =   isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
        $ip = session_id();
        if ($ip!='') {
            $ip = str_replace('.','',$ip);
            $Memcached = new \Memcached();
            $Memcached->addServer('localhost', 11211);
            $request_time = $Memcached->get('request_time'.$ip);
            $request_count = intval($Memcached->get('request_count'.$ip));
            $curenttime = microtime(1)*1000 ;
            if ($curenttime - $request_time < 1000) {
                $Memcached->set('request_time'.$ip, $curenttime, time() + 600);
                $request_count = $request_count + 1;
                $Memcached->set('request_count'.$ip, $request_count, time() + 60);
            } else {
                $request_count = 0;
                $Memcached->set('request_count'.$ip, 0, time() + 60);
                $Memcached->set('request_time'.$ip, $curenttime, time() + 60);
            }
            if ($request_count > 5) {
                echo "<html><head><title>Thông báo</title></head><body><h1 style='text-align: center' >Bạn truy cập quá nhanh</h1><br><div style='text-align: center' ><a  href='/' >Click để tiếp tục</a></div></body></html>";
                self::addIp();
                exit();
            }
        }
    }

    public  function addIp()
    {
        $content = file_get_contents('../storage/ips.json');
        if (isset($_SERVER['REMOTE_ADDR'])){

            $filedetect = (array)json_decode($content);
            $ips = isset($filedetect['ips'])?$filedetect['ips']:array();

            if (!key_exists($_SERVER['REMOTE_ADDR'],$ips)){
                $ip = $_SERVER['REMOTE_ADDR'];
                @$ips[$ip] = time();
                $data = array(
                    'ips' => $ips,
                    'sent_time' => isset($filedetect['sent_time'])?$filedetect['sent_time']:0
                );
                @file_put_contents('../storage/ips.json',json_encode($data));
            }
        }
        return true;
    }

}

