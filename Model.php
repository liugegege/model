<?php
//1.命名空间
//2.为了符合composer中的psr-4规则
namespace liuge\model;
class Model{
    //创建一个私有静态属性
    //方便调用
    private static $config;
    //__call实例化不存在的方法时会自动触发该方法
    public  function __call($name, $arguments){
        //调用当前类中的parseAction方法
            return self::parseAction($name, $arguments);
    }
    //__callStatic调用不存在静态方法时会自动触发该方法

    public static function __callStatic($name, $arguments){
        //调用当前类中的parseAction方法
        return self::parseAction($name, $arguments);
    }
    //解析调用
    //建立一个私有的静态属性，只可以在内部调用
    private static function parseAction($name, $arguments){
        //获得调用当前这个类的类名和空间么，并赋值给 $table
        $table=get_called_class();
        //把获得值从右开始安装“/”截取，并去掉左边的“/”，并转为小写
        //因为类名与表名是一致的，又因为类名是大写的，但是表名是小写的
        $table=strtolower(ltrim(strrchr($table,'\\'),'\\'));
        //实例化base这个类，并把值返回去
        return call_user_func_array([new Base(self::$config,$table),$name],$arguments);
    }
    public static function setConfig($config){
        self::$config=$config;
    }
}

