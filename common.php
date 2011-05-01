<?php

	function echoData($data)
	{
		echo $data . "<br>";
	}

	function well_print($input){
		echo "<pre>";
		print_r($input);
		echo "</pre>";
	}

	function createTPLPath()
	{
		global $THEME_PATH , $WEBROOT;

		//require_once('../session.php');

		return $WEBROOT . $THEME_PATH . "IE2";
	}

	function fetchTemplate($tpl, $location)
	{
		global $HOME_PATH, $THEME_PATH;

		$tpl->assign("tpl_path", createTPLPath() );
		//目前強制使用IE2...
		return $tpl->fetch($HOME_PATH . $THEME_PATH . "IE2" . $location);
	}

	function assignTemplate($tpl, $location){
		global $HOME_PATH, $THEME_PATH, $WEBROOT;
		$tpl->assign("tpl_path", createTPLPath() );
		$tpl->assign("webroot", $WEBROOT);
		$tpl->display($HOME_PATH . $THEME_PATH . "IE2" . $location);
	}
	/*
	 * 這裡放一些可以拿到一些課程 info 的 API
	 * by rja
	 */

	/* author: rja
	 * 回傳課程名稱
	 * 有給 begin_course_cd 就查該門課，沒給就自己拿 session 裡的 begin_course_cd
	 */
	function db_getCourseName($begin_cd= null){
		if (empty($begin_cd)){
			$begin_cd = $_SESSION['begin_course_cd'] ;
		}
	    $find_course_name_sql = "select begin_course_name from begin_course where begin_course_cd = $begin_cd; ";
		$courseName = db_getOne($find_course_name_sql);
		return $courseName;
	}

	/* author: rja
	 * 回傳使用者姓名 personal_name
	 * 有給 personal_id 就查該人姓名，沒給就自己拿 session 裡的 personal_id
	 */
	function db_getPersonalName($personal_id= null){
		if (empty($personal_id)){
			$personal_id = $_SESSION['personal_id'] ;
		}
	    $Q1 = "select personal_name from personal_basic where personal_id = $personal_id; ";
		$personalName = db_getOne($Q1);
		return $personalName;
	}
	/* author: rja
	 * 回傳 personal_basic 裡某欄位
	 * 有給 personal_id 就查該人姓名，沒給就自己拿 session 裡的 personal_id
	 */
	function db_getPersonalBasic($personal_id = null, $column= null){
		if (empty($column)) print 'db_getPersonalBasic: no column name ';
		if (empty($personal_id)){
			$personal_id = $_SESSION['personal_id'] ;
		}
		
	    $Q1 = "select $column from personal_basic where personal_id = $personal_id; ";
		$thisColumn = db_getOne($Q1);
		return $thisColumn;
	}

	/* author: rja
	 * 回傳 該課程裡的老師及助教資料
	 */
	function db_getTeacherAndTAList($begin_cd=null){
		if (empty($begin_cd)){
			$begin_cd = $_SESSION['begin_course_cd'] ;
		}

		$list_tea_And_TA = "select p.personal_id, p.personal_name,p.photo, p.email, p.tel, r.login_id, r.role_cd 
			from personal_basic p, register_basic  r 
			where p.personal_id=r.personal_id
			and r.personal_id in 
			( select teacher_cd 
			from teach_begin_course 
			where begin_course_cd={$begin_cd}) 
			";

		
		$result = db_getAll($list_tea_And_TA);
		return $result;
	}

	/* author: rja
	 * 回傳 該課程裡全部修課學生的資料
	 */
	function db_getAllStudentInfo($begin_cd = null){
		if (empty($begin_cd)){
			$begin_cd = $_SESSION['begin_course_cd'] ;
		}

		$Q1 = "SELECT * 
			FROM register_basic r, personal_basic p, take_course t 
			WHERE t.begin_course_cd='".$_SESSION[begin_course_cd]."' and  t.personal_id=r.personal_id  and 
			r.personal_id = p.personal_id and r.role_cd='3'";

		
		$result = db_getAll($Q1);
		return $result;
	}

	/*author: lunsrot
	 * data: 2007/02/10
	 */
	function db_query($sql){
		global $debug;
		if($debug)
		{
			global $DB_CONN;
			global $DB_DEBUG;
			global $WEBROOT;
			//echo $sql;
			$r = $DB_CONN->query($sql);
			if(PEAR::isError($r))
			{
				if($DB_DEBUG)
					die($_SERVER['PHP_SELF'] . ': '.$r->getDebugInfo());
				else
					header("Location:".$WEBROOT."error.html");
			}
			//return $debug ? $r : $rows;
			return $r ;
		}
		else
		{
			//echo $sql;
			//http://www.phpclasses.org/browse/file/24896.html [+]
			//Usage example of dqml2tree class 
			//$sql = "select date,num from popularity_noip where date = '2011-04-13'";
			//		$sql .= ' and num > 10';
			$query2tree = new dqml2tree($sql);
			$sql_tree = $query2tree->make(); 
			//print_r($sql_tree);
			$rows = adapter($sql_tree);
			//die("::");
			//die(print_r($rows));
			return $rows;
		}
	}
        
	/*author: lunsrot
	 * date: 2007/11/29
	 */
    function db_getOne($sql){

		global $DB_CONN;
		global $DB_DEBUG;
                global $WEBROOT;
		$r = $DB_CONN->getOne($sql);
		if(PEAR::isError($r))
        {
            if($DB_DEBUG)
                die($_SERVER['PHP_SELF'] . ': '.$r->getDebugInfo());
            else
                header("Location:".$WEBROOT."error.html");
        }
        return $r;
	}

	/*author: lunsrot
	 * date: 2007/11/29
	 */
	function db_getRow($sql){
	  	global $DB_CONN;
		global $DB_DEBUG;
                global $WEBROOT;
		$r = $DB_CONN->getRow($sql, DB_FETCHMODE_ASSOC);
		if(PEAR::isError($r))
        {
            if($DB_DEBUG)
                die($_SERVER['PHP_SELF'] . ': '.$r->getDebugInfo());
            else
                header("Location:".$WEBROOT."error.html");
        }
        return $r;
	}

	/* author: rja
	 * date: 2008/3/10
	 */
	function db_getAll($sql){
		global $DB_CONN;
		global $DB_DEBUG;
                global $WEBROOT;
		$r=Array();
		$r = $DB_CONN->getAll($sql, null, DB_FETCHMODE_ASSOC) ;
		if(PEAR::isError($r))
        {
            if($DB_DEBUG)
                die($_SERVER['PHP_SELF'] . ': '.$r->getDebugInfo());
            else
                header("Location:".$WEBROOT."error.html");
        }
		return $r;
	}



	/*author: rja
	 * date: 2008/3/10
	 */
	function flatArray ($arr)
	{
		$returnArray = Array();
		foreach( $arr as $key => $value ) {
			foreach( $value as $innerKey => $innerValue ) {
				$returnArray[] =  $innerValue ;
			}
		}
		return $returnArray;
	}


	/*author: lunsrot
	 *date: 2007/04/30
	 */
	function getCurTime(){
		$tmp = gettimeofday();
		$time = getdate($tmp['sec']);
		$date = $time['year']."-".$time['mon']."-".$time['mday']." ".$time['hours'].":".$time['minutes'].":".$time['seconds'];
		return $date;
	}

	/*author: lunsrot
	 * date: 2007/06/19
	 */
	function createPath($path){

		$old_mask = umask(0);
		if($path[ strlen($path) - 1 ] == '/')
			$path[ strlen($path) - 1 ] = "\0";
		$tmp = explode("/", $path);
		$target = "";
		for($i = 0 ; $i < count($tmp) ; $i++){
			$target .= $tmp[$i] . "/";
			if(!is_dir($target))
			  mkdir($target, 0775);
			 
		}
		umask($old_mask);
	}

	/*author: lunsrot
	 * date: 2007/09/06
	 */
	function getTeacherId(){
		global $DB_CONN;
		if($_SESSION['role_cd'] == 1)
			return $_SESSION['personal_id'];
		$id = $DB_CONN->getOne("select teacher_cd from `teach_aid` where if_aid_teacher_cd=$_SESSION[personal_id]");
		if(!is_numeric($id)){
			header("location:../identification_error.html");
			exit(0);
		}
		return $id;
	}


	/*author: sad man
	 * date: sad time
	 */	
	function update_status ( $status_now ) {

		$status_now = addslashes( $status_now );
		//問卷
		//¥ý§PÂ_¬O§_¦b
		$sql = "SELECT online_cd FROM online_number WHERE personal_id='".$_SESSION['personal_id']."' and host='".$_SESSION['personal_ip']."'";

		$res = db_query($sql);
		$count = $res->numRows();		
		if($count != 0){
			$row_online = $res->fetchRow(DB_FETCHMODE_ASSOC);
			$sql = "UPDATE online_number SET status='". $status_now  ."' WHERE online_cd='".$row_online['online_cd']."'";
			$res = db_query($sql);
		}							
	}

	//function content by rja
	function sync_stu_course_data( $begin_course_cd , $personal_id )
	{
		//作業同步
		$homework_no =  db_getAll(" select homework_no from homework where begin_course_cd = $begin_course_cd ;");
		//print_r($homework_no);

		foreach ( $homework_no as $value){
			//if($value['public'] == 1 || $value['public'] == 3)

			$sql = " insert ignore into handin_homework (begin_course_cd, homework_no, personal_id) 
				values ($begin_course_cd, {$value['homework_no']}, $personal_id ); ";
			db_query($sql);

			$number_id =  db_getOne(" select number_id from course_percentage where begin_course_cd = $begin_course_cd and  percentage_type= 2 and percentage_num  = {$value['homework_no']} ;");

			$sql2 = " insert ignore into course_concent_grade (begin_course_cd, number_id, percentage_type, percentage_num, student_id, concent_grade )
				values ($begin_course_cd,  $number_id , 2,  {$value['homework_no']}, $personal_id, 0 );";
			db_query($sql2);
		}
		//end of 作業同步



		//問卷同步
		$survey_no =  db_getAll("select survey_no from online_survey_setup where survey_target = $begin_course_cd ;");

		//print_r($homework_no);

		foreach ( $survey_no as $value){
			$sql = " insert ignore into survey_student (survey_no, personal_id)
				values ( {$value['survey_no']}, $personal_id );";
			db_query($sql);
		}
		//end of 問卷同步


		//點名同步 

		$roll_no =  db_getAll("select *  from roll_book  where begin_course_cd = $begin_course_cd group by roll_id;");

		//print_r($homework_no);


		//需要 roll_book_status_grade 裡，查各種出席狀態代表的分數，例如出席100分
		$roll_book_status_grade =  db_getAll("select * from roll_book_status_grade  where begin_course_cd = $begin_course_cd ;");
		//print_r($roll_book_status_grade);
		//如果老師沒設出席狀態所代表的分數，就用預設值
		//依照 ../Roll_Call/newRollCallSave.php 的規定所訂
		if(empty($roll_book_status_grade)){
			$roll_book_status_grade[] = Array('status_id' => 0, 'status_grade' => 100 ); 
			$roll_book_status_grade[] = Array('status_id' => 1, 'status_grade' => 0 ); 
			$roll_book_status_grade[] = Array('status_id' => 2, 'status_grade' => 80 ); 
			$roll_book_status_grade[] = Array('status_id' => 3, 'status_grade' => 80 ); 
			$roll_book_status_grade[] = Array('status_id' => 4, 'status_grade' => 100 ); 
			$roll_book_status_grade[] = Array('status_id' => 5, 'status_grade' => 100 ); 
		}

		foreach ( $roll_no as $value){
			$sql = " insert ignore into roll_book (begin_course_cd, personal_id, roll_id, roll_date,state)
				values ($begin_course_cd, $personal_id, {$value['roll_id']},'{$value['roll_date']}', 5 );";
			db_query($sql);
			//print $sql . "\n";
			// 5 是代表其它類 (還沒決定預設值，0是出席，1是缺席)
			$this_state =  5 ;
			$this_concent_grade = $roll_book_status_grade["$this_state"]['status_grade'];

			$number_id =  db_getOne(" select number_id from course_percentage where begin_course_cd = $begin_course_cd and  percentage_type = 3 and percentage_num  = {$value['roll_id']} ;");


			$sql2 = " insert ignore into course_concent_grade (begin_course_cd, number_id, percentage_type, percentage_num, student_id, concent_grade )
				values ($begin_course_cd,  $number_id , 3,  {$value['roll_id']}, $personal_id, $this_concent_grade );";
			db_query($sql2);
			//print $sql2 . "\n";
		}

		//end of 點名同步 

	}	  

    // author : Samuel
    // modify time : 2009/11/02 
    // 因為調整資料庫中begin_course的欄位 = course_stage
    // 所以要把資料還原成原本的資料 
    // 其中 一共有四個bit 第一個bit指的是高中(10)、第二個bit為高職(23)、第三個bit為國中(30)、第四個bit為國小(40)
    function course_stage_decode($begin_course_cd)
    {
        $sql = "SELECT course_stage FROM begin_course WHERE begin_course_cd={$begin_course_cd}";
        $course_stage_string = db_getOne($sql);

        $course_stage = array(); 
        if($course_stage_string[0] == 1)
            $course_stage[] = 10;

        if($course_stage_string[1] == 1)
            $course_stage[] = 20;

        if($course_stage_string[2] == 1)
            $course_stage[] = 30;

        if($course_stage_string[3] == 1)
            $course_stage[] = 40;

        return $course_stage;
    }
    //end modify
	
	// author : q110185
    // modify time : 2009/11/20
    // 因為高師大欄位的轉換需要
    // 所以要把資料還原成原本的資料 
    // 其中 一共有四個bit 第一個bit指的是高中(10)、第二個bit為高職(23)、第三個bit為國中(30)、第四個bit為國小(40)
	 function course_stage_decode_str($course_stage_string='0000')
    {
        $course_stage = array(); 
        if($course_stage_string[0] == 1)
            $course_stage[] = 10;

        if($course_stage_string[1] == 1)
            $course_stage[] = 20;

        if($course_stage_string[2] == 1)
            $course_stage[] = 30;

        if($course_stage_string[3] == 1)
            $course_stage[] = 40;

        return $course_stage;
    }
    //end modify
	function adapter(&$sql)
	{
		global $debug_language;
		$key = array_keys($sql['SQL']);
		$key = $key[2];
		if($debug_language)
		{
			//echo $key;
			//print_r($sql);
		}
		switch($key)
		{
		case 'SELECT':
			$rows = hbase_select($sql['SQL'][$key],$key);
			break;
		case 'INSERT':
			$rows = hbase_insert($sql['SQL'][$key],$key);
			break;
		case 'UPDATE':
			$rows = hbase_update($sql['SQL'][$key],$key);
			break;
		}
		return $rows;
	}
	function hbase_update(&$sql,$action)//,$table,$row,$family)
	{
		global $client,$debug_language;
		if($debug_language)
		{
			//print_r(current(($sql['VALUES'])));
			//print_r(current(next($sql['INTO'])));
			//print_r($fileds);
			//print_r($values);
			//print_r($sql);
			//die();
		}
		$table = hbase_table($sql,$action);

		$row = listtables($table);
		//die(print_r($row));
		//只要給row就好
		//set key for sync 2 array
		//$sync = 0;
		$values = array_values((($sql['SET'])));
		$fileds = array_values(current(($sql['WHERE'])));
		if($debug_language)
		{
			//(print_r($values));
			//die(print_r($fileds));
		}
		foreach ( $sql['WHERE'] as $k => $v)
		{
			if(!is_array(getfirstelement($v)))
			{
				$val = isset($filed) ? current($v) : "";
				$filed = isset($filed)?$filed:current($v);
			}
			else{
				$filed = current($v);
				$val = next($v);
				$filed = $filed['FIELD'];
				$val = $val['VAL'];
			}
			$primary= @in_array($filed,$row['primary']) 
				? "|" .  $val : $primary;
		}
		unset($val);
		unset($filed);
		//die($primary."<><>");
		foreach($sql['SET'] as $k=>$v)
		{
			//>一個condition
			//die(print_r(getfirstelement($v)));
			if(!is_array(getfirstelement($v)))
			{
				$val = isset($filed) ? current($v) : "";
				//die(print_r($val) . "::::");
				if(isset($filed))
				{
					$mutations[] = 
						new Mutation( array(
							'column' => $filed . ':',
							'value' => $val
						) );
				}
				$filed = isset($filed)?$filed:current($v);
			}
			else{
				$filed = current($v);
				$val = next($v);
				$filed = $filed['FIELD'];
				$val = $val['VAL'];
			$mutations[] = 
				new Mutation( array(
					'column' => $filed . ':',
					'value' => $val
					) );
			}
			/*if(isset($filed['FIELD']))
			{
			}*/
			//echo("filed=".var_dump($filed));
			//echo("val=".var_dump($val));
				//$filed = isset($con['FIELD']) ? $con['FIELD'] : "";
				//if(isset($con['VAL'])
						//echo $k . "|".$val . "|" . $filed . "\n<br / >";
			//echo $primary . "++";
			/*
				if(@in_array($filed,$row['primary']))
				{
					//$primary = isset($status) ? current($v) : $primary;
					$primary = isset($status) ? $val : $primary;
					$status = true;
				}
				else unset($status);
			 */
				//k is equetion
			//}

		}
			//die($filed . "|" . $val . "|" . $primary);
		//die(print_r($mutations));
		$client->mutateRow( $table, $primary, $mutations );
		//end

	}

