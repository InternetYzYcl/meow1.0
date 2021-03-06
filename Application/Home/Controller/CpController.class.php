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
	protected $oldpassword;
	protected $newpassword;

	public function _initialize() {
		$this->userDB = M('user');
	}

	public function index() {
		$this->getStuData();
		if($this->changePc()) {
			$status = '200';
			$info = 'success';
		} else {
			$status = '801';
			$info = 'update faild';
		}
		$return = array(
			'status' => $status,
			'info' => $info,
			'data' => '',
			'version' => '1.0',
		);
		$this->ajaxReturn($return);
	}

	/**
	 * 获得用户数据
	 */
	protected function getStuData() {
		$json_data = $_POST['data'];
		$data = json_decode($json_data, true);
		$this->stuid = $data['stuid'];
		$this->password = $data['password'];
		$this->oldpassword = $data['oldpassword'];
		$this->newpassword = $data['newpassword'];
	}
	/**
	 * 验证新旧密码并保存, password可能要加密
	 */
	protected function changePc() {
		if($this->isThisStudent()) {
			if($this->oldpassword == $this->password) {
				$data['idnum'] = $this->newpassword;
				$res = $this->userDB->where(array('stuid' => "$this->stuid"))->save($data);
				return 1;
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}
	/**
	 * 查询是否存在这个学生
	 */
	protected function isThisStudent() {
		if($this->userDB->where(array('stuid' => $this->stuid, 'idnum' => $this->password))->find()) {
			return 1;
		} else {
			return 0;
		}
	}
}
