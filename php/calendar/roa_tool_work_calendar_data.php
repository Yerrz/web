<?php
///////////////////////////////////////////////////////////
//
// roa_tool_work_calendar_data.php
// 锐拓OA - 工具 - 工作日历 - 数据服务
//
///////////////////////////////////////////////////////////
require_once "../Lib/roa.Config.php";
require_once "../const/roa.Const.Work.Calendar.php";

// 验证登录
if (!CheckLogin()) die('{ "msg":["ERROR", "服务器：未登录"] }');

$do = $_GET['do'];

// 新建事件
if ($do == 'update') {
    $sId = Validate::GetPlainText($_POST['sid']);
    $sSubject = Validate::GetPlainTextEx($_POST['sSubject']);  // 事件主题
    $sBeginDate = Validate::GetPlainText($_POST['sBeginDate']);
    $sBeginTime = Validate::GetPlainText($_POST['sBeginTime']);
    $sBeginDateTime = $sBeginDate . ' ' . $sBeginTime;  // 开始时间
    $nRemind = intval($_POST['nRemind']);  // 提醒类型
    $sRemindDate = Validate::GetPlainText($_POST['sRemindDate']);  //提醒日期
    $nRepeat = intval($_POST['nRepeat']);  // 重复类型
    $sRepeatEndDate = Validate::GetPlainText($_POST['sRepeatEndDate']);  // 重复结束日期
    $sNote = Validate::GetPlainTextEx($_POST['sNote']);  // 备注
    $updateRepeat = intval($_POST['updateRepeat']);  //修改重复事件时 类型:1-仅修改今天 2-选中日期及以后所有 

    // 数据校验
    $bValid = true;
    $bValid = $bValid && (strlen($sSubject) > 0);
    $bValid = $bValid && isValidDateTime($sBeginDateTime,'YmdHi');
    if ($nRemind == 70) $bValid = $bValid && isValidDateTime($sRemindDate);
    if ($nRepeat > 10) $bValid = $bValid && isValidDateTime($sRepeatEndDate);
    if (!$bValid) die('{ "msg":["ERROR", "服务器：数据校验失败"] }');


    /// 数据处理 ///
    $sBeginMonth = date('n', strtotime($sBeginDate));   //开始时间-月份
    $sBeginMonth = ($sBeginMonth < 13 && $sBeginMonth > 0) ? $sBeginMonth : 0;

    if($nRepeat > 10){    //重复事件的结束日期处理
        $sRepeatEndDate = date('Y-m-d', strtotime($sRepeatEndDate . ' +1 day'));
        $sRepeatEndDate = "'{$sRepeatEndDate}'";
    }
    else $sRepeatEndDate = '(null)';

    // 自定义提醒时间
    // 默认计算提醒时间点
    if($nRemind > 10){   //设置提醒的事件
        $sRemindDateDiff = 0;  //提醒日期与开始日期之间的天数差
        if($nRemind == 20){ //当天
            $sRemindDate = $sBeginDate;
        }
        elseif($nRemind == 60){ //一天前
            $sRemindDate = date('Y-m-d', strtotime($sBeginDate . ' -1 day'));
            $sRemindDateDiff = 1;
        }
        elseif($nRemind == 70) {  // 自定义提醒时间
            $sRemindDateDiff = getDateDiffDays($sBeginDate,$sRemindDate);  //提醒日期与开始日期之间的天数差
        }
        $sRemindDate = "'{$sRemindDate}'";
        $sRemindDateDiff = intval($sRemindDateDiff); //提醒日期与开始日期之间的天数差
    }
    else{
        $sRemindDate = '(null)';
        $sRemindDateDiff = '';  //提醒日期与开始日期之间的天数差
    }

    // 系统信息
    $date = date("Y-m-d H:i:s");
    $sDetail = ($sId) ? "修改事件" : "新建事件";
    $arrChglog = array(
        "sId" => $_SESSION['USER']['UID'],
        "sName" => $_SESSION['USER']['NAME'],
        "sDetail" => $sDetail
    );
    $sChglog = fnAddChglog($date, $arrChglog);

    $d_conn = DB_Connect();
    if($sId){  //修改
        // 查询原数据
        $sql_select = "SELECT sSubject,sBeginDate,sBeginTime,nRemind,sRemindDate,nRepeat,sNote,sRepeatEndDate FROM roa_tool_work_calendar WHERE sId='{$sId}'";
        $d_ret_select = DB_Query($sql_select, $d_conn);
        $rowsel = DB_GetRows($d_ret_select);
        DB_Free($d_ret_select);

        $sBeginDateFromat = "";
        $sql = "UPDATE roa_tool_work_calendar SET sSubject='{$sSubject}',sBeginDate='{$sBeginDate}',sBeginTime='{$sBeginTime}',sBeginMonth='{$sBeginMonth}',nRemind='{$nRemind}',sRemindDate={$sRemindDate},sRemindDateDiff='{$sRemindDateDiff}',nRepeat='{$nRepeat}',sNote='{$sNote}',sChglog=concat_ws('',sChglog,'{$sChglog}'),dtUpdate=NOW(),sRepeatEndDate={$sRepeatEndDate} WHERE sId='{$sId}'";
        // 判断是否是修改重复事件
        if($updateRepeat > 0){
            $sBeginDateOld = $rowsel['sBeginDate'];  // 获取原开始时间
            $sBeginDateFromat = date("Ymd", strtotime("{$sBeginDate}"));
            $sId_repeat_insert = 'WC' . getOnlyId();
            if($updateRepeat == 1){  //仅修改选中日期
                if($sBeginDate == $sBeginDateOld){  //修改的是重复事件的第一天
                    $tomorrow = date('Y-m-d', strtotime("+1 days",strtotime($sBeginDate)));
                    // 修改原数据
                    $sql = "UPDATE roa_tool_work_calendar SET sBeginDate='{$tomorrow}',sChglog=concat_ws('',sChglog,'{$sChglog}'),dtUpdate=NOW() WHERE sId='{$sId}'";
                }
                else{
                    // 修改原数据
                    $sql = "UPDATE roa_tool_work_calendar SET sRepeatExclusionDate=concat_ws(',',sRepeatExclusionDate,'{$sBeginDateFromat}'),sChglog=concat_ws('',sChglog,'{$sChglog}'),dtUpdate=NOW() WHERE sId='{$sId}'";
                }
                // 新建-修改的重复事件
                $nRepeatNew = 10;  //选中日期设置不重复
                $sRepeatEndDate = '(null)';
            }
            if($updateRepeat == 2){  //修改选中日期及以后重复事件
                if($sBeginDate == $sBeginDateOld){  //修改的是重复事件的第一天
                    $sql = "UPDATE roa_tool_work_calendar SET nIsDel=1,dtUpdate=NOW() WHERE sId='{$sId}'";
                }
                else{
                    // 修改原数据，新增选中日期及之后事件
                    $sql = "UPDATE roa_tool_work_calendar SET sRepeatEndDate='{$sBeginDate}',sChglog=concat_ws('',sChglog,'{$sChglog}'),dtUpdate=NOW() WHERE sId='{$sId}'";               
                }
                $nRepeatNew = $nRepeat;
            }

            $sql_repeat_insert = "INSERT INTO roa_tool_work_calendar(sId,sSubject,sBeginDate,sBeginTime,sBeginMonth,nRemind,sRemindDate,sRemindDateDiff,nRepeat,sNote,sbelongUsrId,sChglog,dtInsert,sRepeatEndDate) VALUES ('{$sId_repeat_insert}','{$sSubject}','{$sBeginDate}','{$sBeginTime}','{$sBeginMonth}','{$nRemind}',{$sRemindDate},'{$sRemindDateDiff}','{$nRepeatNew}','{$sNote}','{$_SESSION['USER']['UID']}','{$sChglog}',NOW(),{$sRepeatEndDate})";
            if(!empty($sql_repeat_insert)) DB_Query($sql_repeat_insert, $d_conn);
        } 
    }
    else{
        $sId = 'WC' . getOnlyId();
        $sql = "INSERT INTO roa_tool_work_calendar(sId,sSubject,sBeginDate,sBeginTime,sBeginMonth,nRemind,sRemindDate,sRemindDateDiff,nRepeat,sNote,sbelongUsrId,sChglog,dtInsert,sRepeatEndDate) VALUES ('{$sId}','{$sSubject}','{$sBeginDate}','{$sBeginTime}','{$sBeginMonth}','{$nRemind}',{$sRemindDate},'{$sRemindDateDiff}','{$nRepeat}','{$sNote}','{$_SESSION['USER']['UID']}','{$sChglog}',NOW(),{$sRepeatEndDate})";
    }
    DB_Query($sql, $d_conn);
    DB_Close($d_conn);
    die('{"msg":["S", "服务器：提交成功"]}');
}
// 删除事件
elseif($do == 'deleteEvent'){
    $sId = Validate::GetPlainText($_POST['sid']);
    $sBeginDate = Validate::GetPlainText($_POST['date']);
    $type = intval(Validate::GetPlainText($_POST['type']));  // 0-删除普通事件，1-（重复事件）仅删除选中日期，2-（重复事件）删除选中日期及之后

    $d_conn = DB_Connect();

    // 校验数据
    $bValid = true;
    $bValid = $bValid && (strlen($sId) > 0);
    if (!$bValid) die('{ "msg":["ERROR", "服务器：数据校验失败"] }');

    // 系统信息
    $date = date("Y-m-d H:i:s");
    $arrChglog = array(
        "sId" => $_SESSION['USER']['UID'],
        "sName" => $_SESSION['USER']['NAME'],
        "sDetail" => "删除事件"
    );
    $sChglog = fnAddChglog($date, $arrChglog);

    // 默认删除事件
    $sql = "UPDATE roa_tool_work_calendar SET nIsDel=1,dtUpdate=NOW(),sChglog=concat_ws('',sChglog,'{$sChglog}') WHERE sId='{$sId}'";

    if ($type > 0) {   //重复事件，1仅删除今天，2删除选中日期以及之后
        $sBeginDateFromat = date("Ymd", strtotime("{$sBeginDate}"));

        // 查询原数据
        $sql_select = "SELECT sBeginDate,nRepeat FROM roa_tool_work_calendar WHERE sId='{$sId}'";
        $d_ret_select = DB_Query($sql_select, $d_conn);
        $rowsel = DB_GetRows($d_ret_select);
        DB_Free($d_ret_select);
        $sBeginDateOld = $rowsel['sBeginDate'];

        if($type == 1){  //仅删除今天
            // 修改原数据
            $sql = "UPDATE roa_tool_work_calendar SET sRepeatExclusionDate=concat_ws(',',sRepeatExclusionDate,'{$sBeginDateFromat}'),sChglog=concat_ws('',sChglog,'{$sChglog}'),dtUpdate=NOW() WHERE sId='{$sId}'";
        }
        if($type == 2){  //删除选中日期及以后重复事件
            if($sBeginDate != $sBeginDateOld){  //删除的不是重复事件的第一天
                $sql = "UPDATE roa_tool_work_calendar SET sRepeatEndDate='{$sBeginDate}',sChglog=concat_ws('',sChglog,'{$sChglog}'),dtUpdate=NOW() WHERE sId='{$sId}'";               
            }
        }
    }

    DB_Query($sql, $d_conn);
    DB_Close($d_conn);
    die('{"msg":["S", "服务器：提交成功"]}');
}
// 获取事件列表
elseif ($do == 'getEventsList') {
    $year = intval($_POST['year']);
    $month = intval($_POST['month']);
    $selectday = intval($_POST['day']);

    // 格式化实际选中日期
    $selectDateActul = date("Y-m-d", mktime(0, 0, 0, $month, $selectday, $year));
    $selectdayFormat = date('Y年m月d日', strtotime($selectDateActul));
    $selectdayFormat .= " ".$aWeekOfDayConst[date('w', strtotime($selectDateActul))];

    $start = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));  // 获取这个月的第一天
    $end = date("Y-m-d", mktime(0, 0, -1, $month + 1, 1, $year));  // 获取这个月的最后一天
    $daysInMonth = date('t', strtotime("{$year}-{$month}")); // 获取每月总天数

    $selectday = 1;
    $selectDate = date("Y-m-d", mktime(0, 0, 0, $month, $selectday, $year));  // 获取选择日期(现在默认从1号获取)

    $sql = "SELECT sId,sSubject,sBeginDate,sBeginTime,sBeginMonth,nRemind,sRemindDate,sRemindDateDiff,nRepeat,sRepeatEndDate,sRepeatExclusionDate,sNote FROM roa_tool_work_calendar "
    ."WHERE sbelongUsrId='{$_SESSION['USER']['UID']}' AND nIsDel=0 AND (sRepeatEndDate>'{$selectDate}' OR sRepeatEndDate IS NULL) "
    . "AND ( "
    // -不重复事件
    . "(nRepeat=10 AND sBeginDate BETWEEN '{$selectDate}' AND '{$end}') "
    . "OR "
    // 年重复
    . "(nRepeat=60 AND sBeginDate<='{$end}' AND sBeginMonth='{$month}') "
    . "OR "
    // 日、周、工作日、月重复
    . "(nRepeat IN (20,30,40,50) AND sBeginDate<='{$end}') "
    . " ) ORDER BY sBeginTime ASC,id ASC";

    $d_conn = DB_Connect();
    $d_ret = DB_Query($sql, $d_conn);

    // 形成整月日期数组
    $aCanlendar = array();
    for ($day = $selectday; $day <= $daysInMonth; $day++) {
        $currentDateParm = new DateTime("$year-$month-$day");
        $currentDateParm = $currentDateParm->format('Y-m-d');
        $aCanlendar[$currentDateParm] = array();
    }
    // 该月工作日
    $aWeekOfDay = getWorkingDaysOfMonth($year, $month);

    while ($row = DB_GetRows($d_ret)) {
        if($row['nRepeat'] == 20){ //每日重复
            for ($day = $selectday; $day <= $daysInMonth; $day++) {
                $currentDateParm = new DateTime("$year-$month-$day");
                $currentDateParm1 = $currentDateParm->format('Y-m-d');
                $currentDateParm2 = $currentDateParm->format('Ymd');
                if($row['sBeginDate'] <= $currentDateParm1 && stristr($row['sRepeatExclusionDate'],$currentDateParm2) == false){
                    if(($row['sRepeatEndDate'] && $row['sRepeatEndDate'] > $currentDateParm1) || empty($row['sRepeatEndDate'])) $aCanlendar[$currentDateParm1][] = $row;
                } 
            }
        }
        elseif($row['nRepeat'] == 30){ //每工作日
            for ($i = 0; $i < count($aWeekOfDay); $i++) {
                $currentDateParm = new DateTime($aWeekOfDay[$i]);
                $currentDateParm = $currentDateParm->format('Ymd');
                if($row['sBeginDate'] <= $aWeekOfDay[$i] && isset($aCanlendar[$aWeekOfDay[$i]]) && stristr($row['sRepeatExclusionDate'],$currentDateParm) == false){
                    if(($row['sRepeatEndDate'] && $row['sRepeatEndDate'] > $aWeekOfDay[$i]) || empty($row['sRepeatEndDate'])) $aCanlendar[$aWeekOfDay[$i]][] = $row;
                } 
            }
        }
        elseif($row['nRepeat'] == 40){  //每周
            $sBeginDateOfWeek = date('w', strtotime($row['sBeginDate']));
            $aWeekDay = getWednesdaysOfMonth($year,$month, $sBeginDateOfWeek);
            for ($i = 0; $i < count($aWeekDay); $i++) {
                $currentDateParm = new DateTime($aWeekDay[$i]);
                $currentDateParm = $currentDateParm->format('Ymd');
                if($row['sBeginDate'] <= $aWeekDay[$i] && isset($aCanlendar[$aWeekDay[$i]]) && stristr($row['sRepeatExclusionDate'],$currentDateParm) == false){
                    if(($row['sRepeatEndDate'] && $row['sRepeatEndDate'] > $aWeekDay[$i]) || empty($row['sRepeatEndDate'])) $aCanlendar[$aWeekDay[$i]][] = $row;
                } 
            }
        }
        elseif($row['nRepeat'] == 50){ //每月
            $dateTime = new DateTime($row['sBeginDate']);
            $dateTime->setDate($dateTime->format('Y'), $month, $dateTime->format('d'));
            $newDate = $dateTime->format('Y-m-d');
            $newDate2 = $dateTime->format('Ymd');
            if(stristr($row['sRepeatExclusionDate'],$newDate2) == false && isset($aCanlendar[$newDate])){
                if(($row['sRepeatEndDate'] && $row['sRepeatEndDate'] > $newDate) || empty($row['sRepeatEndDate'])) $aCanlendar[$newDate][] = $row;
            } 
        }
        elseif($row['nRepeat'] == 60){ //每年
            $dateTime = new DateTime($row['sBeginDate']);
            $dateTime->setDate($year, $dateTime->format('m'), $dateTime->format('d'));
            $newDate = $dateTime->format('Y-m-d');
            $newDate2 = $dateTime->format('Ymd');
            if(stristr($row['sRepeatExclusionDate'],$newDate2) == false && isset($aCanlendar[$newDate])){
                if(($row['sRepeatEndDate'] && $row['sRepeatEndDate'] > $newDate) || empty($row['sRepeatEndDate'])) $aCanlendar[$newDate][] = $row;
            } 
        }
        else $aCanlendar[$row['sBeginDate']][] = $row;
    }
    DB_Free($d_ret);
    DB_Close($d_conn);

    // 构建列表
    $listHTML .= '<div class="fc-listGridMonth-view" id="listGridMonth">';
    $listHTML .='<div class="fc-list-calendar-view"><div class="day-info"><div class="day-num">'.intval(date('d',strtotime($selectDateActul))).'</div><div>'.$selectdayFormat.'</div></div><div id="dtSelectDate"></div></div>';
    $listHTML .='<div class="fc-table-list-box"><table class="fc-table-list"><tbody>';

    $nTotalFalg = 0;  // 数量标记

    // 循环输出日程事件数据
    for ($day = $selectday; $day <= $daysInMonth; $day++) {
        $currentDateParm = new DateTime("$year-$month-$day");
        $currentDateParm = $currentDateParm->format('Y-m-d');

        if (isset($aCanlendar[$currentDateParm]) && !empty($aCanlendar[$currentDateParm])) {
            $nTotalFalg ++;
            $aEventOfDay = $aCanlendar[$currentDateParm];

            $sBeginDate = DateTime::createFromFormat('Y-m-d', $currentDateParm);
            $sDetailDateFormat = $sBeginDate->format('Y年n月j日');
            $sDetailDateOfWeek = $aWeekOfDayConst[date('w', strtotime($currentDateParm))];  // 获取为星期几
            $listHTML .= '<tr class="fc-list-headTr" id="dateTitle'.$currentDateParm.'"><th class="fc-list-events-title"><a>'.$sDetailDateFormat.'</a></th><th class="fc-list-event-time" width="50">'.$sDetailDateOfWeek.'</th><th></th></tr>';

            foreach ($aEventOfDay as $index => $item) {
                // 悬浮信息显示//
                $clDaysDetailShow = "";
                $sDetailDateTime = substr($item['sBeginTime'], 0, 5);
                $sDetailDate = $sDetailDateFormat . " " . $sDetailDateOfWeek . " " . $sDetailDateTime; 
                $clDaysDetailShow = '<div class="fc-day-events-calendar-detailDiv fc-list-detailDiv">'
                . '<ul class="field"><li class="data detailDiv-subject textOmit"><a>' . $item['sSubject'] . '</a></li></ul>'
                . '<ul class="field"><li class="data detailDiv-date"><a>' . $sDetailDate . '</a></li></ul>';
                // 若提醒为“不提醒”，重复为“不重复”，备注为空，则对应项不显示
                if ($item['nRemind'] != 10){  // 提醒
                    $sRemindDateDiff = intval($item['sRemindDateDiff']);    //提醒时间与开始时间的日期天数差值
                    // 计算提醒时间
                    if($item['nRepeat'] == 10){  // 不重复事件
                        $sRemindDateTime = $item['sRemindDate'];
                    }
                    else{  // 重复事件
                        $sRemindDateTime = $currentDateParm;
                        if($item['nRemind'] > 20){
                            $sRemindDateTime = date('Y-m-d', strtotime("-{$sRemindDateDiff} days",strtotime($currentDateParm)));   //实际具体提醒时间
                        }
                    }
                    $sRemindDateTime = date('Y年m月d日', strtotime($sRemindDateTime));
                } 
                $clDaysDetailShow2 = '';
                if($item['nRemind'] != 10) $clDaysDetailShow2 .= '<ul class="field"><li class="title">提醒:</li><li class="data">' . $sRemindDateTime . '</li></ul>';
                if ($item['nRepeat'] != 10) $clDaysDetailShow2 .= '<ul class="field"><li class="title">重复:</li><li class="data">' . $anRepeatConst[$item['nRepeat']] . '</li></ul>';
                if (!empty($item['sNote'])) $clDaysDetailShow2 .= '<ul class="field"><li class="title">备注:</li><li class="data textOmitWrap">'.nl2br($item['sNote']).'</li></ul>';
                if(!empty($clDaysDetailShow2)) $clDaysDetailShow2 = '<br>'.$clDaysDetailShow2;
                $clDaysDetailShow .= $clDaysDetailShow2;
                $clDaysDetailShow .= '</div>';


                // 底部编辑按钮(删除、编辑、关闭)
                $clDaysDetailClickShow = '<div class="fc-day-events-calendar-detailEditDiv">'
                .'<div class="fc-day-events-calendar-detailDiv2">'
                        .'<ul class="field"><li class="data detailDiv-subject textOmit"><a>'.$item['sSubject'].'</a></li></ul>'
                        .'<ul class="field"><li class="data detailDiv-date"><a>'.$sDetailDate.'</a></li></ul>';
                // 若提醒为“不提醒”，重复为“不重复”，备注为空，则对应项不显示
                $clDaysDetailClickShow .= $clDaysDetailShow2;
                $clDaysDetailClickShow .= '</div>';
                $clDaysDetailClickShow .= '<div class="fc-day-events-calendar-editbutton">'
                    .'<button type="button" class="fc-day-events-calendar-edit-btn fc-day-events-delbtn" data-id="'.$item['sId'].'" data-repeat="'.$item['nRepeat'].'" data-date="'.$currentDateParm.'">删除</button>'
                    .'<button type="button" class="fc-day-events-calendar-edit-btn fc-day-events-exitbtn">关闭</button>'
                    .'<button type="button" class="fc-day-events-calendar-edit-btn fc-day-events-editbtn" data-id="'.$item['sId'].'" data-date="'.$currentDateParm.'">编辑</button>'
                    .'</div>';
                
                $calendarHTML .= '<div>';

                // 显示每个日期下的日程事件
                $listHTML .= '<tr><td class="fc-list-events-title"></td><td class="fc-list-event-time fc-list-time"><div class="fc-day-events-calendar-dot"></div><a>'.$sDetailDateTime.'</a></td><td class="fc-list-event-subject"><div><a class="fc-list-subject">'.$item['sSubject'].'</a>'.$clDaysDetailShow.$clDaysDetailClickShow.'</div></td>';

                $listHTML .= '</tr>';

            }
        }
    }

    // 判断查询的日期时间段的事件数量是否为空
    if($nTotalFalg == 0) $listHTML .= '<div class="fc-list-not-available">暂无新建事件</div>';

    $listHTML .= '</tbody></table></div></div>';


    die(json_encode($listHTML));
} 
// 获取修改事件详情
elseif($do == 'getUpdateEvent'){
    $sId = Validate::GetPlainText($_POST['sid']);

    // 校验数据
    $sql = "SELECT sId,sSubject,sBeginDate,sBeginTime,nRemind,sRemindDate,nRepeat,sNote,sRepeatEndDate FROM roa_tool_work_calendar WHERE sId='{$sId}'";
    $d_conn = DB_Connect();
    $d_ret = DB_Query($sql, $d_conn);
    $row = DB_GetRows($d_ret);
    // 重复事件的截止日期处理，实际显示为截止前一天
    if($row['nRepeat'] > 10) $row['sRepeatEndDate'] = date('Y-m-d', strtotime($row['sRepeatEndDate'] . ' -1 day'));
    DB_Free($d_ret);
    DB_Close($d_conn);

    die(json_encode($row));
}
// 获取日历
elseif ($do == 'getCalendar') {
    $year = intval($_POST['year']);
    $month = intval($_POST['month']);

    $start = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));  // 获取这个月的第一天
    $end = date("Y-m-d", mktime(0, 0, -1, $month + 1, 1, $year));  // 获取这个月的最后一天

    $sql = "SELECT sId,sSubject,sBeginDate,sBeginTime,sBeginMonth,nRemind,sRemindDate,sRemindDateDiff,nRepeat,sRepeatEndDate,sRepeatExclusionDate,sNote FROM roa_tool_work_calendar "
    ."WHERE sbelongUsrId='{$_SESSION['USER']['UID']}' AND nIsDel=0 AND (sRepeatEndDate>'{$start}' OR sRepeatEndDate IS NULL) "
    . "AND ( "
    // -不重复事件
    . "(nRepeat=10 AND sBeginDate BETWEEN '{$start}' AND '{$end}') "
    . "OR "
    // 年重复
    . "(nRepeat=60 AND sBeginDate<='{$end}' AND sBeginMonth='{$month}') "
    . "OR "
    // 日、周、工作日、月重复
    . "(nRepeat IN (20,30,40,50) AND sBeginDate<='{$end}') "
    . " ) ORDER BY sBeginTime ASC,id ASC";

    $d_conn = DB_Connect();
    $d_ret = DB_Query($sql, $d_conn);
    $result = array();

    // 形成整月日期数组
    $aCanlendar = array();
    $daysInMonth = date('t', strtotime("{$year}-{$month}")); //总天数
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $currentDateParm = new DateTime("$year-$month-$day");
        $currentDateParm = $currentDateParm->format('Y-m-d');
        $aCanlendar[$currentDateParm] = array();
    }
    // 获取该月工作日
    $aWeekOfDay = getWorkingDaysOfMonth($year,$month);

    while ($row = DB_GetRows($d_ret)) {
        if($row['nRepeat'] == 20){ //每日重复
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $currentDateParm = new DateTime("$year-$month-$day");
                $currentDateParm1 = $currentDateParm->format('Y-m-d');
                $currentDateParm2 = $currentDateParm->format('Ymd');
                if($row['sBeginDate'] <= $currentDateParm1 && stristr($row['sRepeatExclusionDate'],$currentDateParm2) == false){
                    if(($row['sRepeatEndDate'] && $row['sRepeatEndDate'] > $currentDateParm1) || empty($row['sRepeatEndDate'])) $aCanlendar[$currentDateParm1][] = $row;
                } 
            }
        }
        elseif($row['nRepeat'] == 30){ //每工作日
            for ($i = 0; $i < count($aWeekOfDay); $i++) {
                $currentDateParm = new DateTime($aWeekOfDay[$i]);
                $currentDateParm = $currentDateParm->format('Ymd');
                if($row['sBeginDate'] <= $aWeekOfDay[$i] && isset($aCanlendar[$aWeekOfDay[$i]]) && stristr($row['sRepeatExclusionDate'],$currentDateParm) == false){
                    if(($row['sRepeatEndDate'] && $row['sRepeatEndDate'] > $aWeekOfDay[$i]) || empty($row['sRepeatEndDate'])) $aCanlendar[$aWeekOfDay[$i]][] = $row;
                } 
            }
        }
        elseif($row['nRepeat'] == 40){  //每周
            $sBeginDateOfWeek = date('w', strtotime($row['sBeginDate']));
            $aWeekDay = getWednesdaysOfMonth($year,$month, $sBeginDateOfWeek);
            for ($i = 0; $i < count($aWeekDay); $i++) {
                $currentDateParm = new DateTime($aWeekDay[$i]);
                $currentDateParm = $currentDateParm->format('Ymd');
                if($row['sBeginDate'] <= $aWeekDay[$i] && isset($aCanlendar[$aWeekDay[$i]]) && stristr($row['sRepeatExclusionDate'],$currentDateParm) == false){
                    if(($row['sRepeatEndDate'] && $row['sRepeatEndDate'] > $aWeekDay[$i]) || empty($row['sRepeatEndDate'])) $aCanlendar[$aWeekDay[$i]][] = $row;
                } 
            }
        }
        elseif($row['nRepeat'] == 50){ //每月
            $dateTime = new DateTime($row['sBeginDate']);
            $dateTime->setDate($dateTime->format('Y'), $month, $dateTime->format('d'));
            $newDate = $dateTime->format('Y-m-d');
            $newDate2 = $dateTime->format('Ymd');
            if(stristr($row['sRepeatExclusionDate'],$newDate2) == false){
                if(($row['sRepeatEndDate'] && $row['sRepeatEndDate'] > $newDate) || empty($row['sRepeatEndDate'])) $aCanlendar[$newDate][] = $row;
            } 
        }
        elseif($row['nRepeat'] == 60){ //每年
            $dateTime = new DateTime($row['sBeginDate']);
            $dateTime->setDate($year, $dateTime->format('m'), $dateTime->format('d'));
            $newDate = $dateTime->format('Y-m-d');
            $newDate2 = $dateTime->format('Ymd');
            if(stristr($row['sRepeatExclusionDate'],$newDate2) == false){
                if(($row['sRepeatEndDate'] && $row['sRepeatEndDate'] > $newDate) || empty($row['sRepeatEndDate'])) $aCanlendar[$newDate][] = $row;
            } 
        }
        else $aCanlendar[$row['sBeginDate']][] = $row;
    }
    DB_Free($d_ret);
    DB_Close($d_conn);

    $firstDay = date('w', strtotime($start)); // 获取每月第一天是星期几(按日历周一到周六周七)
    $firstDay = ($firstDay == 0) ? 6 : ($firstDay - 1);   // 获取每月第一天是星期几(按日历周日到周一..周六)
    $lastDay = date('w', strtotime($end)); // 获取每月最后一天是星期几(周七为0)
    $lastDay = ($lastDay == 0) ? 6 : ($lastDay - 1);   // 获取每月第一天是星期几(按日历周日到周一..周六)
    $daysInMonth = date('t', strtotime("{$year}-{$month}")); // 获取每月总天数
    $weekrows = ceil(($firstDay + $daysInMonth) / 7);  //该月日历行数

    /////////////////////////////////////////////////////
    // 构建日历
    //////////////////////////////////////////////////////
    $calendarHTML = '';

    // 日历表格
    $calendarHTML .= '<div class="fc-dayGridMonth-view" id="dayGridMonth"><table class="fc-table">';

    // 构建表头，显示周几
    $calendarHTML .= '<thead><tr class="row"><th>周一</th><th>周二</th><th>周三</th><th>周四</th><th>周五</th><th>周六</th><th>周日</th></tr></thead>';

    // 构建日历主体
    $calendarHTML .= '<tbody><tr>';

    // 补充空白天数
    for ($i = 0; $i < $firstDay; $i++) {
        $calendarHTML .= '<td class="fc-day fc-day-others"></td>';
    }


    // 显示当月有效日期
    $currentDateParm = '';
    $sCheduleEvents = $aCanlendar;  //新数组
    // print_r($sCheduleEvents);die;
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $currentDateParm = new DateTime("$year-$month-$day");
        $currentDateParm = $currentDateParm->format('Y-m-d');

        // 换行
        if (($day + $firstDay - 1) % 7 === 0 && $day !== 1) {
            $calendarHTML .= '</tr><tr>';
        }

        // 获取节日常量
        $sFestivalName = $anFixedHolidayConst[$currentDateParm];
        // 高亮当前日期
        if ($year == date('Y') && $month == date('m') && $day == date('d')) {
            $calendarHTML .= '<td class="fc-day fc-day-today fc-day-selected" data-date="' . $currentDateParm . '"><div class="fc-day-box"><div class="fc-day-top fc-day-top-today"><a class="fc-day-number">' . '今天(' . $month . '月' . $day . '日)' . '</a><a class="fc-day-festival">'.$sFestivalName.'</a></div>';
        } else {
            $calendarHTML .= '<td class="fc-day" data-date="' . $currentDateParm . '"><div class="fc-day-box"><div class="fc-day-top"><a class="fc-day-number">' . $day . '</a><a class="fc-day-festival">'.$sFestivalName.'</a></div>';
        }

        // 显示事件
        $eventsNum = 0;
        // $eventsNumMax = ($weekrows > 5) ? 3 : 4; //可视事件的最大数(大于5行则显示3条，默认4条)
        $eventsNumMax = 4; //可视事件的最大数(默认4条)
        $calendarHTML .= '<div class="fc-day-events">';
        // // 1.固定节假日事件
        // if ($day == 5 || $day == 6) {
        //     $calendarHTML .= '<div class="fc-event fc-day-events-main">aaa</div>';
        //     $eventsNum += 1;
        // }
        // 2.日程
        if (isset($sCheduleEvents[$currentDateParm])) {
            $aEventOfDay = $sCheduleEvents[$currentDateParm];
            $sBeginDate = DateTime::createFromFormat('Y-m-d', $currentDateParm);
            foreach ($aEventOfDay as $index => $item) {
                // 需要提醒的事件标红
                $nRemindFlag = ($item['nRemind'] > 10) ? "fc-day-events-calendar-nRemind" : "";

                // 悬浮信息显示
                $clDaysDetailShow = "";
                $sDetailDate = $sBeginDate->format('Y年n月j日');
                $sDetailDate .= " " . $aWeekOfDayConst[date('w', strtotime($currentDateParm))];  // 获取为星期几
                $sDetailDate .= " " . substr($item['sBeginTime'], 0, 5);  
                $clDaysDetailShow = '<div class="fc-day-events-calendar-detailDiv">'
                        .'<ul class="field"><li class="data detailDiv-subject textOmit"><a>'.$item['sSubject'].'</a></li></ul>'
                        .'<ul class="field"><li class="data detailDiv-date"><a>'.$sDetailDate.'</a></li></ul>';
                // 若提醒为“不提醒”，重复为“不重复”，备注为空，则对应项不显示
                if ($item['nRemind'] != 10){  // 提醒
                    $sRemindDateDiff = intval($item['sRemindDateDiff']);    //提醒时间与开始时间的日期天数差值
                    // 计算提醒时间
                    if($item['nRepeat'] == 10){  // 不重复事件
                        $sRemindDateTime = $item['sRemindDate'];
                    }
                    else{  // 重复事件
                        $sRemindDateTime = $currentDateParm;
                        if($item['nRemind'] > 20){
                            $sRemindDateTime = date('Y-m-d', strtotime("-{$sRemindDateDiff} days",strtotime($currentDateParm)));  //实际具体提醒时间
                        }
                    }
                    $sRemindDateTime = date('Y年m月d日', strtotime($sRemindDateTime));
                } 
                $clDaysDetailShow2 = "";
                if($item['nRemind'] != 10) $clDaysDetailShow2 .= '<ul class="field"><li class="title">提醒:</li><li class="data">'.$sRemindDateTime.'</li></ul>';
                if($item['nRepeat'] != 10) $clDaysDetailShow2 .= '<ul class="field"><li class="title">重复:</li><li class="data">'.$anRepeatConst[$item['nRepeat']].'</li></ul>';
                if(!empty($item['sNote'])) $clDaysDetailShow2 .= '<ul class="field"><li class="title">备注:</li><li class="data textOmitWrap">'.nl2br($item['sNote']).'</li></ul>';
                if(!empty($clDaysDetailShow2)) $clDaysDetailShow2 = "<br>".$clDaysDetailShow2; 
                $clDaysDetailShow .= $clDaysDetailShow2;
                $clDaysDetailShow .= '</div>';
                
                // 底部编辑按钮(删除、编辑、关闭)
                $clDaysDetailClickShow = '<div class="fc-day-events-calendar-detailEditDiv">'
                .'<div class="fc-day-events-calendar-detailDiv2">'
                        .'<ul class="field"><li class="data detailDiv-subject textOmit"><a>'.$item['sSubject'].'</a></li></ul>'
                        .'<ul class="field"><li class="data detailDiv-date"><a>'.$sDetailDate.'</a></li></ul>';
                // 若提醒为“不提醒”，重复为“不重复”，备注为空，则对应项不显示
                $clDaysDetailClickShow .= $clDaysDetailShow2;
                $clDaysDetailClickShow .= '</div>';
                $clDaysDetailClickShow .= '<div class="fc-day-events-calendar-editbutton">'
                    .'<button type="button" class="fc-day-events-calendar-edit-btn fc-day-events-delbtn" data-id="'.$item['sId'].'" data-repeat="'.$item['nRepeat'].'" data-date="'.$currentDateParm.'">删除</button>'
                    .'<button type="button" class="fc-day-events-calendar-edit-btn fc-day-events-exitbtn">关闭</button>'
                    .'<button type="button" class="fc-day-events-calendar-edit-btn fc-day-events-editbtn" data-id="'.$item['sId'].'" data-date="'.$currentDateParm.'">编辑</button>'
                    .'</div>';
                $clDaysDetailClickShow .= '</div>';
                
                $calendarHTML .= '<div>';
                // 日程事件在日历栏上的显示内容
                $calendarHTML .= '<a class="fc-event fc-day-events-calendar fc-day-events-calendar-basic ' . $nRemindFlag . '">' 
                    .'<div class="fc-day-events-calendar-dot"></div>' 
                    .'<div class="fc-day-events-calendar-time">' . substr($item['sBeginTime'], 0, 5) . '</div>' 
                    .'<div class="fc-day-events-calendar-title">' . $item['sSubject'] . '</div>' 
                    .'</a>';
                
                $calendarHTML .= $clDaysDetailShow;
                $calendarHTML .= $clDaysDetailClickShow;
                $calendarHTML .= '</div>';
            }

            $calendarHTML .= '<a class="fc-event fc-day-events-calendar fc-day-events-calendar-add" data-date="' . $currentDateParm . '">' .
                            '<div class="fc-day-events-calendar-time"></div>' .
                            '<div class="fc-day-events-calendar-title">新建事件...</div>' .
                            '</a>';
        }
        $calendarHTML .= '</div></div>'; 

        if (isset($sCheduleEvents[$currentDateParm])) {
            // 判断是否有剩余的事件,有则显示+（数量）more
            $calendarHTMLMore = "";
            if (($eventsNum + count($sCheduleEvents[$currentDateParm])) > $eventsNumMax) {
                $calendarHTMLMore = '<a class="fc-event fc-day-events-calendar fc-day-events-calendar-more">' .
                    '<div class="fc-day-events-calendar-time"></div>' .
                    '<div class="fc-day-events-calendar-title">+' . ($eventsNum + count($sCheduleEvents[$currentDateParm]) - $eventsNumMax) . 'more</div>' .
                    '</a>';
            }
            $calendarHTML .= $calendarHTMLMore;
        }

        $calendarHTML .= '</td>';
    }

    // 补充空白天数
    for ($i = $lastDay; $i < 6; $i++) {
        $calendarHTML .= '<td class="fc-day fc-day-others"></td>';
    }

    $calendarHTML .= '</tr></tbody>';
    $calendarHTML .= '</table></div>';

    die(json_encode($calendarHTML));
}

