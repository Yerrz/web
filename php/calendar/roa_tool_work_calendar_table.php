<?php
///////////////////////////////////////////////////////////
//
// roa_tool_work_calendar_table.php
// 锐拓OA - 工具 - 工作日历
//
///////////////////////////////////////////////////////////
require_once "Lib/roa.Config.php";
require_once "const/roa.Const.Work.Calendar.php";

// 验证登录
if (!CheckLogin()) dieEx("请<a href='roa_log.php?do=log'>登录</a>");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>工作日历</title>

    <?php
    echo "<link href=\"{$_DIR}style/jq-ui/jquery-ui-1.8.13.custom.css?" . _SITE_UPDATE . "\" rel=\"stylesheet\" type=\"text/css\" />
<link href=\"{$_DIR}style/main.css?" . _SITE_UPDATE . "\" rel=\"stylesheet\" type=\"text/css\" />
<link href=\"{$_DIR}style/roa_tool_work_calendar_table.css?" . _SITE_UPDATE . "\" rel=\"stylesheet\" type=\"text/css\" />
<script type=\"text/javascript\">!window.jQuery && document.write(unescape('%3Cscript src=\"{$_DIR}script/jq/jquery.min.js?ver=1.7.2\" type=\"text/javascript\"%3E%3C/script%3E'));</script>
<script type=\"text/javascript\">!window.jQuery.ui && document.write(unescape('%3Cscript src=\"{$_DIR}script/jq/jquery-ui.min.js?ver=1.8.13\" type=\"text/javascript\"%3E%3C/script%3E'));</script>
<script type=\"text/javascript\" src=\"{$_DIR}script/main.js?" . _SITE_UPDATE . "\"></script>
<script type=\"text/javascript\" src=\"{$_DIR}script/ajaxflipper.roa.js\"></script>
<script type=\"text/javascript\" src=\"{$_DIR}script/jq/jquery.form.js?ver=2.84\"></script>
";
    ?>

</head>

<body>
    <div id="calendar" class="fc">
        <div class="overlay"></div>
        <div class="fc-header-toolbar">
            <div class="fc-toolbar-chunk fc-toolbar-left">
                <button type="button" class="fc-button-addEvent fc-button fc-button-primary" title="新建日程">+</button>
                <button type="button" class="fc-button-today fc-button fc-button-primary">今天</button>
            </div>
            <div class="fc-toolbar-chunk fc-toolbar-center">
                <button type="button" class="fc-button-premonth fc-button fc-button-primary"><span class="fc-icon fc-icon-premonth"></span></button>
                <h2 class="fc-header-toolbar-title"><span class="fc-header-title-year"></span>年<span class="fc-header-title-month"></span>月</h2>
                <button type="button" class="fc-button-nextmonth fc-button fc-button-primary"><span class="fc-icon fc-icon-nextmonth"></span></button>
            </div>
            <div class="fc-toolbar-chunk fc-toolbar-right fc-button-group">
                <button type="button" class="fc-button-month fc-button fc-button-primary fc-button-active">月</button>
                <button type="button" class="fc-button-eventsList fc-button fc-button-primary">事件</button>
            </div>
        </div>

        <div class="fc-view" id="mainGridView"></div>
    </div>

    <!-- 新建事件对话框 -->
    <div id="div_chg_addEvent" style="padding: 0; max-height: 500px; overflow-y: auto; display: none;">
        <div id="reg">
            <form class="roa-form" id="dialog_form_addEvent" action="request/roa_tool_work_calendar_data.php?do=update" method="post">
                <div class="SP"></div>
                <div class="SP"></div>
                <ul class="h">
                    <li class="text">事件主题</li>
                    <li class="redstar">*</li>
                    <li class="input">
                        <input type="text" class="text ui-widget-content input_event" maxlength="100" name="sSubject" id="sSubject" placeholder="请输入事件主题" autocomplete="off">
                        <br><span class="CR sSubjectResult inputResult"></span>
                    </li>
                </ul>
                <div class="SP"></div>

                <ul class="h">
                    <li class="text">开始时间</li>
                    <li class="redstar">*</li>
                    <li class="input">
                        <input type="text" class="small" name="sBeginDate" id="sBeginDate" style="width:80px;" maxlength="18" size="22" readonly value="<?php echo date('Y-m-d'); ?>" />
                        <input type="text" class="small" name="sBeginTime" id="sBeginTime" style="width:80px;" maxlength="18" size="22" readonly value="<?php echo date('H:i'); ?>" />
                        <br><span class="CR sBeginResult inputResult"></span>
                    </li>
                </ul>
                <div class="SP"></div>

                <ul class="h">
                    <li class="text">提醒</li>
                    <li class="redstar">&nbsp;</li>
                    <li class="input">
                        <?php echo selectFunction('nRemind', $anRemindConst, 10) ?>
                        <input type="text" class="small nRemindCustomize" name="sRemindDate" id="sRemindDate" style="width:80px;" maxlength="18" size="22" readonly />
                        <br><span class="CR sRemindResult inputResult"></span>
                    </li>
                </ul>
                <div class="SP"></div>

                <ul class="h">
                    <li class="text">重复</li>
                    <li class="redstar">&nbsp;</li>
                    <li class="input">
                        <?php echo selectFunction('nRepeat', $anRepeatConst, 10) ?>
                    </li>
                </ul>
                <div class="SP"></div>

                <ul class="h sRepeatSection">
                    <li class="text">重复时间区间</li>
                    <li class="redstar">*</li>
                    <li class="input">
                        <input type="text" class="small" name="sRepeatStartDate" id="sRepeatStartDate" style="width:80px;" maxlength="18" size="22" readonly /> - 
                        <input type="text" class="small" name="sRepeatEndDate" id="sRepeatEndDate" style="width:80px;" maxlength="18" size="22" readonly />
                        <br><span class="CR sRepeatSectionResult inputResult"></span>
                    </li>
                </ul>
                <div class="SP sRepeatSection"></div>

                <ul class="h">
                    <li class="text">备注</li>
                    <li class="redstar">&nbsp;</li>
                    <li class="input">
                        <textarea name="sNote" id="sNote" cols="50" rows="6"></textarea>
                    </li>
                </ul>
                <div class="SP"></div>
            </form>
        </div>
    </div>

    <!-- 重复事件确认对话框-修改 -->
    <div id="div_confirm_repeatEvent" style="padding: 0; max-height: 500px; overflow-y: auto; overflow-x: hidden; display: none;">
        <div id="reg">
            <div class="SP"></div>
            <ul class="h">
                <li class="text">&nbsp;</li>
                <li class="redstar"></li>
                <li class="input">
                    <div class="SP" id="confirmNote" style="padding-bottom: 5px;">您正在修改重复事件！</div>
                    <span>选择“修改所有重复事件”将修改<strong class="CR">今天及今天之后</strong>的重复事件</span>
                </li>
            </ul>
            <div class="SP"></div>
        </div>
    </div>

    <!-- 重复事件确认对话框-删除 -->
    <div id="div_confirm_repeatEvent_del" style="padding: 0; max-height: 500px; overflow-y: auto; overflow-x: hidden; display: none;">
        <div id="reg">
            <div class="SP"></div>
            <ul class="h">
                <li class="text">&nbsp;</li>
                <li class="redstar"></li>
                <li class="input">
                    <div class="SP" id="confirmNote" style="padding-bottom: 5px;">您正在删除重复事件！</div>
                    <span>选择“删除所有重复事件”将删除<strong class="CR">今天及今天之后</strong>的重复事件</span>
                </li>
            </ul>
            <div class="SP"></div>
        </div>
    </div>

