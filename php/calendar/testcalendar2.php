<?php
header("Content-type: text/html; charset=utf-8");
require_once "Lib/roa.Config.php";
require_once "Lib/Config.PHPMailer.php";

// die("fnWorkCalendarRemind今日日历事件提醒");
if (!CheckLogin()) dieEx("请<a href='roa_log.php?do=log'>登录</a>");

require_once "modules/tming_task_module.php";
fnWorkCalendarRemind();
// function fnWorkCalendarRemind() {
//     $today = date("Y-m-d");  // 今日日期

//     // 查询今日需要提醒的日程事件
//     $sql = "SELECT A.sId,A.sSubject,A.sBeginDate,A.sBeginTime,A.sBeginMonth,A.sRemindDate,A.sRemindDateDiff,A.nRepeat,A.sRepeatExclusionDate,A.sRepeatEndDate,B.sName,B.sEmail FROM roa_tool_work_calendar A "
//         ."LEFT JOIN roa_user_account B ON A.sbelongUsrId=B.sUsrId "
//         ."WHERE A.nIsDel=0 AND A.nRemind>10 "
//         . "AND ( "
//         // -不重复事件
//         . "(A.nRepeat=10 AND A.sRemindDate='{$today}') "
//         . "OR "
//         // 日、周、工作日、月、年重复
//         . "(A.nRepeat IN (20,30,40,50,60) AND A.sRemindDate<='{$today}' AND A.sRepeatEndDate>'{$today}') "
//         . " ) ORDER BY A.id ASC";
//     // die($sql);
//     $d_con = DB_Connect();
//     $d_ret = DB_Query($sql, $d_con);
//     while ($row = DB_GetRows($d_ret)) {
//         $flag = false;
//         if ($row['nRepeat'] == 10) {  //不是重复事件
//             $flag = true;
//         } 
//         else {  //重复事件
//             $sRemindDateDiff = intval($row['sRemindDateDiff']);    //提醒时间与开始时间的日期天数差值
//             $sBeginDateActual = date('Y-m-d', strtotime("+{$sRemindDateDiff} days", strtotime($today)));  //实际开始日期
//             $sBeginDateActual2 = date('Ymd', strtotime($sBeginDateActual));  //格式化实际开始日期
//             // 实际开始时间属于有效期
//             if (($row['sRepeatEndDate'] && $sBeginDateActual < $row['sRepeatEndDate']) && stristr($row['sRepeatExclusionDate'], $sBeginDateActual2) == false) {
//                 if ($row['nRepeat'] == 20) { //每日重复
//                     $flag = true;
//                 } elseif ($row['nRepeat'] == 30) { //每工作日
//                     if (isWorkingDay($sBeginDateActual)) $flag = true;
//                 } elseif ($row['nRepeat'] == 40) {  //每周
//                     if (date('w', strtotime($sBeginDateActual)) == date('w', strtotime($row['sBeginDate']))) {  // 满足周几
//                         $flag = true;
//                     }
//                 } elseif ($row['nRepeat'] == 50) { //每月
//                     if (date('d', strtotime($row['sBeginDate'])) == date('d', strtotime($sBeginDateActual))) {  // 号数相同
//                         $flag = true;
//                     }
//                 } elseif ($row['nRepeat'] == 60) { // 每年
//                     if (date('m', strtotime($row['sBeginDate'])) == date('m', strtotime($sBeginDateActual))) {  // 月份相同
//                         if (date('d', strtotime($row['sBeginDate'])) == date('d', strtotime($sBeginDateActual))) {  // 号数相同
//                             $flag = true;
//                         }
//                     }
//                 }
//             }
//         }

//         // 邮件通知
//         // 邮件
//         if($flag && $row['sEmail']){
//             $sBeginDateTime = $sBeginDateActual . " " . substr($row['sBeginTime'], 0, 5); //开始时间
//             $mSubjectTo = "尊敬的{$row['sName']}，OA工作日历提醒 ".date("Y-m-d H:i:s");
//             $mBodyTo = "{$row['sName']} 您好:<br />
//                 <br />    
//                 OA工作日历 {$row['sSubject']} 将于 {$sBeginDateTime} 开始，可以现在去<a target='_blank' href='http://{$_SERVER['HTTP_HOST']}/oa/roa_tool_work_calendar_table.php'>查看</a>。 
//                 <br /><br />
//                 " . date("Y.m.d") . "<br />
//                 <br />
    
//                 本邮件为自动发送，请勿回复，谢谢！";

//             SendMail($row['sEmail'], $row['sName'], $mSubjectTo, $mBodyTo);
//         }
//     }
//     DB_Free($d_ret);DB_Close($d_con);
//     echo "今日日历事件提醒完毕！";
// }


// function isWorkingDay($date) {
//     // 将日期字符串转换为时间戳
//     $timestamp = strtotime($date);

//     // 获取星期几，0表示周日，1表示周一，以此类推
//     $dayOfWeek = date('w', $timestamp);

//     // 判断是否是周一至周五
//     return $dayOfWeek >= 1 && $dayOfWeek <= 5;
// }
?>