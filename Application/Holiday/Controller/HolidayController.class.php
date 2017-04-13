<?php
namespace Holiday\Controller;

use Think\Controller;

class HolidayController extends Controller {
	public $holidayDB;
	public function _initialize() {
		$this->holidayDB = M('holiday');
	}
    /**
     * @param $json 申请请假的详细信息 
     */
    public function apply() {        
        $data = json_decode($_POST['data'],true);

		$stuid = $data['stuid'];
    	$password = $data['password'];
        
    	if($this->checkPassword($stuid,$password)) {
			$data['status'] = 1;
    		$data['class'] = $this->getClassByStuid($data['stuid']);
    		if($this->holidayDB->add($data)) {
	    		$this->ajaxReturn(array(
	    			'status' => 200,
	    			'info' => 'success',
	    			'version' => '1.0,'
	    		));
	    	} else {
	    		$this->ajaxReturn(array(
	    			'status' => 400,
	    			'info' => 'saving fail, unknown reason',
	    			'version' => '1.0',
	    		));
	    	}
    	} else {
    		$this->ajaxReturn(array(
    			'status' => 403,
    			'info' => 'password wrong',
    			'version' => '1.0'
    		));
    	}
    }
    //取消请假 
    public function cancleHoliday() {
    	$data = json_decode($_POST['data'],true);
    	$stuid = $data['stuid'];
    	$password = $data['password'];
    	if($this->checkPassword($stuid,$password)) {
    		$requestid = $data['requestid'];
    		if(!$this->holidayDB->where(array('id' => $requestid))->save(array('status' => '7'))) {
    			$this->ajaxReturn(array(
	    			'status' => 403,
	    			'info' => 'requestid not found',
	    			'version' => '1.0'
	    		));
    		} else {
    			$this->ajaxReturn(array(
	    			'status' => 200,
	    			'info' => 'success',
	    			'version' => '1.0'
	    		));
    		}
    	} else {
    		$this->ajaxReturn(array(
    			'status' => 403,
    			'info' => 'password wrong',
    			'version' => '1.0'
    		));
    	}

    }
    //班委审核
    /**
     * @param $stuid $password 学号密码
     *
     * @param $agree 是否同意 同意为true 不同意为false
     *
     * @param $evaluate 评语
     *
     * @param $requestid 请假的id号
     */
    public function verifyByMonitor() {
    	$data = json_decode($_POST['data'],true);
    	$stuid = $data['stuid'];
    	$password = $data['password'];
    	if($this->checkPassword($stuid,$password) || $this->checkIsMonitor($stuid)) {
    		
            $status = $this->getHolidayColumn($data['requestid'],'status');

    		if($status['status'] != 1) {
    			$this->ajaxReturn(array(
	    			'status' => 403,
	    			'info' => 'requestid not found or already verified',
	    			'version' => '1.0'
	    		));
    		} else {
                $save = array(
                    'status' => $data['agree'] ? 3 : 2,             //通过是3 不通过是2
                    'evaluate_monitor' => $data['evaluate'],
                );
                $this->holidayDB->where(array('id' => $data['requestid']))->save($save);
    			$this->ajaxReturn(array(
	    			'status' => 200,
	    			'info' => 'success',
	    			'version' => '1.0'
	    		));
    		}

    	} else {
    		$this->ajaxReturn(array(
    			'status' => 403,
    			'info' => 'password wrong or student not monitor',
    			'version' => '1.0'
    		));
    	}

    }
    //辅导员审核
    public function verifyByTeacher() {
    	$data = json_decode($_POST['data'],true);
    	$stuid = $data['stuid'];
    	$password = $data['password'];
    	if($this->checkPassword($stuid,$password) || $this->checkIsTeacher($stuid)) {
    		$status = $this->getHolidayColumn($data['requestid'],'status');

            if($status['status'] != 3) {
                $this->ajaxReturn(array(
                    'status' => 403,
                    'info' => 'requestid not found or already verified',
                    'version' => '1.0'
                ));
            } else {
                $save = array(
                    'status' => $data['agree'] ? 5 : 4,             //通过是5 不通过是4
                    'evaluate_monitor' => $data['evaluate'],
                );
                $this->holidayDB->where(array('id' => $data['requestid']))->save($save);
                $this->ajaxReturn(array(
                    'status' => 200,
                    'info' => 'success',
                    'version' => '1.0'
                ));
            }
    	} else {
    		$this->ajaxReturn(array(
    			'status' => 403,
    			'info' => 'password wrong or student not teacher',
    			'version' => '1.0'
    		));
    	}
    }
    //获取单一学生所有请假信息
    /**
     * @param $stuid $password 学生学号 密码
     *
     * @return $data 学生所有请假的 简略信息
     */
    public function getMyHoliday() {
    	$data = json_decode($_POST['data'],true);
    	
        $stuid = $data['stuid'];
    	$password = $data['password'];
        $status = $data['status'] ? $data['status'] : null;
    	if($this->checkPassword($stuid,$password)) {

            if($status == null) {
                $where = array(
                    'stuid' => $stuid,
                );
            } else {
                $where = array(
                    'stuid' => $stuid,
                    'status' => $status,
                );
            }

    		$res = $this->holidayDB->where($where)->field('id, title, time, status')->select();
	    	$this->ajaxReturn(array(
    			'status' => 200,
    			'info' => 'success',
    			'data' => $res,
    			'version' => '1.0' 
    		));
    	} else {
    		$this->ajaxReturn(array(
    			'status' => 403,
    			'info' => 'password wrong',
    			'version' => '1.0'
    		));
    	}
        
    }
    //通过id获取某条请假信息的全部内容
    /**
     * @param $requestid 请假id号
     *
     * @return $data 某条假的详细信息
     */
    public function getHolidayById() {
    	$data = json_decode($_POST['data'],true);
    	$stuid = $data['stuid'];
    	$password = $data['password'];
        $requestid = $data['requestid'];
    	if($this->checkPassword($stuid,$password)) {
	    	$res = $this->holidayDB->where(array('id' => $requestid))->field('id',true)->select();
	    	$this->ajaxReturn(array(
    			'status' => 200,
    			'info' => 'success',
    			'data' => $res,
    			'version' => '1.0' 
    		));
    	} else {
    		$this->ajaxReturn(array(
    			'status' => 403,
    			'info' => 'password wrong',
    			'version' => '1.0'
    		));
    	}

    	
    }

