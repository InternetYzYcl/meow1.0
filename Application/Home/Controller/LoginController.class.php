<?php
namespace Home\Controller;
use Think\Controller;

class LoginController extends Controller
{
	protected $stuId;
	protected $password;
	protected $url = 'http://hongyan.cqupt.edu.cn/api/verify';

	public function index() {
		$stuId = I('post.stuId');
		$password = I('post.password');
		$flag = $this->inspect($stuId, $password);
		if($flag) {
			$this->stuId = $stuId;
			$this->password = $password;
			$post_data = array(
				'stuNum' => $stuId,
				'idNum' => $password,
			);
			$data = $this->curlpost($this->url, $post_data);
			$data = json_decode($data, true);
			if
			//待完成..............
			$this->ajaxReturn($data);
		} else {
			$return = array(
				'status' => 403,
				'info' => 'param invalid',  //输入非法
				'data' => '',
				'version' => '1.0',
			);
			$this->ajaxReturn($return);
		}
	}

	//验证
	protected function inspect($stuId, $password) {
		$flag_1 = is_numeric($stuId);
		//拆分password
		$pass_1 = substr($password, -1);
		$pass_2 = substr($password, 0, strlen($password) - 1);
		if(is_numeric($password)) {
			$flag_2 = 1;
		} elseif (is_numeric($password) || $pass_2 == 'x' || $pass_2 == 'X') {
			$flag_2 = 1;
		}
		//判断并返回
		if(($flag_1 == 1) && ($flag_2 == 1))
			return 1;
		else
			return 0;
	}

	//curl链接学号接口
	public function curlPost($url = '', $post_data = array()) {    //模拟登陆
        if (empty($url) || empty($post_data)) {
            return false;
        }  
        $o = "";
        foreach ( $post_data as $k => $v ) 
        { 
            $o.= "$k=" . urlencode( $v ). "&" ;
        }
        $post_data = substr($o,0,-1);

        $postUrl = $url;
        $curlPost = $post_data;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch); 
        return $data;
    }
}