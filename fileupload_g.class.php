<?php
/**
 * Created by PhpStorm.
 * User: ct
 * Date: 2015/4/14
 * Time: 21:54
 * file: fileupload.class.php  文件上传类FileUpload本类的实例对象用于处理上传文件，可以上传一个文件，也可同时处理多个文件上传
 *(具体规范在搜狗笔记)书写规则，变量一律小写。函数按驼峰规则书写。类使用驼峰法命名，并且首字母大写
 * 变量只允许由小写字母和下划线组成，且建议用描述性的变量的命名，越详细越好，以至于像 $i 或 $n 等等都是不鼓励使用的。
 * 方法名只允许由字母组成，下划线是不允许的，首字母要小写，其后每个单词首字母要大写，即所谓的 “驼峰法命名” 规则，且越详细越好，应该能够描述清楚该方法的功能，例如switchModel、findPage。
 */

class FileUpload{

    private $path='./uploads'; //定义上传文件的路径
    private $allowtype=array('txt','xls','png','jpg','mp3','gif');  //定义允许上传的文件类型
    private $maxsize=1000000;     //限制上传文件的大小,单位字节
    private $israndname=true;//设置是否随机重命名文件，false为不随机

    private $originname;//定义上传文件的源文件名
    private $tmpname;//临时文件名
    private $filetype; //上传文件的后缀
    private $filesize;//文件大小
    private $newfilename;//新文件名
    private $errornum;//错误号
    private $errormess='';//错误的报告信息

    /*set方法用于设置成员属性（$path,$allowtype,$maxsize,$israndname）
     *可以通过连贯操作一次设置多个属性值
     *@param	string	$key	成员属性名(不区分大小写)
	 *@param	mixed	$val	为成员属性设置的值
	 *@return	object			返回自己对象$this，可以用于连贯操作
    */
   function set($key,$val){
       $key=strtolower($key);//统一设置成为小写
       /*get_class_vars()--  返回由类的默认属性组成的关联数组，此数组的元素以 varname => value 的形式存在。
         get_class()返回对象实例 obj 所属类的名字(也就是所引用的类的名称)。如果 obj 不是一个对象则返回 FALSE。在本例中get_class($this)获得本对象的类名称，也可以使用实例化后的名称取代$this
       */
       /*如果传进来的$key在类中默认属性，则进行设置*/
       if(array_key_exists(get_class_vars(get_class($this)))){
           $this->setOption($key, $val);
       }
       return $this;//返回修改好的实例化的类
   }

