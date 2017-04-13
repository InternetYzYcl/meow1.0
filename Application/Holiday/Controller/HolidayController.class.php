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
   //  	$json = array(
   //  		'stuid' => '2015210342',
   //  		'password' => '23155X',
   //  		'title' => '我要请假',
   //  		'aim' => '事假',
   //  		'detail' => '周一上午5,6节课 我要睡觉！！！！！！',
   //  		'course' => '信号与系统',
   //  		'time' => 
   //  			json_encode(array(
			// 		'2017-04-10',
			// 		'2017-04-10',
			// 	)),
   //  		'count' => '2',
   //  		'certifier' => 
	  //   		json_encode(array(
			// 		'stuid' => '2015210355',
			// 		'name' => '杨周',
			// 		'phone' => '110',
			// 	)),
			// 'status' => '0',
   //  	);

    	$data = json_encode(I('post.data'),true);
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
    			'status' => 400,
    			'info' => 'password wrong',
    			'version' => '1.0'
    		));
    	}

    	
    
    	

    }
    //取消请假 
    public function cancleHoliday() {
    	$data = json_decode(I('post.data'),true);
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
    			'status' => 400,
    			'info' => 'password wrong',
    			'version' => '1.0'
    		));
    	}

    }
    //班委审核
    public function verifyByMonitor() {
    	$data = json_decode(I('post.data'),true);
    	$stuid = $data['stuid'];
    	$password = $data['password'];
    	if($this->checkPassword($stuid,$password) || $this->checkIsMonitor($stuid)) {
    		$status = $data['agree'] ? 3 : 2;    			//通过是3 不通过是2
    		$evaluate = $data['evaluate'];
    		if(!$this->holidayDB->save(array('status' => $status,'evaluate_monitor' => $evaluate))) {
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
    			'status' => 400,
    			'info' => 'password wrong or student not monitor',
    			'version' => '1.0'
    		));
    	}

    }
    //辅导员审核
    public function verifyByTeacher() {
    	$data = json_decode(I('post.data'),true);
    	$stuid = $data['stuid'];
    	$password = $data['password'];
    	if($this->checkPassword($stuid,$password) || $this->checkIsTeacher($stuid)) {
    		$status = $data['agree'] ? 4 : 5;    			//通过是5 不通过是4
    		$evaluate = $data['evaluate'];
    		if(!$this->holidayDB->save(array('status' => $status,'evaluate_teacher' => $evaluate))) {
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
    			'status' => 400,
    			'info' => 'password wrong or student not teacher',
    			'version' => '1.0'
    		));
    	}
    }
    //获取单一学生所有请假信息
    /**
     * @param $stuid 学生学号
     *
     * @return $data 学生所有请假的 简略信息
     */
    public function getMyHoliday() {
    	$data = json_decode(I('post.data'),true);
    	$stuid = $data['stuid'];
    	$password = $data['password'];
    	if($this->checkPassword($stuid,$password)) {
    		$res = $this->holidayDB->where(array('stuid' => $stuid))->field('id, title, time, status')->select();
	    	$this->ajaxReturn(array(
    			'status' => 200,
    			'info' => 'success',
    			'data' => $res,
    			'version' => '1.0' 
    		));
    	} else {
    		$this->ajaxReturn(array(
    			'status' => 400,
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
    	$data = json_decode(I('post.data'),true);
    	$stuid = $data['stuid'];
    	$password = $data['password'];
    	if($this->checkPassword($stuid,$password)) {
	    	$res = $this->holidayDB->where(array('requestid' => $requestid))->field('id',true)->select();
	    	$this->ajaxReturn(array(
    			'status' => 200,
    			'info' => 'success',
    			'data' => $res,
    			'version' => '1.0' 
    		));
    	} else {
    		$this->ajaxReturn(array(
    			'status' => 400,
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
    	$data = json_decode(I('post.data'),true);
    	$stuid = $data['stuid'];
    	$password = $data['password'];
    	if($this->checkPassword($stuid,$password) || $this->checkIsMonitor($stuid)) {
    		
			$class = getClassByStuid($stuid);
			$res = $this->holidayDB->where(array('class' => $class))->field('id, title, time, status')->select();
			$this->ajaxReturn(array(
				'status' => 200,
				'info' => 'success',
				'data' => $res,
				'version' => '1.0' 
			));

    	} else {
    		$this->ajaxReturn(array(
    			'status' => 400,
    			'info' => 'password wrong or student not monitor',
    			'version' => '1.0'
    		));
    	}

    	
    }
    //辅导员获取所有班级请假情况
    public function getMajorHoliday() {
    	$data = json_decode(I('post.data'),true);
    	$stuid = $data['stuid'];
    	$password = $data['password'];
    	if($this->checkPassword($stuid,$password)) {
    		
    		$class = M('class')->where(array('t_id' => $stuid))->field('class')->select();
			
			$res = $this->holidayDB->where(array('class' => $class))->field('id, title, time, status')->select();
			$this->ajaxReturn(array(
				'status' => 200,
				'data' => $res,
				'info' => 'success',
				'version' => '1.0',
			));,
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
    	return $class = M('user')->where(array('stuid' => $stuid))->field('class')->find();
    }
    //判断一个学生是不是班委
    /**
     * @param $stuid 要检验的学号 
     */
    private function checkIsMonitor($stuid) {
    	$character = M('user')->where(array('stuid' => $stuid))->field('character')->find();
    	return $character == 'monitor' ? true : false; 
    }
     //判断一个user是不是辅导员
    /**
     * @param $stuid 要检验的学号 
     */
    private function checkIsTeacher($stuid) {
    	$character = M('user')->where(array('stuid' => $stuid))->field('character')->find();
    	return $character == 'teacher' ? true : false; 
    }
    /**
     * @param $stuid 要检验的学号
     *
     * @param $password 传过来的密码
     */
    private function checkPassword($stuid, $password) {
    	$user = M('user')->where('stuid' => $stuid, 'password' => $password)->find();
    	return $user ? true : false;

    }
}