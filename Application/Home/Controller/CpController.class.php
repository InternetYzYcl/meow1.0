<?php
namespace Home\Controller;
use Think\Controller;
/**
* 修改密码
*/
class CpController extends Controller
{
	protected $userDB;
	protected $stuid;
	protected $password;

	public function _initiatize() {
		$this->userDB = M('user');
	}

	protected function index() {
		
	}
	protected function getStuData() {
		$this->stuid = I('post.stuid');
		$this->password = I('post.password');
	}
}
