<?php

/*
 * 在您使用STS SDK前，请仔细阅读RAM使用指南中的角色管理部分，并阅读STS API文档
 * RAM 使用指南：https://docs.aliyun.com/#/pub/ram/ram-user-guide/role&intro
 * STS API文档：https://docs.aliyun.com/#/pub/ram/sts-api-reference/actions&assume_role
 *
 */

namespace Library;

include_once 'Oss/aliyun-php-sdk-core/Config.php';

use Sts\Request\V20150401 as Sts;

class Oss {

    protected $AccesskeyId = '';        // key id
    protected $AccesskeySecret = '';    // key 密匙
    protected $bucket = '';     // 存储单元 名称
    protected $domain = '';        //阿里云服务器访问域名
    protected $client = null;     // aliyun 实例对象
    protected $node = '';
    protected $arn = '';
    protected $duration = '';

    /**
     * __construct 
     * 初始化函数
     * @access public
     * @return void
     */

    public function __construct($role) {
        $oss_config = C('OSS');
        $oss_config = $oss_config[$role];
        //读取配置 初始化类属性
        $this->AccesskeyId = $oss_config['OSS_KEYID'];
        $this->AccesskeySecret = $oss_config['OSS_KEYSECRET'];
        $this->bucket = $oss_config['OSS_BUCKET'];
        $this->domain = $oss_config['OSS_ALIYUN_DOMAIN'];
        $this->node = $oss_config['OSS_NODE'];
        $this->arn = $oss_config['OSS_ARN'];
        $this->duration = $oss_config['OSS_DURATION'];
        //引入外部文件
    }

    public function getSts($uc_code='') {

        // 你需要操作的资源所在的region，STS服务目前只有杭州节点可以签发Token，签发出的Token在所有Region都可用
// 只允许子用户使用角色
        $iClientProfile = \DefaultProfile::getProfile($this->node, $this->AccesskeyId, $this->AccesskeySecret);
        $client = new \DefaultAcsClient($iClientProfile);

// 角色资源描述符，在RAM的控制台的资源详情页上可以获取
        $roleArn = $this->arn;


// 在扮演角色(AssumeRole)时，可以附加一个授权策略，进一步限制角色的权限；
// 详情请参考《RAM使用指南》
// 此授权策略表示读取所有OSS的只读权限

        $data = array(
            'Statement' =>
            array(
                0 =>
                array(
                    'Action' =>
                    array(
                        0 => 'oss:Get*',
                        1 => 'oss:List*',
                    ),
                    'Effect' => 'Allow',
                    'Resource' => '*',
                ),
            ),
            'Version' => '1',
        );
        $policy = json_encode($data);

        $request = new Sts\AssumeRoleRequest();
// RoleSessionName即临时身份的会话名称，用于区分不同的临时身份
// 您可以使用您的客户的ID作为会话名称
        $request->setRoleSessionName($uc_code);
        $request->setRoleArn($roleArn);
//        $request->setPolicy($policy);
        $request->setDurationSeconds($this->duration);
        $response = $client->doAction($request);
        $data = json_decode($response->getBody(), true);
        $data = $data['Credentials'];
        $status = $response->getStatus();
        if($status != 200){
            //不成功
            return FALSE;
        }
        $data['Domain'] = $this->domain;
        return $data;
       
    }

}

?>