    //班委获得该班级所有请假信息
    /**
     * @param $stuid 班委的学号
     *
     * @return $data 该班级所有请假的简略信息
     */
    public function getClassHoliday() {
    	$data = json_decode($_POST['data'],true);

    	$stuid = $data['stuid'];
    	$password = $data['password'];
        $status = $data['status'] ? $data['status'] : null;

    	if($this->checkPassword($stuid,$password) && $this->checkIsMonitor($stuid)) {
    		
			$class = $this->getClassByStuid($stuid);
           
            if($status == null) {
                $where = array(
                    'class' => $class,
                );
            } else {
                $where = array(
                    'class' => $class,
                    'status' => $status,
                );
            }
			$res = $this->holidayDB->where($where)->field('id, title, time, status')->select();
			$this->ajaxReturn(array(
				'status' => 200,
				'info' => 'success',
				'data' => $res,
				'version' => '1.0' 
			));

    	} else {
    		$this->ajaxReturn(array(
    			'status' => 403,
    			'info' => 'password wrong or student not monitor',
    			'version' => '1.0'
    		));
    	}

    	
    }
    //辅导员获取所有班级请假情况
    public function getMajorHoliday() {
    	$data = json_decode($_POST['data'],true);

    	$stuid = $data['stuid'];
    	$password = $data['password'];
        $status = $data['status'] ? $data['status'] : null;

    	if($this->checkPassword($stuid,$password)) {
    		
    		$res = M('class')->where(array('t_id' => $stuid))->field('class')->select();
            $class = array();
            foreach ($res as $key => $value) {
                $class[$key] = $value['class'];
            } 
			if($status == null) {
                $where = array(
                    'class' => array('in',$class),
                );
            } else {
                $where = array(
                    'class' => array('in',$class),
                    'status' => $status,
                );
            }
            var_dump($where);
			$res = $this->holidayDB->where($where)->field('id, title, time, status')->select();
			$this->ajaxReturn(array(
				'status' => 200,
				'data' => $res,
				'info' => 'success',
				'version' => '1.0',
			));
    	} else {
    		$this->ajaxReturn(array(
    			'status' => 400,
    			'info' => 'password wrong',
    			'version' => '1.0'
    		));
    	}   

		
	}
    //获取学生班级
    /**
     * @param $stuid 学生学号
     *
     * @return $class 学生班级号
     */
    private function getClassByStuid($stuid) {
    	$class = M('user')->where(array('stuid' => $stuid))->field('class')->find();
        return $class['class'];
    }
    //判断一个学生是不是班委
    /**
     * @param $stuid 要检验的学号 
     */
    private function checkIsMonitor($stuid) {
    	$character = M('user')->where(array('stuid' => $stuid))->field('character')->find();
    	return $character['character'] == 'monitor' ? true : false; 
    }
     //判断一个user是不是辅导员
    /**
     * @param $stuid 要检验的学号 
     */
    private function checkIsTeacher($stuid) {
    	$character = M('user')->where(array('stuid' => $stuid))->field('character')->find();
    	return $character['character'] == 'teacher' ? true : false; 
    }
    /**
     * @param $stuid 要检验的学号
     *
     * @param $password 传过来的密码
     */
    private function checkPassword($stuid, $password) {
    	$user = M('user')->where(array('stuid' => $stuid, 'idnum' => $password))->find();
        if($user != null) {
            return true;
        } else {
            return false;
        }

    }
    /**
     * @param $id 请假的id号
     *
     * @param $column 需要查找的字段值
     */
    private function getHolidayColumn($id,$column) {
        $res = $this->holidayDB->where(array('id' => $id))->field($column)->find();
        return $res;
    }
}