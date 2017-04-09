<?php
namespace Home\Controller;
use Think\Controller;

class LoginController extends Controller
{
	protected $stuId;
	protected $password;

	public function index() {
		$stuId = I('post.stuId');
		$password = I('post.password');
		$flag = $this->inspect($stuId, $password);
		if($flag) {
			$this->stuId = $stuId;
			$this->password = $password;
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
	protected function curlpost(){
	    $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => "Content-type: text/html; charset=utf-8",
	    );
            //判断是否传输文件
            $params = $multi ? $params : http_build_query($params);
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
        default:
            throw new Exception('不支持的请求方式！');
	    }
	    /* 初始化并执行curl请求 */
	    $ch = curl_init();
	    curl_setopt_array($ch, $opts);
	    $data  = curl_exec($ch);
	    $error = curl_error($ch);
	    curl_close($ch);
	    if($error) throw new Exception('请求发生错误：' . $error);
	    return  $data;
	}
}