/*
 * @param sql
 * @param action
 * return flan tables
 */ 	
	function hbase_table(&$sql,$action)
	{
		global $client, $debug_language;
		$table = "";
		if($debug_language)
		{
			//echo $key;
			//die(print_r($getT));
		}
		//Array ( [INTO] => Array ( [0|*INSERT] => Array ( [TABLE] => popularity_noip ) [1|*INSERT] => Array ( [INTO] => Array ( [0|*INTO] => Array ( [FIELD] => date ) [1|*INTO] => Array ( [FIELD] => num ) ) ) )
		switch($action)
		{
			case "UPDATE":
				$getT = end ( $sql );
				break;
			case "INSERT":
				$getT = current($sql['INTO']);
				break;
			case "SELECT":
				//for two table
				if( $sql['FROM'] > 1)
				{
					foreach( $sql['FROM'] as $v )
					{
						//alias table as key
						$getT[] = array(current(next($v))=>
							current(prev($v))
						);
						//$getT[] = current(($v));
					}
				}
				else
				{
				//for one table
				$getT = current( $sql['FROM'] );
				}
				break;
		}
		//for two table
		if( $sql['FROM'] > 1)
		{
			foreach($getT as $k=>$v)
			{
				//list($alias,$fulltable) = $v;
				//$table[$alias] = $fulltable;
				$alias = array_search( current($v),$v);
				$table[$alias] = current($v);
				//$table[] = current($v);
			}
		}
		else{
			foreach($getT as $k=>$v)
				$table = $v;
		}
		if($debug_language)
		{
			//print_r($getT);
			//die(print_r($table));
		}
		return $table;
	}
	function hbase_insert(&$sql,$action)//,$table,$row,$family)
	{
		global $client,$debug_language;
		$table = hbase_table($sql,$action);
		$row = listtables($table);
		if($debug_language)
		{
			//die(print_r($row));
			//die(print_r($sql));
		}
		//只要給row就好
		//set key for sync 2 array
		$sync = 0;
		$values = array_values(current(($sql['VALUES'])));
		$fileds = array_values(current(next($sql['INTO'])));
		if($debug_language)
		{
			//print_r(current(($sql['VALUES'])));
			//print_r(current(next($sql['INTO'])));
			//print_r($fileds);
			//print_r($values);
		}
		foreach($values as $k=>$v)
		{
			if(!is_array(getfirstelement($v)))
			{
				//$val = isset($filed) ? current($v) : "";
				//$filed = isset($filed)?$filed:current($v);
			}
			else{
				//for($i = 0;$i<$sync; $i++)
				//	$filed = next($v);
			}
			$filed = current($fileds[$k]);
			$val = current($v);
			if(@in_array($filed,$row['primary'])) 
			{
				//for($i = 0;$i<$sync; $i++)
				//	$val= next($values);
				$primary .= ( "|" . $val);
			}
			if($debug_language)
			{
				//print_r($values);
				//print_r($v);
				echo "filed=" . $filed . "\t"
					. "val=" . $val . "\t"
					. "primary=" . $primary . "\n";	
			}

			$mutations[] = 
				new Mutation( array(
					'column' => $filed . ':',
					'value' => $val
				) );
		}
		//die(print_r($mutations));
		$client->mutateRow( $table, $primary, $mutations );
		//end

	}
	function hbase_select(&$sql)//,$table,$row,$family)
	{
		global $client,$debug;
		//only one table;
		//print_r($client);
		if($debug)
		{
			//print_r($sql);
		}
		//echo "EEEE";
		foreach($sql['FROM'] as $k=>$v)
			$table[] = $v;
		//condition is primary key?
		//print_r($table);
		$row = listtables($table);
		//die(print_r($row) );
		//$row['primary'] = (array('date'));
		//die();
		$primarys = hbase_table_getprimary($row,$sql['WHERE']);
		if(is_array($sql['WHERE']))
		{
			foreach($sql['WHERE'] as $k=>$v)
			{
				//跑where的判斷句
				//foreach($v as $col=>$con)
				//{
				//>一個condition
				//case 1 like '2011-04%'
				//stradegy get all table and using preg for filter
				//die(print_r(getfirstelement($v)));
				if(!is_array(getfirstelement($v)))
				{
					$val = isset($filed) ? current($v) : "";
					$filed = isset($filed)?$filed:current($v);
				}
				else{
					//在join的時候很複雜
					//hbase_select_con($k,$v);
					$filed = current($v);
					$val = next($v);
					$filed = $filed['FIELD'];
					$val = $val['VAL'];
				}
			/*if(isset($filed['FIELD']))
			{
			}*/
				//echo("filed=".var_dump($filed));
				//echo("val=".var_dump($val));
				//$filed = isset($con['FIELD']) ? $con['FIELD'] : "";
				//if(isset($con['VAL'])
				if( $debug )
				{
					//echo $k . "|".$val . "|" . $filed . "\n<br / >";
				}
				$primary= @in_array($filed,$row['primary']) 
					? "|" .  $val : $primary;
				//echo $primary . "++";
			/*
				if(@in_array($filed,$row['primary']))
				{
					//$primary = isset($status) ? current($v) : $primary;
					$primary = isset($status) ? $val : $primary;
					$status = true;
				}
				else unset($status);
			 */
				//k is equetion
				//}
			}
			//die("DDD");
		}
		else//if no WHERE condition
		{
		}
		if($debug)
		{
			//echo($k . "||" . $primary."||}|" . $table);
		}
		//want
		//print_r(next(next($sql)));
/*
		foreach(array_keys($sql) as $k=>$v)
		{
			//array_push($column,$v);
			if( $v != "FROM" ||
				$v != "WHERE")
			$column[] = $v;
		}
 */
		//die($k . "LKLKLK");
		if(strpos($k,"LIKE") !== false)
		{
			//die("DDD".print_r($arr));
			//print_r($mutations);
			//Run a scanner on the rows we just created
			//echo( "Starting scanner...\n" );
			$mu = getmutation($sql,$table);
			/*
			foreach(end($sql) as $k=>$v)
			{
				if($v === "*")break;
				else $mu[] = $v;
			}
			if(!is_array($mu))
			{
				foreach($client->getColumnDescriptors( $table )
					as $k => $v)
					$mu[] = $k;
			}
			 */
			//die(print_r($mu));
			//$mu[] = "date";
			//$mu[] = "num";
			//$scanner = $client->scannerOpen( $table, "", array( "column:" ) );
			$scanner = $client->scannerOpen( $table, "", $mu);
			//print_r($scanner);
			try {
				while (true) 
				{
					//printRow( $client->scannerGet( $scanner ) );

					$arrs[] = $client->scannerGet( $scanner );
					if(!count(end($arrs)))break;
					//print_r( $client->scannerGet( $scanner ) );
				}
			} catch ( NotFound $nf ) {
				$client->scannerClose( $scanner );
				echo( "Scanner finished\n" );
			}
			//die(print_r($arrs));
			$pattern = array("|","%","'");
			$replace = array("\|",".+","");
			$primary = str_replace($pattern,$replace,$primary);//trim($primary,"'"));
			//echo "\n/$primary/\n"; 
			foreach($arrs as $k=>$v)
			{
				preg_match("/$primary/",$v[0]->row,$match,PREG_OFFSET_CAPTURE);
				//echo "preg_match(\"/$primary/\",".$v[0]->row.",$match,PREG_OFFSET_CAPTURE)";
				if(count($match))
				{
					$arr = $client->getRow($table, $match[0][0]);
					foreach ( $arr as $ks=>$vs )
					{
						foreach($vs->columns as $col=>$tc)
							$cell[substr($col,0,-1)] = trim($tc->value,"\'");
						$rows[] = $cell;
						unset($cell);
					}
				}
				unset($match);
			}
			//die(print_r($rows));

		}
		else if( strpos($k,"EQ") !== false )
		{
			$arr = $client->getRow($table, $primary);
			//$arr = $client->getRow($table, "|2011-04-11");
			//foreach ( $arr as $k=>$TRowResult  ) {
			//	// $k = 0 ; non-use
			//	// $TRowResult = TRowResult
			//	//printTRowResult($TRowResult);
			//	print_r($TRowResult);
			//}
			//die("{".$primary."}");
			//echo "}}";
			//$arr = $client->get($table, $row , $column);
			//TRowResult Object ( [row] => |2011-04-11 [columns] => Array ( [date:] => TCell Object ( [value] => 2011-04-11 [timestamp] => 1303253059997 ) [num:] => TCell Object ( [value] => 66 [timestamp] => 1303253059997 ) ) ) 
			//die(print_r($client->getColumnDescriptors( $table )));
			//多比data
			foreach ( $arr as $k=>$v )
			{
				//$row[$k] = $v;
				foreach($v->columns as $col=>$tc)
				{
					if($debug)
					{
						//echo "value = {$tc->value} , <br> ";
						//echo "timestamp = {$tc->timestamp} <br>";
					}
					//filte
					//if(array_key_exists($k)||array_key_exists('*'))
					$cell[substr($col,0,-1)] = trim($tc->value,"\'");
				}
				$rows[] = $cell;
				unset($cell);
			}
		}
		else if( strpos($k,"AND") !== false )
		{
			if ( $debug )
			{
				//print_r( $primarys );
				//print_r( ($table));
			}
			//Array([A]=>news[B]=>news_target)
			$tablewithalias = hbase_table($sql, "SELECT");
			//die(print_r($tablewithalias));
			$fakesql = array(array("FILED"=>"*"));
			$rows = hbase_table_getval($fakesql,$table,&$sql);
			//Array([A]=>*[B]=>course_type)
			$search = hbase_select_con($sql,$table,"SELECT");
			//die(print_r($search));
			//先寫死，暫時沒有範例
			//先求table數
			//foreach($rows as $k=>$row)
			//{
			//	foreach($row as $cell=>$v)
			//	{
			//		//取出每個的條件(for primary)
			//		//$primarys[$k]
			//		//在這裡要兩個的key是一樣

			//	}
			//}
			//先去看說那個比較多，在去每一比比較
			list($news,$news_target) = $rows;
			//list($news_target,$news) = $rows;
			//print_r($news);
			//die(print_r($news_target));
			//die(print_r($rows));
			foreach( $news as $k=>$v)
			{
				foreach( $news_target as $n )
				{
					//寫死的判斷式
					//[1|*AND]=>
					//Array([0|!EQ]=>
					//Array([TABLE]=>A[FIELD]=>news_cd)
					//[1|!EQ]=>Array([TABLE]=>B[FIELD]=>news_cd))
					if($v['news_cd'] == $n['news_cd'])
					{
						//[0|*AND]=>
						//Array([0|!EQ]=>
						//Array([TABLE]=>B[FIELD]=>role_cd)
						//[1|!EQ]=>Array([VAL]=>0))
						if($n['role_cd'] == 0)
						{
							//[WHERE]=>
							//Array([0|*OR]=>Array([0|!EQ]=>
							//Array([FIELD]=>course_type)
							//[1|!EQ]=>Array([VAL]=>1))[1|*OR]=>
							//Array([0|!EQ]=>Array([FIELD]=>course_type)
							//[1|!EQ]=>Array([VAL]=>2))...
							$course_type = array(1,2,3,4,6);
							if(in_array($n['course_type'],$course_type))
							{
								//Array([A]=>news[B]=>news_target)
								//Array([A]=>*[B]=>course_type)
								//foreach($search as $alias => $needle)
								//{
								//	//寫死
								//	//可能要建立map的關係在這裡鮮芋仙知道
								//	//if($needle == "*")
								//}
								//array_push($v,$n['course_type']);
								$result[] = array_merge(
									$v,array('course_type'=>
									$n['course_type']
								));
								//$result[] = $v['news_cd'];
								//echo( $v['news_cd']);
							}
						}
					}
				}
				//print_r($n);
			}
			//echo "EEeded";
			//都寫死
			foreach(hbase_getcol($sql,"ORDER") as $v)
			{
				if( $v['DESC'] === "DESC")
				{
					if($v['FIELD'] == "d_news_begin")
						usort($result,array("sortbyu","DateSort"));
					if($v['FIELD'] == "d_news_begin")
						usort($result,array("sortbyu","DateSort2"));
				}

			}
			$limit = (hbase_getcol($sql,"LIMIT")); 
			$limit_start = current($limit);
			$limit_start = $limit_start['VAL'];
			$limit_end = next($limit);
			$limit_end = $limit_end['VAL'];
			unset($rows);
			for(;$limit_start < $limit_end ;$limit_start++)
			{
				$rows[] = $result[$limit_start];
			}
			//die(print_r($rows));
			//$rows = $result;
		}
		else // no where condition
		{
			$mu = getmutation(&$sql,$table);
			//die(print_r($mu));
			//$mu['column'] = "num";
			$scanner = $client->scannerOpen( $table, "", $mu);
			//print_r($scanner);
			try {
				while (true) 
				{
					$arrs[] = $client->scannerGet( $scanner );
					if(!count(end($arrs)))break;
				}
			} catch ( NotFound $nf ) {
				$client->scannerClose( $scanner );
			}
			//die(":::");
			//die(print_r($arrs));
			foreach ( $arrs as $k=>$v )
			{
				//print_r($v);
				//$row[$k] = $v;
				foreach($v[0]->columns as $col=>$tc)
				{
					if($debug)
					{
						//echo "value = {$tc->value} , <br> ";
						//echo "timestamp = {$tc->timestamp} <br>";
					}
					$cell[substr($col,0,-1)] = trim($tc->value,"\'");
				}
				$rows[] = $cell;
				unset($cell);
			}

			//die(print_r($rows));
			//$rows = $arrs;

		}
		//print_r($rows);
		return isset($rows) ? $rows : array() ;
	}
	function insert($table, $row, $family, $value)
	{
		$mutations = array(
			new Mutation( array(
				'column' => $family,
				'value' => $value
			) )
		);
		$client->mutateRow( $table, $row, $mutations );
	}
	function listtables(&$table)
	{
		global $client;
		if(count($table) == 1)
		{
			//(print_r($table));
			$table = is_array($table) ? current($table) : $table;
			$descriptors = $client->getColumnDescriptors( ($table) );
			//$table = current($table);
		}
		else
		{
			foreach($table as $k=>$v)
			{
				$descriptors[] = $client->getColumnDescriptors( 
					current(current($v)));
				//$tables[current(current($v))] = current(next($v));
			}
		}
		//die(print_r($tables));
		asort( $descriptors );
		//die(print_r($descriptors));
		$primary = "PRIMARY KEY";
		$unique = "UNIQUE KEY";
		foreach ( $descriptors as $col ) {
			if( !isset ( $descriptors[0] ) )
			{
				$primary = (strpos($col->name,$primary)) === false ? $primary :
					preg_match("/(.+),?/",($col->name),$primatch);
				$unique = (strpos($col->name,$unique)) === false ? $unique:
					preg_match("/(.+),?/",($col->name),$unimatch);
			}
			else
			{
				foreach ( $col as $cols)
				{
					(strpos($cols->name,$primary)) === false ? $primary :
						preg_match("/(.+),?/",($cols->name),$primatch);
					(strpos($cols->name,$unique)) === false ? $unique:
						preg_match("/(.+),?/",($cols->name),$unimatch);
					//echo print_r($cols) . $primatch. "|" . $unimatch. "<br/>\n";
				}
				$primatchs[] = isset($primatch) ? preg_split("/[,\s]/",
					str_replace("PRIMARY KEY ","",$primatch[1])) : "";
				$unimatchs[] = isset($unimatch) ? preg_split("/[,\s]/",
					str_replace("UNIQUE KEY ","",$unimatch[1])) : "";
				//die(print_r($primatch) );//. print_r($unimatch));
				unset($primatch);
				unset($unimatch);
			}
		}
		if(isset($primatchs))
		{
			$primary = $unique = array();
			$primatch = $primatchs;
			$unimatch = $unimatch;
			foreach($primatch as $k=>$v)
			{
				//print_r($v);
				$primary[] = isset($v) ? preg_split("/[,\s]/",
					str_replace("PRIMARY KEY ","",$v[0])) : "";
			}
			foreach( $unimatch as $k=>$v)
			{
				$unique[]= isset($v) ? preg_split("/[,\s]/",
					str_replace("UNIQUE KEY ","",$v[0])) : "";
			}
		}
		else
		{
			$primary = isset($primatch) ? preg_split("/[,\s]/",
				str_replace("PRIMARY KEY ","",$primatch[1])) : "";
			$unique= isset($unimatch) ? preg_split("/[,\s]/",
				str_replace("UNIQUE KEY ","",$unimatch[1])) : "";
		}
		//			die(print_r($primatch) . print_r($unimatch));
		//die(print_r($primary) . print_r($unimatch));
		return array("primary"=>$primary,"unique"=>$unique);
	}
	function getfirstelement(&$arr)
	{
		$keys = array_keys( $arr );
		return $arr[$keys[0]];
	}
	function getmutation(&$sql,$table)
	{
		global $client, $debug;
		foreach(end($sql) as $k=>$v)
		{
			if($v === "*")break;
			else $mu[] = $v;
		}
		if(!is_array($mu))
		{
			foreach($client->getColumnDescriptors( $table )
				as $k => $v)
				$mu[] = $k;
		}
		if( $debug )
		{
			//print_r( $mu );
			//print_r( end($sql) );
			//echo $table;
		}
		return $mu;
	}
	/*
	 * what data did u want to fetch
	 * @param sql
	 * @param action
	 */
	function hbase_select_con(&$sql,$table,$action)
	{
		foreach( $sql as $k=>$v)
		{
			if(strpos($k,$action))
			{
				//tablename as a key
				$search[current($v)] = next($v);
			}
		}
		//die(print_r($table));
		return $search;
/*		if(strpos($k,"AND"))
			$k;
 */
	}
	function hbase_table_getprimary(&$row,&$sql)
	{
		//Array
		//(
		//    [primary] => Array
		//        (
		//            [0] => Array
		//                (
		//                    [0] => news_cd
		//                )
		//
		//            [1] => Array
		//                (
		//                    [0] => news_cd
		//                )
		//
		//        )
		//
		//    [unique] => Array
		//        (
		//        )
		//
		//)
		//print_r(current($row['primary']));
		if(!is_array(current($row['primary'])))//for only one table
		{
		}
		else
		{
			foreach( $row['primary'] as $v)
				//有多少table
			{
				foreach( $v as $p )//一個table中有幾個primary
				{
					$keys = array_search( $p, $sql); 
					//die($keys . "::::L" . print_r($v));
					//getfiledval($p,$sql);
					if( ($val = getParentStackComplete($p,$sql)) !== false){ 
						$vals[] = $val ;//getParentStackComplete("VAL",$sql); 
						//die(var_dump($val)); 
						//getfiledval($val,$sql);
						//拿出下一個val

					}
				}
			}
		}
		return $vals;
	}
	/*function array_search_recursive(&$search,&$haystack)
	{
		//$key = array();
		while(is_array($haystack))
		{
			if( $ass = array_search ( $search, $haystack))
			{
				$key["'" . $ass  . "'"] = $search;
				break;
			}
		}
	}*/
	//	function array_search_key( $needle_key, $array ) { 
	//		foreach($array AS $key=>$value){ 
	//			if($key == $needle_key) return $value; 
	//			if(is_array($value)){ 
	//				if( ($result = array_search_key($needle_key,$value)) !== false) 
	//					return $result; 
	//			} 
	//		} 
	//		return false; 
	//	}
	//	function my_array_search($needle, $haystack) {
	//		if (empty($needle) || empty($haystack)) {
	//			return false;
	//		}
	//
	//		foreach ($haystack as $key => $value) {
	//			$exists = 0;
	//			foreach ($needle as $nkey => $nvalue) {
	//				if (!empty($value[$nkey]) && $value[$nkey] == $nvalue) {
	//					$exists = 1;
	//				} else {
	//					$exists = 0;
	//				}
	//			}
	//			if ($exists) return $key;
	//		}
	//
	//		return false;
	//	}
	function getParentStackComplete($child, $stack) {
		$return = array();
		unset($flag);
		foreach ($stack as $k => $v) {
			if (is_array($v)) {
				// If the current element of the array is an array, recurse it 
				// and capture the return stack
				$stack = getParentStackComplete($child, $v);

				// If the return stack is an array, add it to the return
				if (is_array($stack) && !empty($stack)) {
					$return[$k] = $stack;
				}
			} else {
				// Since we are not on an array, compare directly
				//echo $flag . "<br />\n";
				if ( isset( $flag ) && $k == "VAL") 
				{
					$return[$k] = $v; 
					unset( $flag );
				}
				if ($v == $child) {
					// And if we match, stack it and return it
					$return[$k] = $child;
					//$return[$k] = $child;
					$flag = "VAL";
				}
			}
		}

		// Return the stack
		//( print_r($return) );
		return empty($return) ? false: $return;
	}
	function getfiledval($getPSC,&$sql)
	{
		//$getPSC = array("1|*AND"=>"");
		//$getPSC = array_pop($getPSC);
		print_r($getPSC);
		print_r($sql);
		print_r( fullArrayDiff($getPSC,$sql));
		die("EDED");
	}
	function fullArrayDiff($left, $right)
	{
		return array_diff(array_merge($left, $right), array_intersect($left, $right));
	} 
	function hbase_table_getval(&$sql,&$tables,$orgsql)
	{
		global $client, $debug, $debug_language;
		//$tables = die(print_r(hbase_table($orgsql,"SELECT")));
		$tables = ((hbase_table($orgsql,"SELECT")));
		foreach( $tables as $v)
		{
			//$t = current(current($v));
			$t = $v;
			//$tables[] = current(current($v));
			$mu = getmutation($sql,$t);
			$scanner = $client->scannerOpen( $t, "", $mu);
			try {
				while (true) 
				{
					//printRow( $client->scannerGet( $scanner ) );

					$arrs[] = $client->scannerGet( $scanner );
					if(!count(end($arrs)))break;
					//print_r( $client->scannerGet( $scanner ) );
				}
			} catch ( NotFound $nf ) {
				$client->scannerClose( $scanner );
				echo( "Scanner finished\n" );
			}
			if( $debug_language )
			{
				//print_r( $arrs );
			}
			foreach ( $arrs as $k=>$v )
			{
				//print_r($v);
				//$row[$k] = $v;
				//$v[0] TRowResult Object
				foreach($v[0]->columns as $col=>$tc)
				{
					if($debug)
					{
						//echo "value = {$tc->value} , <br> ";
						//echo "timestamp = {$tc->timestamp} <br>";
					}
					$cell[substr($col,0,-1)] = trim($tc->value,"\'");
				}
				$rows[] = $cell;
				unset($cell);
			}
			//print_r($rows);
			$returnrow[] = $rows;
			unset($arrs);
			unset($rows);
		}
		//die(print_r($returnrow));
		return $returnrow;
	}
	function hbase_table_order(&$sql)
	{

	}
	/*
	 * return the segment of condition(like WHERE, SELECT)
	 * @param sql
	 * @param tag
	 */
	function hbase_getcol(&$sql,$tag)
	{
		foreach($sql as $k => $v)
		{
			//echo $k . $tag."<br/>";
			if(strpos($k,$tag) !== false)
			{
				return $v;
			}
		}
		return NULL;
	}
	class sortbyu
	{
		function DateSort2($a,$b){
			$a = $a['news_cd'];
			$b = $b['news_cd'];
			//echo $a . "|" . $b . "<br />\n";
			if ($a == $b) {
				return 0;
			} else {  //Convert into dates and compare
				if ($a > $b){
					return -1;
				} else {
					return 1;
				}
			}
		} 

		function DateSort($a,$b,$d="-",$d1=":") {
			$a = $a['d_news_begin'];
			$b = $b['d_news_begin'];
			if ($a == $b) {
				return 0;
			} else {  //Convert into dates and compare
				// 2009-10-22 00:00:00
				//list($am,$ad,$ay)=split($d,$a);
				$a = explode(" ",$a);
				$b = explode(" ",$b);
				list($ay,$am,$ad)=explode($d,$a[0]);
				list($by,$bm,$bd)=explode($d,$b[0]);
				list($ah,$ai,$as)=explode($d1,$a[1]);
				list($bh,$bi,$bs)=explode($d1,$b[1]);
				//list($bm,$bd,$by)=split($d,$b);
				//echo (mktime(0,0,0,$am,$ad,$ay) ."|". mktime(0,0,0,$bm,$bd,$by))
				//echo (($am."|".$ad."|".$ay) ."<|>". ($bm."|".$bd."|".$by))
				//	. "<br/>\n"; 
				//if (mktime(0,0,0,$am,$ad,$ay) < mktime(0,0,0,$bm,$bd,$by)) {
				if (mktime($ah,$ai,$as,$am,$ad,$ay) > mktime($bh,$bi,$bs,$bm,$bd,$by)) {
					return -1;
				} else {
					return 1;
				}
			}
		} 
	}
?>
