<?php

namespace app\chuangke\controller;
use think\Controller;
use think\Db;
use app\common\logic\MemberLogic;
use app\mobile\controller\MobileBase;

class Article extends MobileBase
{
    public function __construct()
    {
        parent::__construct();

    }

    public function index(){
        $id = I('id');
        $article = M('article')->where(array('cat_id'=>$id))->find();
        $article['content'] = htmlspecialchars_decode( $article['content']);
        $this->assign('article',$article);
        return $this->fetch();
    }
}