    /*调用该方法上传的类
    @program string $fileField 上传的文件的表单名称
    @return bool 上传是否成功 ture false 如果上传成功返回数true
    */
    function unload($fileField){
        /*文件上传步骤：多文件上传只是多个循环而已
         1、先设置上传过来的信息存储到类的变量中
         2、检查文件大小及类型，合格则为上传的文件赋值新名(要么源文件名要么随机文件名，由随机参数控制)
         3、copy 文件到path设定的地方*/
        /* 如果是多个文件上传则$_FILES["name"]会是一个数组,其实其中$_FIlES为3维数组，其中5个类型项目分别存储多个文件的类型 */
        $return=true;//设置这个变量的意义在与有错误不马上退出，先设置好错误代码，最后依据错误代码，统一写出错误信息。
        //检查路径是否合法
        if(!$this->checkFilePath()){
           // $this->setOption('errornum',-5);
           $this->errormess=$this->__getError();//返回错误的提示信息,赋值给变量
            return false;
        }
        /* 将文件上传的信息取出赋给变量 就是这个中的name<input type="file" name="file" id="file" />*/
        $name=$_FILES['$fileField']['name'];
        $tmp_name=$_FILES['$fileField']['tmp_name'];
        $size=$_FILES['$fileField']['size'];
        /*该文件上传相关的错误代码。以下为不同代码代表的意思：0; 文件上传成功。1; 超过了文件大小php.ini中即系统设定的大小。
          2; 超过了文件大小MAX_FILE_SIZE 选项指定的值。3; 文件只有部分被上传。4; 没有文件被上传。5; 上传文件大小为0。*/
        $error = $_FILES[$fileField]['error'];//上传文件失败时候，数组自带的错误号，非自定义
        //$type = $_FILES[$fileField]['type'];

        if(is_array($name)){//表示上传多个文件
            $errors=array();
            /*多个文件上传则循环处理 ， 这个循环只有检查上传文件的作用，并没有真正上传 */
            for($i=0;$i<count($name);$i++){
                /*设置文件信息,这里循环设置上传文件的原文件名、临时文件名、后缀名、大小、报错号 */
                if($this->setFiles($name[$i],$tmp_name[$i],$size[$i],$error[$i])){//如果设置成功
                    /*检查文件大小及文件类型*/
                    if(!$this->checkFileSize() || !$this->checkFileType()){//如果文件大小及文件类型出错
                    /*上面的两个检查函数，如果失败，则进入下面语句。函数内会设置$errornum，这样循环数组得出提示信息*/
                        $error[]=$this->getErrorMess();
                        $return=false;//这里设置变量值，不直接返回
                    }

                }else{//如果是其他类型的错误
                    $error[]=$this->getErrorMess();
                    $return=false;//这里设置变量值，不直接返回
                }
                /* 如果有问题，则重新初使化属性 */
                if(!$return){
                    $this->setFiles();//上传的这个文件失败则把本类的变量设置为原始状态，为下一次读文件准备。
                }
            }
            if($return){//检查通过，真正开始上传文件
                /* 存放所有上传后文件名的变量数组 */
                $filenames=array();
                /* 如果上传的多个文件都是合法的，则通过消息循环向服务器上传文件 */
                for($i = 0; $i < count($name);  $i++){
                    if($this->setFiles($name[$i], $tmp_name[$i], $size[$i], $error[$i] )){
                        $this->setNewFileName();//给每个上传的文件赋值源文件名,为下面的copyFile（）准备
                        if(!$this->copyFile()){//如果copy文件失败
                          $errors[]=$this->getErrorMess();
                            $return = false;
                        }
                        $filenames[]=$this->newfilename;//先把上传的多个文件赋值的新名依次赋值给数组
                    }
                }
                $this->newfilename=$filenames;//最后把多个文件名的数组，放入类的变量
            }
            $this->errorMess = $errors;
            return $return;
        }else{//单个文件
            /* 设置文件信息 */
            $this->setFiles($name,$tmp_name,$size,$error);
            /* 上传之前先检查一下大小和类型 */
            if($this->checkFileSize() && $this->checkFileType()){
                /* 为上传文件设置新文件名 */
                $this->setNewFileName();//主要是设置copy目标的文件名
                /* 上传文件   返回0为成功， 小于0都为错误 */
                if($this->copyFile()){//$this->copyFile()使用前面必须先调用 $this->setNewFileName()得出新文件名;然后再组合得到绝对路径
                    return true;
                }else{
                    $return=false;
                }
            }else{
                $return=false;
            }
            //如果$return为false, 则出错，将错误信息保存在属性errorMess中
            if(!$return)
                $this->errorMess=$this->getError();
            return $return;
        }
    }


    /**
     * 获取上传后的文件名称
     * @param	void	 没有参数
     * @return	string 	上传后，新文件的名称， 如果是多文件上传返回数组
     */
    function getFileName(){
        return $this->newfilename;//这里$this->newfilename由setNewFileName()直接赋值，要么源文件名要么随机文件名
    }
    /**
     * 上传失败后，调用该方法则返回，上传出错信息
     * @param	void	 没有参数
     * @return	string 	 返回上传文件出错的信息报告，如果是多文件上传返回数组
     */
    /*此函数只是为了好看*/
    function getErrorMsg(){
        return $this->errormess;//$this->errormess由__getError()设置
    }

    /*设置上传时候发生的的出错信息
    当类成员方法被声明为 private 时，必须分别以双下划线 "__"为开头；被声明为 protected 时，必须分别以单下划线 "_" 为开头；一般情况下的方法不含下划线。例如 ：
   class Foo
   {    private function __example()
       {  // ... }
       protected function _example()
       {  // ...}
       public function example()
       { // ... }}*/
    private function __getError(){
          $str="上传文件<font color='red'>{$this->originname}</font>时出错:";//'red'不能用“”,否则出错，这是嵌套错误
         /*. "switch" 条件控制语句中，必须用空格将待测参数与其它元素分隔开。例如：
              switch ($num) { // …}
             "switch" 语句的内容必须以四个空格缩进，"case" 条件控制的内容必须再加四个空格进行缩进。 在 "switch" 语句中应该总是包括 "default" 控制。
              有时候我们需要在 "case" 语境中省略掉 "break" 或 "return" ，这个时候我们必须为这些 "case" 语句加上 "// 此处无break" 注释。例如：*/
        switch ($this->errornum){
             case 4:
                  $str .="没有文件被上传";
                  break;
             case 3:
                   $str .="文件只有部分被上传";
                  break;
             case 2:
                $str .="上传文件的大小超过了HTML表单中MAX_FILE_SIZE选项指定的值";
                break;
             case 1:
                 $str .="上传的文件超过了php.ini中upload_max_filesize选项限制的值";
                 break;
            case -1:
                $str .="未允许类型";
                break;
            case -2:
                $str .="文件过大,上传的文件不能超过{$this->maxsize}个字节";
                break;
            case -3:
                $str .="上传失败";
                break;
            case -4:
                $str .="建立存放上传文件目录失败，请重新指定上传目录";
                break;
            case -5:
                $str .="必须指定上传文件的路径";
                break;
            default:
                $str .="未知类型错误";
                break;
        }
        return $str.'<br>';
    }