// 时间格式校验 y-m-d H:i
function isValidDateTime($datetime,$format='Ymd')
{
    $pattern = '/^\d{4}-\d{2}-\d{2}$/';
    if($format == 'YmdHi') $pattern = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/';
    if (!preg_match($pattern, $datetime)) return false;

    try {
        $dateTime = new DateTime($datetime);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// 构建某月的日期数组（用于日期判断）
function getDatesOfMonth($year, $month) {
    // 创建一个 DateTime 对象表示该月的第一天
    $firstDayOfMonth = new DateTime("$year-$month-01");
  
    // 创建一个 DateInterval 对象，用于按一天进行迭代
    $interval = new DateInterval('P1D');
  
    // 创建一个 DatePeriod 对象，用于生成该月每一天的日期
    $period = new DatePeriod($firstDayOfMonth, $interval, $firstDayOfMonth->format('t'));
  
    // 存储所有日期
    $dates = array();
    foreach ($period as $date) {
        $dates[$date->format('Y-m-d')] = array();
    }
  
    return $dates;
  }

// 获取某月的工作日
function getWorkingDaysOfMonth($year, $month){
    // 创建一个 DateTime 对象表示该月的第一天
    $firstDayOfMonth = new DateTime("$year-$month-01");

    // 创建一个 DateInterval 对象，用于按一天进行迭代
    $interval = new DateInterval('P1D');

    // 创建一个 DatePeriod 对象，用于生成该月每一天的日期
    $period = new DatePeriod($firstDayOfMonth, $interval, $firstDayOfMonth->format('t'));

    // 过滤出所有的工作日（周一至周五）
    $workingDays = array();
    foreach ($period as $date) {
        if ($date->format('N') >= 1 && $date->format('N') <= 5) { // N 表示星期几，1=周一，7=周日
            $workingDays[] = $date->format('Y-m-d');
        }
    }

    return $workingDays;
}

// 获取该月所有的星期几
function getWednesdaysOfMonth($year, $month, $beginDateOfWeek){
    // 创建一个 DateTime 对象表示该月的第一天
    $firstDayOfMonth = new DateTime("$year-$month-01");

    // 计算该月最后一天
    $lastDayOfMonth = clone $firstDayOfMonth;
    $lastDayOfMonth->modify('last day of this month');

    // 存储所有星期一的日期
    $mondays = array();

    // 初始化当前日期为该月的第一天
    $currentDate = clone $firstDayOfMonth;

    while ($currentDate <= $lastDayOfMonth) {
        // 将星期日设置为0
        $dayOfWeek = $currentDate->format('w'); // w 表示星期几，0=周日，6=周六
        if ($dayOfWeek == $beginDateOfWeek) { // 星期一
            $mondays[] = $currentDate->format('Y-m-d');
        }

        // 移动到下一天
        $currentDate->add(new DateInterval('P1D'));
    }

    return $mondays;
}

// 计算两个日期间之间的天数差值
function getDateDiffDays($date1, $date2) {
    $date1 = new DateTime($date1);
    $date2 = new DateTime($date2);
    
    // 计算时间差值
    $interval = $date1->diff($date2);
    // 格式化为天数
    $datediff = $interval->format('%a');
    
    return $datediff;
}
