<?php
/**
 * Created by PhpStorm.
 * User: jagad
 * Date: 8/16/2017
 * Time: 11:51 PM
 */

namespace App\Traits;

trait ApiResponse
{
    private $jsonResponse = [];

    public function onSuccess($data = null,$msg)
    {
        $this->jsonResponse['error'] = false;
        $this->jsonResponse['data'] = $data;
        $this->jsonResponse['msg'] = $msg;
        return $this->jsonResponse;
    }

    public function onFailure($data = null, $msg)
    {
        $this->jsonResponse['error'] = true;
        $this->jsonResponse['data'] = $data;
        $this->jsonResponse['msg'] = $msg;
        return $this->jsonResponse;
    }
}