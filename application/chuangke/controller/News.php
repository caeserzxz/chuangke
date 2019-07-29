<?php

namespace app\chuangke\controller;
use think\Controller;
use think\Db;

class News extends Controller
{
    /**
     * 消息列表
     */
    public function newsList(){

        return $this->fetch();
    }
}