    /* 设置和$_FILES有关的内容 ,传进来4个参数，实际要设置5个变量*/
    function setFiles($name='',$tmp_name='',$size=0,$error=0){
        $this->setOption('errornum',$error);//
        if($this->$errornum){
            return false;
        }
        $arystr=explode('.',$name);
       $this->setOption('tmpname',$tmp_name);//自定义函数规定的参数
        $this->setOption('originname',$name);//原始的文件名
        $this->setOption('filetype',strtolower($arystr[count($name)-1]));//如果出现类似 upfile.class.php等多个。的文件名,统一小写
        $this->setOption('filesize',$size);//文件大小
        return true;

    }


    /*为单个属性赋值*/
    function setOption($key,$val){
        $this->$key=$val;
    }
    /* 设置上传后的文件名称 */
    function setNewFileName(){
        if($this->israndname){//默认为是
            $this->setOption('newfilename',$this->isRandName());
        }else{
            $this->setOption('newfilename',$this->originname);
        }
       return true;
    }


    /* 检查上传的文件是否是合法的类型 */
    function checkFileType(){
       if(in_array(strtolower($this->filetype),$this->allowtype)){//先行统一为小写
           return true;
       }else{
            $this->setOption('errornum',-1);
           return false;
       }
    }

    /* 检查上传的文件是否是允许的大小 */
    function checkFileSize(){
    if($this->filesize>$this->maxsize){
        $this->setOption('errornum',-2);
        return false;
    }else{
        return true;
         }
    }

    /* 检查是否有存放上传文件的目录 */
    function checkFilePath(){
    if(empty($this->path)){//应该是如果路径变量为空，则调用自定义函数setOption设置出错的代码。返回false
       $this->setOption('errornum',-5);
        return false;
    }
      /*file_exists() 函数检查文件或目录是否存在。如果指定的文件或目录存在则返回 true，否则返回 false。语法 file_exists(path)
      is_writable() 函数判断指定的文件是否可写。is_writable(file) 如果文件存在并且可写则返回 true。file 参数可以是一个允许进行是否可写检查的目录名。
      mkdir() 尝试新建一个由 path 指定的目录。默认的 mode 是 0777，意味着最大可能的访问权。umask只对当前目录有效，默认umask值为0022，所以你无法在另外一个地方直接创建0777的目录。
      而是0777-0022=0755.所以在unix环境下写0755总不会错*/
        if(!file_exists($thi->path) || !is_writable($this->path)){//如果路径标定的目录不存在或者不可写
          if(!mkdir($tihs->path,0755))
              {//如果创建目录失败，设置出错等级，返回false。
              $this->setOption('errornum',-4);
              return false;
               }
            return true;//创建成功或者经过检查有这个目录，返回true
        }
    }


    /* 设置随机文件名
    @param void 没有参数
    @return string 返回生成文件名，格式为随机生成的文件名+所上传文件的后缀
    */
    function isRandName(){
     $filename=date('YmdHis').'_'.rand(100,999);//随机生成日期时间加上随机数的文件名
        return $filename.'.'.$this->filetype;//返回格式为随机生成的文件名+所上传文件的后缀

    }


    /* 复制上传文件到指定的位置 */
    function copyFile(){
        if(!$this->errornum){//如果errornum=0则设置表示正常，开始设置目标的绝对路径
            $this->path=rtrim($this->path,"/")."/";//不管path最后是否有"/"，先删除再加上
            $this->path.=$this->newfilename;
            if(@move_uploaded_file($this->tmpname,$this->path)){//如果搬运文件失败
                return true;
            }else{
                $this->setOption('errornum',-3);
                return false;
            }

        }else{//如果要copy函数的是否，errornum为出错，则不能copy
            return true;
        }
    }
}