</body>

</html>


<script type="text/javascript" src="./script/jq/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript">
    $(function() {
        // 默认加载日历表格
        buildCalendar();

        // 开始时间
        $("#sBeginDate").datepicker({
            dateFormat: 'yy-mm-dd',
            defaultDate: "yymmdd",
            dayNamesMin: ['日', '一', '二', '三', '四', '五', '六'],
            monthNamesShort: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
            changeMonth: true,
            changeYear: true,
            showButtonPanel: false,
            onClose: function(selectedDate) {
                if ($('#nRemind').val() == 70) $('#sRemindDate').val(selectedDate);
            }
        });
        $("#sBeginTime").timepicker({
            hourMin: 0,
            hourMax: 23,
            // stepMinute: 10,
            onClose: function() {
                // if ($('#nRemind').val() == 70) $('#sRemindTime').val($('#sBeginTime').val());
            }
        });

        // 提醒-自定义
        $("#sRemindDate,#sRepeatStartDate").datepicker(_O_DATEPICKER_OPTIONS_);
        $('#nRemind').change(function() {
            var nRemind = $(this).val();
            if (nRemind == 70) { //选择“自定义”
                $('.nRemindCustomize').show(); //显示自定义时间，默认为设置的开始时间
                $('#sRemindDate').val($('#sBeginDate').val());
            } else $('.nRemindCustomize').hide();
        });

        // 重复
        $('#nRepeat').change(function() {
            var nRepeat = $(this).val();
            if (nRepeat != 10) { // 选择重复类型
                $('.sRepeatSection').show();
            } else $('.sRepeatSection').hide();
        });
        // 重复时间区间
        $("#sRepeatEndDate").datepicker({
            dateFormat: 'yy-mm-dd',
            defaultDate: "yymmdd",
            changeMonth: true,
            changeYear: true,
            dayNamesMin: ['日', '一', '二', '三', '四', '五', '六'],
            monthNamesShort: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
            beforeShow: function(input, inst) {
                $("#sRepeatEndDate").datepicker('option', 'minDate', new Date($('#sRepeatStartDate').val()));
            }
        });
        // 开始时间/重复时间区间的开始时间 联动变化
        $('#sBeginDate,#sRepeatStartDate').change(function() {
            $('#sBeginDate,#sRepeatStartDate').val($(this).val());
        });


        // 菜单栏按钮 //
        // 今天
        $('.fc-button-today').click(function() {
            var todayDate = Today();
            if ($('#listGridMonth').length > 0) { //若为列表页
                showEventsList(todayDate.year, todayDate.month, todayDate.day);
            } else { //默认日历表格页
                buildCalendar(todayDate.year, todayDate.month);
            }
        });
        // 上一月
        $('.fc-button-premonth').click(function() {
            preMonth();
        });
        // 下一月
        $('.fc-button-nextmonth').click(function() {
            nextMonth();
        });
        // 切换到日历表格
        $('.fc-button-month').click(function() {
            buttonActive(".fc-button-month");  // 按钮样式切换
            $('.fc-toolbar-center').show();  // 菜单栏变化
            buildCalendar();
        });
        // 切换到列表
        $('.fc-button-eventsList').click(function() {
            buttonActive(".fc-button-eventsList");
            $('.fc-toolbar-center').hide();
            $('.fc-toolbar-right').css("float", "right");
            showEventsList();
        });

        // 新建日程事件
        $('.fc-button-addEvent').click(function() {
            showAddEventDialog();
        });

    });

    // 组合按钮样式切换
    function buttonActive(obj) {
        $(obj).siblings('button').removeClass("fc-button-active");
        $(obj).addClass("fc-button-active");
    }

    // 日期表格绑定事件
    function bindCalendarDayEvent() {
        // 点击空白处（不是日程事件）时，去除所有点击的样式
        $('body').click(function(event) {
            if (!$(event.target).is('.fc-day') || !$(event.target).is('.fc-day')) {
                calendarRemoveClass();  // 清除所有选中样式
            }
        });
        // 在日期上新建日程
        $('.fc-day-events-calendar-add').click(function() {
            console.log('新建事件', $(this).data('date'));
            showAddEventDialog($(this).data('date'));
        });
        // 点击日程框
        $('.fc').on('click', '.fc-day', function(event) {
            event.stopPropagation(); // 阻止事件冒泡
            calendarRemoveClass();  // 清除所有选中样式

            $(this).addClass('fc-day-selected');
            $(this).children('.fc-day-box').find('.fc-day-events-calendar-add').css("display", "flex");
        });
        // 点击日历表格更多"+more"
        $('.fc-day').on('click', '.fc-day-events-calendar-more', function(event) {
            event.stopPropagation(); // 阻止事件冒泡
            calendarRemoveClass();  // 清除所有选中样式

            $(this).closest('.fc-day').addClass('fc-day-selected');
            // 展示全部事件+新建事件按钮
            $(this).siblings('.fc-day-box').addClass('fc-day-box-overflow-border-selected').find('.fc-day-events-calendar-add').css("display", "flex");
        });
        // 悬浮被选择的日程框-显示新建事件按钮
        $('.fc-day').hover(function() {
            if ($(this).hasClass('fc-day-selected')) $(this).children('.fc-day-box').find('.fc-day-events-calendar-add').css("display", "flex");
        }, function() {
            $('.fc-day').children('.fc-day-box').find('.fc-day-events-calendar-add').css("display", "none");
        });

        //悬浮查看日程详情
        $('.fc-day-events-calendar').hover(function() {
            // console.log('悬浮查看日程详情', eventId);
            $(this).siblings('.fc-day-events-calendar-detailDiv').show();

            // 悬浮信息显示位置优化 //
            var pageScrollTop = $('html').scrollTop();  //页面滑动距离
            // 获取鼠标位置
            var mouseX = $(this).offset().left + $(this).outerWidth() / 2;
            var mouseY = $(this).offset().top + $(this).outerHeight() / 2;
            // 获取提示框尺寸
            var tooltipWidth = $(this).siblings('.fc-day-events-calendar-detailDiv').outerWidth();
            var tooltipHeight = $(this).siblings('.fc-day-events-calendar-detailDiv').outerHeight();
            // 计算提示框位置
            var left = mouseX;
            var top = mouseY;
            // 确保提示框不会超出屏幕边界
            var screenWidth = $(window).width();
            var screenHeight = $(window).height();
            if (left + tooltipWidth > screenWidth) {
                left = mouseX - tooltipWidth - $(this).outerWidth() / 2;
            } else if (left < 0) {
                left = 0;
            }
            if (top + tooltipHeight > screenHeight) {
                top = screenHeight - tooltipHeight - pageScrollTop - 2;
            } else if (top < 0) {
                top = 0;
            }
            else {
                top = top - pageScrollTop;
            }
            // 设置提示框位置
            $(this).siblings('.fc-day-events-calendar-detailDiv').css({
                top: top,
                left: left
            });

            // console.log('top', top,screenHeight,mouseY);
        }, function() {
            $(this).siblings('.fc-day-events-calendar-detailDiv').hide();
        });


        // 点击日程事件呼出带编辑面板
        $('.fc-day').on('click', '.fc-day-events-calendar-basic', function(event) {
            event.stopPropagation(); // 阻止事件冒泡

            calendarRemoveClass();  // 清除所有选中样式
            $(this).siblings('.fc-day-events-calendar-detailEditDiv').show();
            togglePopup();  
        });
        // 日程事件带编辑面板-阻止冒泡
        $('.fc-day').on('click', '.fc-day-events-calendar-detailEditDiv', function(event) {
            event.stopPropagation(); // 阻止事件冒泡
        });
        // 日程事件带编辑面板-关闭
        $('.fc-day').on('click', '.fc-day-events-exitbtn', function(event) {
            event.stopPropagation(); // 阻止事件冒泡

            calendarRemoveClass();  // 清除所有选中样式
            $(this).closest('.fc-day-events-calendar-detailEditDiv').hide();
            togglePopup();
        });
        // 日程事件带编辑面板-删除
        $('.fc-day').on('click', '.fc-day-events-delbtn', function(event) {
            event.stopPropagation(); // 阻止事件冒泡

            var eventId = $(this).data('id');
            var eventRepeat = $(this).data('repeat');
            var eventDate = $(this).data('date');

            calendarRemoveClass();  // 清除所有选中样式
            $(this).closest('.fc-day-events-calendar-detailEditDiv').hide();
            togglePopup();

            // 删除日程事件
            deleteEvent(eventId, eventRepeat, eventDate);
        });
        // 日程事件带编辑面板-编辑
        $('.fc-day').on('click', '.fc-day-events-editbtn', function(event) {
            event.stopPropagation(); // 阻止事件冒泡
            var eventId = $(this).data('id');
            var eventDate = $(this).data('date');

            $(this).closest('.fc-day-events-calendar-detailEditDiv').hide();
            togglePopup();

            getUpdateEvent(eventId, eventDate);
        });
        // 按钮样式
        $('.fc-day-events-calendar-edit-btn').button();

    }

    // 日期列表绑定事件
    function bindListDayEvent() {
        // 实现鼠标悬浮表格效果
        $("tr:gt(0)").mouseover(function() {
                $(this).css('background-color', '#eee');
            })
            .mouseout(function() {
                $(this).css('background-color', 'white');
            });

        // 悬浮日程主题显示详情
        $('.fc-list-subject').hover(function() {
            var clonedElement = $(this).siblings('.fc-list-detailDiv').clone();
            // 将复制的元素添加到文档的根节点下
            clonedElement.appendTo($('#listGridMonth'));
            $('#listGridMonth').children('.fc-list-detailDiv').show();

            // 悬浮信息显示位置优化 //
            var pageScrollTop = $('html').scrollTop();  //页面滑动距离
            // 获取鼠标位置
            var mouseX = $(this).offset().left + $(this).outerWidth() / 2;
            var mouseY = $(this).offset().top + $(this).outerHeight() / 2;
            // 获取提示框尺寸
            var tooltipWidth = $(this).siblings('.fc-list-detailDiv').outerWidth();
            var tooltipHeight = $(this).siblings('.fc-list-detailDiv').outerHeight();
            // 计算提示框位置
            var left = mouseX;
            var top = mouseY;
            // 确保提示框不会超出屏幕边界
            var screenWidth = $(window).width();
            var screenHeight = $(window).height();
            if (left + tooltipWidth > screenWidth) {
                left = mouseX - tooltipWidth - $(this).outerWidth() / 2;
            } else if (left < 0) {
                left = 0;
            }
            else {
                left += 20;
            }
            if (top + tooltipHeight - pageScrollTop > screenHeight) {
                top = screenHeight - tooltipHeight - 10;
            } else if (top < 0) {
                top = 0;
            }
            else {
                top = top - pageScrollTop + 5;
            }
            // 设置提示框位置
            $('#listGridMonth').children('.fc-list-detailDiv').css({
                top: top,
                left: left
            });
        }, function() {
            $('#listGridMonth').children('.fc-list-detailDiv').remove();
        });
        // 悬浮日程时间显示详情
        $('.fc-list-event-time').hover(function() {
            // 事件主题
            var elem = $(this).siblings('.fc-list-event-subject').find('.fc-list-detailDiv');

            var clonedElement = elem.clone();
            // 将复制的元素添加到文档的根节点下
            clonedElement.appendTo($('#listGridMonth'));
            $('#listGridMonth').children('.fc-list-detailDiv').show();

            // 悬浮信息显示位置优化 //
            var pageScrollTop = $('html').scrollTop();  //页面滑动距离
            // 获取鼠标位置
            var mouseX = $(this).offset().left + $(this).outerWidth() / 2;
            var mouseY = $(this).offset().top + $(this).outerHeight() / 2;
            // 获取提示框尺寸
            var tooltipWidth = $(this).siblings('.fc-list-event-subject').find('.fc-list-detailDiv').outerWidth();
            var tooltipHeight = $(this).siblings('.fc-list-event-subject').find('.fc-list-detailDiv').outerHeight();
            // 计算提示框位置
            var left = mouseX;
            var top = mouseY;
            // 确保提示框不会超出屏幕边界
            var screenWidth = $(window).width();
            var screenHeight = $(window).height();
            if (left + tooltipWidth + 50 > screenWidth) {
                left = mouseX - tooltipWidth - $(this).outerWidth() / 2;
            } else if (left < 0) {
                left = 0;
            }
            else {
                left += $(this).outerWidth() / 2;
            }

            if (top + tooltipHeight - pageScrollTop > screenHeight) {
                top = screenHeight - tooltipHeight - 10;
            } else if (top < 0) {
                top = 0;
            }
            else {
                top = top - pageScrollTop + 5;
            }
            // 设置提示框位置
            $('#listGridMonth').children('.fc-list-detailDiv').css({
                top: top,
                left: left
            });
        }, function() {
            $('#listGridMonth').children('.fc-list-detailDiv').remove();
        });

        // 点击日程主题显示详情-呼出待按钮面板
        $('.fc-list-subject').click(function() {
            $(this).siblings('.fc-day-events-calendar-detailEditDiv').show();
            togglePopup();
        });
        // 点击日程时间详情-呼出待按钮面板
        $('.fc-list-time').click(function() {
            $(this).siblings('.fc-list-event-subject').find('.fc-day-events-calendar-detailEditDiv').show();
            togglePopup();
        });

        // 日程事件带编辑面板-关闭
        $('.fc-day-events-exitbtn').click(function() {
            $(this).closest('.fc-day-events-calendar-detailEditDiv').hide();
            togglePopup();
        });
        // 日程事件带编辑面板-编辑
        $('.fc-day-events-editbtn').click(function() {
            var eventId = $(this).data('id');
            var eventDate = $(this).data('date');

            $(this).closest('.fc-day-events-calendar-detailEditDiv').hide();
            togglePopup();

            getUpdateEvent(eventId, eventDate);
        });
        // 日程事件带编辑面板-删除
        $('.fc-day-events-delbtn').click(function() {
            var eventId = $(this).data('id');
            var eventRepeat = $(this).data('repeat');
            var eventDate = $(this).data('date');

            $(this).closest('.fc-day-events-calendar-detailEditDiv').hide();
            togglePopup();

            // 删除日程事件
            deleteEvent(eventId, eventRepeat, eventDate);
        });


        // 显示日历选择器
        $("#dtSelectDate").datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            dayNamesMin: ['日', '一', '二', '三', '四', '五', '六'],
            monthNamesShort: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
            beforeShow: function(selectedDate, inst) {},
            onClose: function(selectedDate) {},
            onSelect: function(selectedDate, inst) {
                var currentDate = new Date(selectedDate);
                showEventsList(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate());
            }
        });

        // 按钮样式
        $('.fc-day-events-calendar-edit-btn').button();
    }

    function beforeSubmit() {
        // 数据校验
        if ($("#sSubject").val() == "") {
            $('.sSubjectResult').html('事件主题为必填');
            $('#sSubject').focus().addClass('ui-state-error');
            return;
        }
        if ($("#sBeginDate").val() == "" || $("#sBeginTime").val() == "") {
            $('.sBeginResult').html('开始时间为必填');
            return;
        }
        if ($("#nRemind").val() == 70) { //提醒-自定义 时间不能晚于开始时间
            if ($("#sRemindDate").val() == "") {
                $('.nRemindResult').html('自定义提醒时间为必填');
                return;
            }
            var sRemindDateTime = $("#sRemindDate").val();
            var sRemindDateTime2 = new Date(sRemindDateTime);
            var sBeginDateTime = $("#sBeginDate").val();
            var sBeginDateTime2 = new Date(sBeginDateTime);
            if (sRemindDateTime2.getTime() > sBeginDateTime2.getTime()) {
                $('.sRemindResult').html('提醒时间不得晚于开始时间');
                return;
            }
            if (sBeginDateTime2.getTime() - sRemindDateTime2.getTime() > 100 * 24 * 60 * 60 * 1000) {   // 提前提醒小于100天
                $('.sRemindResult').html('提前提醒不得超过100天');
                return;
            }
        }
        if($('#nRepeat').val() > 10){  //重复时间区间
            if($("#sRepeatStartDate").val() == '' || $("#sRepeatEndDate").val() == ''){
                $('.sRepeatSectionResult').html('重复时间区间为必填');
                return;
            }
        }
        return true;
    }

    // 新建修改事件对话框
    function showAddEventDialog(arg, flag = 0, eventDate) {
        var sId = "";
        var title = "新建事件";

        if (flag == 1) { //修改
            sId = arg.sId;
            title = "修改事件";
            // eventDate  点击的日期
            $('#sSubject').val(arg.sSubject);
            $('#nRemind').val(arg.nRemind);
            $('#sBeginDate,#sRepeatStartDate').val(eventDate);
            $('#sBeginTime').val(arg.sBeginTime.slice(0, 5));
            if (arg.nRemind == 70) {  //自定义提醒时间
                if(arg.nRepeat != 10){ //且为重复事件-根据设置规律显示
                    var sBeginTimeRemind = (new Date(eventDate)).getTime() - ( (new Date(arg.sBeginDate)).getTime() - (new Date(arg.sRemindDate)).getTime() );
                    $('#sRemindDate').val(formatDate(sBeginTimeRemind));
                }
                else $('#sRemindDate').val(arg.sRemindDate);
                $('.nRemindCustomize').show();
            }
            if(arg.nRepeat > 10){  //重复时间区间
                $('.sRepeatSection').show();
            }
            else $('.sRepeatSection').hide();
            $("#sRepeatEndDate").val(arg.sRepeatEndDate);
            $('#nRepeat').val(arg.nRepeat);
            $('#sNote').val(arg.sNote);

            // 按钮-添加删除按钮
            var buttons = [
                    {
                        text: "提交",
                        class: "", // 自定义样式类
                        click: function() {
                            // 数据校验
                            if (!(beforeSubmit())) return;

                            // 若为重复事件
                            if (arg.nRepeat && arg.nRepeat > 10) {
                                showdelrepeatEventDialog(sId);
                                return false;
                            }

                            submitAddEvent(sId);
                        }
                    },
                    {
                        text: "取消",
                        class: "", // 自定义样式类
                        click: function() {
                            $(this).dialog('close');
                        }
                    },
                    {
                    text: "删除",
                    class: "ui-dialog-delbtn", // 自定义样式类
                    click: function() {
                        console.log('删除');
                        deleteEvent(arg.sId, arg.nRepeat, eventDate);
                        $(this).dialog('close');
                    }
                }
            ];
        } else { //新建
            if (arg) {
                $('#sBeginDate,#sRemindDate,#sRepeatStartDate').val(formatDate(arg));
                $('#sBeginTime').val('09:00');
            } else {
                $('#sBeginDate,#sRemindDate,#sRepeatStartDate').val(formatDate());
                $('#sBeginTime').val('09:00');
            }
            $('#sSubject,#sNote,#nRemind,#nRepeat,#sRepeatEndDate').val('');
            $('.nRemindCustomize,.sRepeatSection').hide();

            // 按钮
            var buttons = [
                {
                    text: "提交",
                    class: "", // 自定义样式类
                    click: function() {
                        // 数据校验
                        if (!(beforeSubmit())) return;
                        submitAddEvent(sId);
                    }
                },
                {
                    text: "取消",
                    class: "", // 自定义样式类
                    click: function() {
                        $(this).dialog('close');
                    }
                }
            ];
        }
        $(".roa-form input.text").removeClass('ui-state-error');
        $(".inputResult").html("");
        $('#div_chg_addEvent').dialog({
            autoOpen: true,
            bgiframe: true,
            minWidth: 700,
            minHeight: 240,
            draggable: true,
            resizable: false,
            modal: true,
            closeOnEscape: true,
            title: title,
            close: function(event, ui) {},
            beforeclose: function(event, ui) {},
            create: function(event,ui) {},
            buttons: buttons,
        });
        $('div.ui-dialog-titlebar').show();
    }

    // 构建日历的HTML结构
    function buildCalendar(year, month) {
        if (!isValidDate(year, month + 1)) {
            var today = Today();
            year = today.year;
            month = today.month;
        }

        $.ajax({
            url: "request/roa_tool_work_calendar_data.php?do=getCalendar",
            type: "POST",
            data: {
                "year": year,
                "month": month + 1
            },
            dataType: "json",
            success: function(data) {
                $('html').scrollTop(0);

                $('.fc-header-title-year').text(year);
                $('.fc-header-title-month').text(month + 1);
                $('#mainGridView').html(data);

                bindCalendarDayEvent();
            }
        });
    }



    // 获取日历当前日期
    function getCurrentDate() {
        var currentDate = {};

        var titleYear = parseInt($('.fc-header-title-year').text());
        var titleMonth = parseInt($('.fc-header-title-month').text());
        // 验证年份是否有效(实际日期数字)
        if (isValidDate(titleYear, titleMonth)) {
            currentDate = {
                year: titleYear,
                month: titleMonth,
            };
        }
        return currentDate;
    }

    // 校验日期年月是否有效(实际日期数字)
    function isValidDate(year, month) {
        if (!isNaN(year) && year >= 1900 && year <= 2100) {
            if (!isNaN(month) && month >= 1 && month <= 12) {
                return true;
            }
        }
        return false;
    }

    // 获取今日日期对象
    function Today() {
        var todayDate = {};

        // 获取当前日期
        var currentDate = new Date();
        var currentYear = currentDate.getFullYear();
        var currentMonth = currentDate.getMonth();
        var currentDay = currentDate.getDate();
        todayDate = {
            year: currentYear,
            month: currentMonth,
            day: currentDay
        };

        return todayDate;
    }

    // Goto today
    function gotoToday() {
        buildCalendar(Today().year, Today().month);
    }

    // 上一月
    function preMonth() {
        var currentDate = getCurrentDate();
        if (currentDate.year && currentDate.month) {
            var currentYear = parseInt(currentDate.year);
            var currentMonth = parseInt(currentDate.month) - 2;
            if (currentMonth < 0) { //当前为1月时，则跳转到上一年的12月
                currentYear = currentYear - 1;
                currentMonth = 11;
            }
            buildCalendar(currentYear, currentMonth);
        }
    }

    // 下一月
    function nextMonth() {
        var currentDate = getCurrentDate();
        if (currentDate.year && currentDate.month) {
            var currentYear = parseInt(currentDate.year);
            var currentMonth = parseInt(currentDate.month);
            if (currentMonth > 11) { //当前为12月时，则跳转到下一年的1月
                currentYear = currentYear + 1;
                currentMonth = 0;
            }
            buildCalendar(currentYear, currentMonth);
        }
    }

    // 事件列表
    function showEventsList(year, month, day) {
        // 校验是否有效日期
        if (!isValidDate(year, month + 1)) {
            var today = Today();
            year = today.year;
            month = today.month;
            day = today.day;
        }
        $.ajax({
            url: "request/roa_tool_work_calendar_data.php?do=getEventsList",
            type: "POST",
            data: {
                "year": year,
                "month": month + 1,
                "day": day
            },
            dataType: "json",
            success: function(data) {
                $('#mainGridView').html(data);

                bindListDayEvent();

                // 日期选择器
                $("#dtSelectDate").datepicker("setDate", new Date(year, month, day)); //默认设置选择的日期
                // 日期选择器样式调整
                $('#dtSelectDate .ui-datepicker-next,#dtSelectDate .ui-datepicker-prev').addClass('ui-state-hover').css('border', 'none');
                $('#dtSelectDate .ui-datepicker-header').removeClass('ui-corner-all');
                $('#dtSelectDate .ui-datepicker-next,#dtSelectDate .ui-datepicker-prev,#dtSelectDate .ui-corner-all').hover(function() {
                        $(this).addClass('ui-state-hover');
                        if($(this).hasClass('ui-datepicker-next') || $(this).hasClass('ui-datepicker-prev')) $(this).css('border', '1px solid #cdd5da');
                    }, function() {
                        if($(this).hasClass('ui-datepicker-next') || $(this).hasClass('ui-datepicker-prev')) $(this).addClass('ui-state-hover').css('border', 'none');
                    }
                );

                // 事件列表的外边框显示
                if($('.fc-list-not-available').length > 0) $('.fc-table-list-box').css('border', 'none');
                else $('.fc-table-list-box').css('border', '1px solid #ddd');

                // 定位到选择日期的位置
                var dateTitle = year + "-" + (month + 1) + "-" + day;
                dateTitle = formatDate(dateTitle);
                const selector = '#dateTitle' + dateTitle;
                const id = $(selector);
                if (id.length > 0) {
                    var offset = id.offset();
                    $('.fc-table-list-box').scrollTop(offset.top - 90);
                }

            }
        });

    }

    // 删除日程事件
    function deleteEvent(sid, repeat, date) {
        if (repeat > 10) {
            console.log("重复事件删除", sid);
            showdelrepeatEventDelDialog(sid, date);
            return;
        }
        if (!confirm("确定要删除吗？")) return;
        // 校验
        $.ajax({
            url: "request/roa_tool_work_calendar_data.php?do=deleteEvent",
            type: "POST",
            data: {
                "sid": sid,
            },
            dataType: "json",
            success: function(data) {
                if (data.msg[0] == "ERROR") {
                    alert(data.msg[1]);
                } else if (data.msg[0] == "S") {
                    alert("删除日程事件成功");
                    // 刷新当前页面
                    refreshPage();
                } else alert("操作失败");
            }
        });
    }

    // 显示重复事件编辑确认框-删除
    function showdelrepeatEventDelDialog(sId, date) {
        $('#div_confirm_repeatEvent_del').dialog({
            autoOpen: true,
            bgiframe: true,
            minWidth: 550,
            minHeight: 240,
            draggable: true,
            resizable: false,
            modal: true,
            closeOnEscape: false,
            dialogClass: 'hide-dialog-titlebar',
            // title: '确认重复事件',
            close: function(event, ui) {},
            beforeclose: function(event, ui) {},
            buttons: {
                "仅删除今天事件": function() {
                    $(this).dialog('close');
                    submitDelEvent(sId, date, 1);
                },
                "删除所有重复事件": function() {
                    $(this).dialog('close');
                    submitDelEvent(sId, date, 2);
                },
                "取消": function() {
                    $(this).dialog('close');
                    submitDelEvent(sId, date, 3);
                },
            }
        });
        $('div.ui-dialog-titlebar').hide();
    }

    // 显示重复事件编辑确认框-修改
    function showdelrepeatEventDialog(sId) {
        $('#div_confirm_repeatEvent').dialog({
            autoOpen: true,
            bgiframe: true,
            minWidth: 550,
            minHeight: 240,
            draggable: true,
            resizable: false,
            modal: true,
            closeOnEscape: true,
            // title: '确认重复事件',
            close: function(event, ui) {},
            beforeclose: function(event, ui) {},
            buttons: {
                "仅修改今天事件": function() {
                    $(this).dialog('close');
                    submitAddEvent(sId, 1);
                },
                "修改所有重复事件": function() {
                    $(this).dialog('close');
                    submitAddEvent(sId, 2);
                },
                "取消": function() {
                    $(this).dialog('close');
                    submitAddEvent(sId, 3);
                },
            }
        });
        $('div.ui-dialog-titlebar').hide();
    }

    // 显示重复事件对话确认框-删除
    function submitDelEvent(sId, date, e = 0) {
        if (sId && e == 3) { //重复事件删除-点击取消
            $('#div_chg_addEvent—_del').dialog('close');
            return;
        }

        // $('.ui-dialog-title').append('<img src="images/icon_16x16_spinner.gif" alt="请稍候..." />');
        $('.ui-dialog button').attr('disabled', true).css({
            'cursor': 'default !important',
            'opacity': '0.35'
        });
        $.ajax({
            url: "request/roa_tool_work_calendar_data.php?do=deleteEvent",
            type: "POST",
            data: {
                "sid": sId,
                "date": date,
                "type": e
            },
            dataType: "json",
            success: function(data) {
                if (data.msg[0] == "ERROR") {
                    alert(data.msg[1]);
                } else if (data.msg[0] == "S") {
                    alert("删除日程事件成功");
                    // 刷新当前页面
                    refreshPage();
                } else alert("操作失败");
            }
        });
    }

    // 新建修改-对话框数据提交
    function submitAddEvent(sId, e = 0) {
        if (sId && e == 3) { //重复事件修改且点击取消
            $('#div_chg_addEvent').dialog('close');
            return;
        }

        var form = $('#dialog_form_addEvent');
        $('#div_chg_addEvent').dialog('close');

        $('.ui-dialog-title').append('<img src="images/icon_16x16_spinner.gif" alt="请稍候..." />');
        $('.ui-dialog button').attr('disabled', true).css({
            'cursor': 'default !important',
            'opacity': '0.35'
        });
        $.ajax({
            type: form.attr("method"),
            url: form.attr("action"),
            data: form.serialize() + "&sid=" + sId + "&updateRepeat=" + e,
            dataType: "json",
            success: function(data) {
                if (data.msg[0] == "ERROR") {
                    alert(data.msg[1]);
                } else if (data.msg[0] == "S") {
                    alert(data.msg[1]);
                    // 刷新当前页面
                    refreshPage();
                } else alert("操作失败");
            }
        });
    }

    // 获取修改事件
    function getUpdateEvent(sid, eventDate) {
        $.ajax({
            url: "request/roa_tool_work_calendar_data.php?do=getUpdateEvent",
            type: "POST",
            data: {
                "sid": sid,
            },
            dataType: "json",
            success: function(data) {
                showAddEventDialog(data, 1, eventDate);
            }
        });
    }

    // 刷新当前页面-列表页/日历表格页
    function refreshPage() {
        if ($('#listGridMonth').length > 0) { //若为列表页
            var calendarDate = $('#dtSelectDate').val();
            calendarDate = new Date(calendarDate);
            showEventsList(calendarDate.getFullYear(), calendarDate.getMonth(), calendarDate.getDate());
        } else { //默认日历表格页
            var calendarDate = getCurrentDate();
            buildCalendar(calendarDate.year, (calendarDate.month - 1));
        }
    }

    // 格式化时间
    function formatDate(date, format = 'YMD') {
        var result, year, month, day, hours, minutes;
        date = (date) ? new Date(date) : new Date();
        if (format == 'YM') {
            year = date.getFullYear();
            month = ("0" + (date.getMonth() + 1)).slice(-2); // 月份是从 0 开始的，所以加 1
            result = year + "-" + month;
        } else if (format == 'YMDHm') {
            year = date.getFullYear();
            month = ("0" + (date.getMonth() + 1)).slice(-2); // 月份是从 0 开始的，所以加 1
            hours = ("0" + date.getHours()).slice(-2);
            minutes = ("0" + date.getMinutes()).slice(-2);
            result = year + "-" + month + "-" + day + " " + hours + ":" + minutes;
        } else if (format == 'Hm') {
            hours = ("0" + date.getHours()).slice(-2);
            minutes = ("0" + date.getMinutes()).slice(-2);
            result = hours + ":" + minutes;
        } else {
            year = date.getFullYear();
            month = ("0" + (date.getMonth() + 1)).slice(-2); // 月份是从 0 开始的，所以加 1
            day = ("0" + date.getDate()).slice(-2);
            result = year + "-" + month + "-" + day;
        }
        return result;
    }

    // 显示或隐藏自定义的弹出层
    function togglePopup(id) {
        const overlay = document.querySelector('.overlay');

        // 显示或隐藏弹出层
        if (overlay.style.display === 'none' || !overlay.style.display) {
            overlay.style.display = 'block';
            // popup.style.display = 'block';
        } else {
            overlay.style.display = 'none';
            // popup.style.display = 'none';
        }
    }

    // 日历表格动态样式切换-移除样式
    function calendarRemoveClass() {
        // 去除所有弹出框或行为样式
        $('.fc-table .fc-day-events-calendar-detailEditDiv').hide(); // 隐藏所有展开的编辑面板
        $('.fc-day').removeClass('fc-day-selected'); //移除被选择的日历表格样式
        $('.fc-day-box-overflow-border-selected').scrollTop(0);  // more容器滚动条回到顶部
        $('.fc-day-box').removeClass('fc-day-box-overflow-border-selected'); //移除点击more的日历表格样式
    }
</script>