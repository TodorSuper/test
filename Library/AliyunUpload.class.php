<?php
/*
 * Author: haowenhui
 * 阿里云 文件上传类
 */


/*  新增 domo
 *
 *	public function add(){
 *		$aliyun = new \Common\Library\AliyunUpload();
 *		$url = $aliyun->putFile('C:\Users\Administrator\Desktop\6409210_141829507000_2.jpg');
 *		echo '<img src="'.$url.'" alt="" />';
 *	}
 *	
 *	删除 domo
 *	public function del(){
 * 		$aliyun = new \Common\Library\AliyunUpload();
 *		$url = $aliyun->deleteFile('C:\Users\Administrator\Desktop\6409210_141829507000_2.jpg');
 *	}
 *
 */

namespace Library;

use \Aliyun\OSS\OSSClient;

class AliyunUpload{
	
	protected $AccesskeyId = '';        // key id
	
	protected $AccesskeySecret = '';    // key 密匙
	
	protected $bucket = '';			  // 存储单元 名称

	protected $domain = '';		      //阿里云服务器访问域名

	protected $client = null;			  // aliyun 实例对象

	/**
	 * __construct 
	 * 初始化函数
	 * @access public
	 * @return void
	 */
	public function __construct() {
		//读取配置 初始化类属性
		$this->AccesskeyId = C('OSS_KEYID');
		$this->AccesskeySecret = C('OSS_KEYSECRET');
		$this->bucket = C('OSS_BUCKET');
		$this->domain = C('OSS_ALIYUN_DOMAIN');
		//引入外部文件
		require_once 'aliyun.php';
		//实例化对象
		$this->client = OSSClient::factory(array(
		    'AccessKeyId' => $this->AccesskeyId,
			'AccessKeySecret' => $this->AccesskeySecret,
		));
	}

	/**
	 * putObject 
	 * 云上传文件
	 * @access public
	 * @return 返回上传后的url
	 */
	public function putFile($filePath) {
		$key = $this->getFileSuffix($filePath);
                $key = date('Ymd',time()).'/'.$key;
		$res = fopen($filePath, 'r');
		$name = $this->client->putObject(array(
			'Bucket' => $this->bucket,
			'Key' => $key,
			'Content' => $res,
			'ContentLength' => filesize($filePath),
			'Expires' => new \DateTime("+3650 day"),    //设置缓存时间为3650天
		));
		fclose($res);
		$upload_url = $this->domain.'/'.$key;
		return $upload_url;
	}


	/**
	 * putObject 
	 * 云上传文件
	 * @access public
	 * @return 返回上传后的url
	 */
	public function putVideo($filePath) {
		$key = $this->getFileSuffix($filePath);
		$res = fopen($filePath, 'r');
		$name = $this->client->putObject(array(
			'Bucket' => $this->bucket,
			'Key' => $key,
			'Content' => $res,
			'ContentType' => 'video/mp4',
			'ContentLength' => filesize($filePath),
			'Expires' => new \DateTime("+3650 day"),    //设置缓存时间为3650天
		));
		fclose($res);
		$upload_url = $this->domain.'/'.$key;
		return $upload_url;
	}


	/**
	 * deleteFile
	 * 删除云文件
	 * @param mixed $filePath 
	 * @access public
	 * @return void
	 */
	public function setFile($key) {
		$res = $this->client->putObject(array(
			'Bucket' => $this->bucket,
			'Key' => $key,
			'ResponseExpires'=> new \DateTime("+3650 day"),    //设置缓存时间为3650天
		));
		return $res;
	}
	
	/**
	 * deleteFile
	 * 删除云文件
	 * @param mixed $filePath 
	 * @access public
	 * @return void
	 */
	public function deleteFile($filePath) {
		$key = $this->getFileSuffix($filePath);
		$this->client->deleteObject(array(
			'Bucket' => $this->bucket,
			'Key' => $key,
		));
	}

	/**
	 * getFileSuffix 
	 * 截取文件后缀名称
	 * @access private
	 * @return void
	 */
	private function getFileSuffix($fileName) {
		$suffix = basename($fileName);
		return $suffix;
	}
        
        /**
         * 获取列出指定目录下的所有Object
         */
        public function getObjectList($Prefix=''){
            $objectList = $this->client->listObjects(
                    array(
                        'Bucket' => $this->bucket,
			'Prefix' => $Prefix,
                        'MaxKeys'=>1000,
                        )
             );
             foreach ($objectList->getObjectSummarys() as $objectSummary) {
                $res[] =  $objectSummary->getKey();
            }
            return $res;
        }
        
        /**
         * 获取所有目录
         */
        public function getObjectDir($Prefix=''){
            $objectList = $this->client->listObjects(
                    array(
                        'Bucket' => $this->bucket,
			'Prefix' => $Prefix,
                        'Delimiter' => '/',
                        
                        )
             );
            
            foreach ($objectList->getCommonPrefixes() as $objectSummary) {
                $res[] =  $objectSummary;
            }
            return $res;
        }

}
?>

