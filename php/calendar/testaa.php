
<?php
header("Content-type: text/html; charset=utf-8");
require_once "Lib/roa.Config.php";
require_once "Lib/Config.PHPMailer.php";

$interal = 60; //1min

// 测试记录日志
    file_put_contents("testcalendatlog.txt", date('Y-m-d H:i:s')."自动执行脚本"."\r\n", FILE_APPEND);

    $time = date("Y-m-d H:i:s");
    $run = require("controlCalendar.php");
    if (!$run) die("run error");

    $time = date("H:i:00");
    // 查询今日需要提醒的日程事件
    // 今日日期
    $today = date("Y-m-d");
    $todayFormat = date("Ymd");
    $isWorkday = isWorkingDay($today);   //是否是工作日
    $sDayOfWeek = date('w', strtotime($today));  //是星期几
    $month = date('n', strtotime($today));
    $year = date("H");
    $day = date("d");


    // 查询今日需要提醒的日程事件
    $sql = "SELECT A.sId,A.sSubject,A.sBeginDate,A.sBeginMonth,A.sBeginTime,A.sRemindTime,A.nRepeat,B.sName,B.sEmail  FROM roa_tool_work_calendar A "
        ."LEFT JOIN roa_user_account B ON A.sbelongUsrId=B.sUsrId "
        ."WHERE A.nIsDel=0 AND (A.sRepeatEndDate>'{$today}' OR A.sRepeatEndDate IS NULL) AND (A.sRepeatExclusionDate NOT LIKE '%{$todayFormat}%') AND A.nRemind>10 AND A.sRemindTime='{$time}' "
        . "AND ( "
        // -不重复事件
        . "(A.nRepeat=10 AND A.sBeginDate='{$today}') "
        . "OR "
        // 年重复
        . "(A.nRepeat=60 AND A.sBeginDate<='{$today}' AND A.sBeginMonth='{$month}') "
        . "OR "
        // 日、周、工作日、月重复
        . "(A.nRepeat IN (20,30,40,50) AND A.sBeginDate<='{$today}') "
        . " ) ORDER BY A.sBeginTime ASC,A.id ASC";
// die($sql);
    $d_con = DB_Connect();
    $d_ret = DB_Query($sql, $d_con);
    // echo $sql;
    $result = array();
    while ($row = DB_GetRows($d_ret)) {
        $flag = false;
        if($row['nRepeat'] == 10 || $row['nRepeat'] == 20) { //不是重复事件/每日重复
            // $result[] = $row;
            $flag = true;
        }
        elseif ($row['nRepeat'] == 30 && $isWorkday) { //每工作日
            // $result[] = $row;
            $flag = true;
        } 
        elseif ($row['nRepeat'] == 40) {  //每周
            $sBeginDateOfWeek = date('w', strtotime($row['sBeginDate']));
            if ($sDayOfWeek == $sBeginDateOfWeek) {
                // $result[] = $row;
                $flag = true;
            }
        } 
        elseif (($row['nRepeat'] == 50 || $row['nRepeat'] == 60) && date('d') == date('d',strtotime($row['sBeginDate']))) { //每月/每年
            // $result[] = $row;
            $flag = true;
        } 

        // 邮件通知
        // 邮件
        if($flag && $row['sEmail']){
            $sBeginDateTime = $row['sBeginDate'] ." ". substr($row['sBeginTime'],0,5); //开始时间处理
            $mSubjectTo = "尊敬的{$row['sName']}，OA工作日历提醒 {$time}";
            $mBodyTo = "{$row['sName']} 您好:<br />
                <br />    
                OA工作日历 {$row['sSubject']} 将于 {$sBeginDateTime} 开始，可以现在去<a target='_blank'href='http://{$_SERVER['HTTP_HOST']}/oa/roa_tool_work_calendar_table.php'>查看</a>。 
                <br /><br />
                " . date("Y.m.d") . "<br />
                <br />
    
                本邮件为自动发送，请勿回复，谢谢！";
            SendMail($row['sEmail'], $row['sName'], $mSubjectTo, $mBodyTo);
    
            // 测试记录日志
            $msg = date('Y-m-d H:i:s').":".$row['sId']." 提醒时间-".$row['sRemindTime']." 主题-".$row['sSubject']."\r\n";
            file_put_contents("testcalendatlog.txt", $msg, FILE_APPEND);
        }
    }
    DB_Free($d_ret);DB_Close($d_con);
    file_put_contents("testcalendatlog.txt", date('Y-m-d H:i:s')."自动执行脚本一次完"."\r\n", FILE_APPEND);


    function isWorkingDay($date) {
        // 将日期字符串转换为时间戳
        $timestamp = strtotime($date);
    
        // 获取星期几，0表示周日，1表示周一，以此类推
        $dayOfWeek = date('w', $timestamp);
    
        // 判断是否是周一至周五
        return $dayOfWeek >= 1 && $dayOfWeek <= 5;
    }
    ?>