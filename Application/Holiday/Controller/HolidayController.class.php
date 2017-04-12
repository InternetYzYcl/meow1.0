<?php
namespace Holiday\Controller;

use Think\Controller;

class HolidayController extends Controller {
    public function index(){
		echo "meow";	
    }
    /**
     * @param $json 申请请假的详细信息 
     */
    public function apply() {
    	$json = array(
    		'stuid' => '2015210342',
    		'password' => '23155X',
    		'title' => '我要请假',
    		'aim' => '事假',
    		'detail' => '周一上午5,6节课 我要睡觉！！！！！！',
    		'course' => '信号与系统',
    		'time' => 
    			json_encode(array(
					'2017-04-10',
					'2017-04-10',
				)),
    		'count' => '2',
    		'certifier' => 
	    		json_encode(array(
					'stuid' => '2015210355',
					'name' => '杨周',
					'phone' => '110',
				)),
    	);

    	// $data = I('post.data');
    	$data = json_encode($json);
    	$info = json_decode($data,true);
    	$info['status'] = 0;
    	$info['class'] = $this->getClassByStuid($info['stuid']);
    	$db = M('holiday');
    	// var_dump(json_decode($data,true));
    	if($db->add($info)) {
    		$return = array(
    			'status' => 200,
    			'info' => 'success',
    			'version' => '1.0,'
    		);
    		$this->ajaxReturn($return);
    	}

    }
    //审核
    public function verify(){
		$data = array(
			'verifier' => 'teacher', 
			// 'verifier' => 'monitor',
			'status' => '2',
			// 'status' => '3',
			'evaluate' => '喵喵喵？？？',
		);
		if($data['verifier'] !='teacher')
			
			$this->ajaxReturn(array(
				'status' => 403,
				'info' => 'param verifier invalid',
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
    public function getMyHoliday() {}
    //通过id获取某条请假信息的全部内容
    /**
     * @param $requestid 请假id号
     *
     * @return $data 某条假的详细信息
     */
    public function getHolidayById() {}

    //班委获得该班级所有请假信息
    /**
     * @param $stuid 班委的学号
     *
     * @return $data 该班级所有请假的简略信息
     */
    public function getClassHoliday() {
    	$stuid = ''; //班委的学号
    	if($this->(checkIsMonitor($stuid))) {

    	} else {
    		//不是班委
    		$this->ajaxReturn(array(
    				'status' => 403,
    				'info' => 'stuid not monitor,',
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
    private function getClassByStuid($stuid) {}
    //判断一个学生是不是班委
    /**
     * @param $stuid 要检验的学号 
     */
    private function checkIsMonitor($stuid) {}
}