<?php
/**
 * Created by PhpStorm.
 * User: jagad
 * Date: 8/20/2017
 * Time: 3:39 AM
 */

namespace App\Traits;


use GuzzleHttp\Client;

trait SmsClient
{
    private $authKey = "166951APutj3jVuG5977616b";
    private $sender = "DISJEE";
    private $route = 4;
    private $country = 91;

//    public function sendSms($to, $msg)
//    {
//        try {
//            $client = new Client();
//            $uri = "https://control.msg91.com/api/sendhttp.php?authkey=" . $this->authKey . "&mobiles=91" . $to . "&message=" . $msg . "&sender=" . $this->sender . "&route=" . $this->route . "&country=" . $this->country;
//            return $client->get($uri, []);
//        } catch (\Exception $exception) {
//            return $exception->getMessage();
//        }
//    }

    public function sendSms($to, $msg)
    {
        $uri = "http://nettyhost.com/smsapi/TR/sendmsg.aspx?&username=discountjee&password=djee_admin@123&mobile=".$to."&sendername=DISJEE&message=".$msg;
        $client = new Client();
        return $client->get($uri, []);
    }
}
