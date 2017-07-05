<?php
//1.命名空间
//2.为了符合composer中的psr-4规则
namespace liuge\model;
//建立一个base类
class Base{
    //建立一个私有的静态属性，并赋值给null
    //方便全局调用
    private static $pdo = null;
    //建立一个私有属性
    private $table;
    //建立一个私有属性默认为空
    private $where='';
    //创建一个构造方法
    //在外部实例化这个类时，会自动触发该方法
    public function __construct($config,$table) {
        //调用当前类的connect方法
        //在外部实例化这个类是，会自动触发这个构造方法，就会自动调用这个方法
        $this->connect($config);
        //调用当前类的$table.属性，并赋值给$table
        $this->table=$table;
    }
    //建一个连接数据库的方法
    private function connect($config){

        //如果属性$pdo已经连接过数据库，就不需要重复连接了
        if (!is_null(self::$pdo)) return;
        //如果在try之中产生了pdo的异常错误，会被catch捕捉到
        try{
            $dsn="mysql:host=" . $config['db_host'] .";dbname=" . $config['db_name'];
            $user=$config['db_user'];
            $password=$config['db_password'];
            $pdo=new \PDO($dsn,$user,$password);
            //1.设置错误类型
            //2.异常错误，能被catch捕捉到
            $pdo->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);
            //1.设置字符集，根据客户端的字符集来设置
            //2.这样设置了字符集才不会乱码
            $pdo->query("SET NAMES " . $config['db_charset']);
            //存到静态属性中
            //方便全局调用
            self::$pdo=$pdo;

        }catch (\PDOException $e){
            //输出错误代码，并结束代码执行
            exit($e->getMessage());
        }
    }
    //建立一个获得其中的一条方法
    public function where($where){
        $this->where="WHERE {$where}";
        //把当前的对象返回出去
        return $this;
    }
    //建立一个get方法
    public function get(){
        //建立一个sql语句
        //因为要从mysql数据库中调用数据
        $sql="SELECT * FROM {$this->table} {$this->where}";
       //调用当前类中的q方法，并把sql作为参数出入q方法中
        return $this->q($sql);
    }
    //建立一个q方法
    public function q($sql){
        //建立一个try
        //用来捕捉异常错误
        try{
            //调用pdo的query方法 并赋值给$result
            $result=self::$pdo->query($sql);
            $data = $result->fetchAll(\PDO::FETCH_ASSOC);
//            p($data);
            return $data;
            }catch (\PDOException $e){
            //输出错误代码，并结束代码执行
            exit($e->getMessage());
        }
    }
    //建立一个无结果的方法e，来操作增删改的效果
    public function e($sql){
        //建立一个try
        //用来捕捉异常错误
        try{
            //调用pdo的exec方法 exec是执行无结果的操作
            return self::$pdo->exec($sql);
        }catch (\PDOException $e){
            //输出错误代码，并结束代码执行
            exit($e->getMessage());
        }
    }
    //建立一个寻找数据中一条的方法
    public function find($pri){
        //获得主键的字段，用$priField接收
        //如果要寻找其中的一条数据，获得主键字段，比如cid还是aid
        $priField= $this->getPri();
//        p($priField);
        //调用当前类中的where方法
        //里面传入的参数是主键字段=用户传进来的id
        $this->where("{$priField}={$pri}");
        //组合一天sql语句
        //因为当前这个方法是要用sql数据库连接的，要用到sql语句
        $sql="SELECT * FROM {$this->table} {$this->where}";
//        echo $sql;//SELECT * FROM article WHERE aid=1
        //调用当前类中的q方法把组合sql语句作为参数传入到q方法中并赋值给$data
        //因为q方法执行的是有结果集的操作，当前方法是一个有结果集的操作，所有要调用q方法
        $data=$this->q($sql);
//     p($data);
        //把原来的二维数组变成一维数组
        $data=current($data);
//        p($data);
//        Array
//        (
//            [0] => Array
//            (
//                [aid] => 3
//            [title] => 明天要放假了
//        )
//)
        //把获得的数组赋给当前类中的私有属性$data
        //为了方便在findArray方法中调用
        $this->data= $data;
        //返回当前的对象给findArray方法
        return $this;
    }
    //建立一个从数据库查询一条的方法
    public function findArray($pri){
        //调用当前类中的find方法
        //把接收到的方法赋值给$obj,因为find方法返回的是一个对象，所以在这里也是一个对象
            $obj=$this->find($pri);
//            p($obj);
        //调用$obj对象中的data数据
        //因为我们接收到的是一个对象，但是返回时不能返回一个对象，要返回对象中的数组
        //find方法在返回对象是已经获得了数组，赋值给属性，也就是说在返回的这个对象中已经存在
        //返回给app\home\controller\Entry类中的arc方法
            return $obj->data;
    }
    //获得表的主键
    public function getPri(){
        //要获得表的主键，首先要查看表的结构
        //要调用当前类中的q方法，里面传入sql语句就可以获得表结构
        $desc = $this->q("DESC {$this->table}");
//        p($desc);
        //获得表的主键字段
        //定义一个变量 $priField 默认为空
        //为了存入主键字段
        $priField='';
        //循环获得表的结构
        //为了获得主键字段
        foreach ($desc as $v){
            //做if判断
            //当$v['Key']=='PRI'时，说明就是一个主键
            if ($v['Key']=='PRI'){
                //$v['Field']代表主键字段，就把 $v['Field']赋给上面定义好的$priField
                $priField = $v['Field'];
//                p($priField);
                //如果找到主键，就跳出终止本次循环
                break;
            }
        }
        //把获得的主键字段返回当前类中的find方法
        return $priField;
    }
}
