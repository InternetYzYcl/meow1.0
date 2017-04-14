<?php
namespace Home\Controller;
use Think\Controller;

class LoginController extends Controller
{
	protected $stuId;
	protected $password;
	protected $userDB;
	protected $url = 'http://hongyan.cqupt.edu.cn/api/verify';

	public function _initialize() {
		$this->userDB = M('user');
	}

	/**
	 * 获得学生学号和密码，并进行处理
	 */
	public function index() {
		//学号密码
		$post_json = $_POST['data'];
		$post = json_decode($post_json, true);
		$stuId = $post['stuid'];
		$password = $post['password'];
		// $stuId = 2015210367;
		// $password = 247328;
		// $flag = $this->inspect($stuId, $password);
		$flag = 1;
		if($flag) {
			$this->stuId = $stuId;
			$this->password = $password;
			//调整将要返回的数据
			if($this->LoginWay()) {
				$info = 'success';
				$data = $this->userDB->where(array('stuid' => $this->stuId))->find();
				$status = "200";
			} else {
				$info = 'login failed';
				$data = '';
				$status = "801";
			}
		} else {
			$info = 'param invalid';  //输入非法
			$data = '';
			$status = "801";
		}
		$return = array(
			'info' => $info,
			'data' => $data,
			'status' => $status,
			'version' => '1.0',
		);
		$this->ajaxReturn($return);
	}
	/**
	 * 加密方法
	 * @param $password 需要加密的密码
	 * @return $password_encrypt 加密后的密码
	 */
	protected function encrypt() {

	}
	/**
	 * 验证输入是否正确
	 * @param $flag_1 $flag_2 得到判断后的正误 int类型
	 */
	protected function inspect($stuId, $password) {
		$flag_1 = is_numeric($stuId);
		//拆分password
		$pass_1 = substr($password, -1);
		$pass_2 = substr($password, 0, strlen($password) - 1);
		echo $pass_2;
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
	/**
	 * curl链接学号接口 模拟登陆
	 */
	protected function curlPost($url = '', $post_data = array()) {    
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
        //初始化curl
        $ch = curl_init();
        //抓取指定网页
        curl_setopt($ch, CURLOPT_URL,$postUrl);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        //运行curl
        $data = curl_exec($ch);
        curl_close($ch); 
        return $data;
    }
    /**
	 * 使用两种不同的登陆方式
	 * @param $json_data 使用接口返回的json格式的学生数据
	 */
	protected function LoginWay() {
		if($student = $this->userDB->where(array('stuid' => $this->stuId))->find()) {
			//使用数据库登陆
			if($student['idnum'] == $this->password)
				return 1;
			else
				return 0;
		} else {
			//使用接口登陆,接口的post数据
			$post_data = array(
				'stuNum' => $this->stuId,
				'idNum' => $this->password,
			);
			$json_data = $this->curlpost($this->url, $post_data);
			$data = json_decode($json_data, true);
			if($data['status'] == 200) {
				$array = array(
					'stuid' => $this->stuId,
					//可能要加密
					'idNum' => $this->password,
					'name' => $data['data']['name'],
					'gender' => $data['data']['gender'],
					'class' => $data['data']['classNum'],
					'major' => $data['data']['major'],
					'college' => $data['data']['college'],
					'grade' => $data['data']['grade'],
					//可能需要修改
					'character' => 'student'
				);
				$insert_res = $this->userDB->data($array)->add();
				if($insert_res)return $array;else return 0;
			} else {
				return 0;
			}
		}
	}
}