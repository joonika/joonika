<?php

namespace Joonika\Idate;
use Joonika\Modules\Ws\Ws;

if (!defined('jk')) die('Access Not Allowed !');

include_once(__DIR__ . '/idate/jdf.php');

function idateReady()
{
	global $View;
	$View->footer_js_files('/includes/idate/assets/jquery.md.bootstrap.datetimepicker.js?v=2');
	$View->header_styles_files('/includes/idate/assets/jquery.md.bootstrap.datetimepicker.style.css');
}

function setDatetime($options = [])
{
	global $View;
	$option = [
		"name" => "datetime",
		"format" => 3,
		"inLine" => "false",
		"position" => "bottom",
		"disabled" => false,
		"lang" => 0,
		"disableBeforeToday" => "false",
		"rangeSelector" => false,
	];

	if (sizeof($option) >= 1) {
		foreach ($option as $key => $opt) {
			if (isset($options[$key])) {
				$option[$key] = $options[$key];
			}
		}
	}
	if ($option['lang'] == '0') {
		$option['lang'] = JK_LANG;
	}
	if ($option['lang'] != 'fa') {
		$isGregorian = 'true';
	} else {
		$isGregorian = 'false';
	}
    if ($option['format'] == 3) {
        $enabletimepicker = "false";
    } else {
        $enabletimepicker = "true";
    }

    if ($option['disabled'] == true) {
        $disabled = "disabled : true,";
    } else {
        $disabled = "";
    }
    if ($option['rangeSelector'] == true) {
        $rangeSelector = "rangeSelector : true,";
    } else {
        $rangeSelector = "";
    }


	$View->footer_js('
        <script type="text/javascript">
            $(\'#' . $option['name'] . '\').MdPersianDateTimePicker({
                placement: \'' . $option['position'] . '\',
                isGregorian: ' . $isGregorian . ',
                yearOffset: 100,
        		enableTimePicker: ' . $enabletimepicker . ',
        		targetTextSelector: \'#' . $option['name'] . '\',
                inLine: ' . $option['inLine'] . ',
                '.$disabled.'
                '.$rangeSelector.'
                disableBeforeToday: ' . $option['disableBeforeToday'] . ',
    });
        </script>
        ');
}

function datetoint($val, $format = 3, $lang = '0')
{
	if ($lang == '0') {
		$lang = JK_LANG;
	}

	$value = '';
	$val = str_replace('   ', ' ', $val);
	$val = str_replace('  ', ' ', $val);
	$val = str_replace('  ', ' ', $val);
	if ($format == 3) {

		if ($lang == 'fa') {
			$val = tr_num($val);
			$timerday = explode('/', $val);
			if (sizeof($timerday) >= 2) {
				$totdate = jalali_to_gregorian(intval($timerday[0]), intval($timerday[1]), intval($timerday[2]), '/');
				$value = $totdate;
			}
		} else {
			$value = $val;

		}
	} elseif ($format == 6) {
		if ($lang == 'fa') {
			$val = tr_num($val);
			$timerday = explode(' ', $val);
			$timerday2 = explode('/', $timerday[0]);
			if (sizeof($timerday2) >= 2) {
				$totdate = jalali_to_gregorian(intval($timerday2[0]), intval($timerday2[1]), intval($timerday2[2]), '/');
				$value = $totdate . ' ' . $timerday[1];
			}
		} else {
			$value = $val;
		}
	}
	return strtotime($value);
}

function datetodate($val, $formatSource = 3, $langSource = '0',$formatDest=6,$langDest="en")
{
    if ($langSource == '0') {
        $langSource = JK_LANG;
    }

    $value=datetoint($val,$formatSource,$langSource);
    if(is_numeric($formatDest)){
        if ($formatDest == 3) {
            $format = "Y/m/d";
        } else {
            $format = "Y/m/d H:i:s";
        }
    }else{
        $format=$formatDest;
    }
    $newVal=date_int($format,$value,$langDest);
    return $newVal;
}

function dateToDateRange($val, $formatSource = 3, $langSource = '0',$formatDest=6,$langDest="en")
{
    if ($langSource == '0') {
        $langSource = JK_LANG;
    }
    $exploder="to";

    if($langSource=="fa"){
        $exploder="تا";
    }



    $explode=explode($exploder,$val);
    if(sizeof($explode)!=2){
    $explode=explode("-",$val);
    }
    $from=trim($explode[0]);
    $to=trim($explode[1]);

    $valueFrom=datetoint($from,$formatSource,$langSource);
    if(is_numeric($formatDest)){
        if ($formatDest == 3) {
            $format = "Y/m/d";
        } else {
            $format = "Y/m/d H:i:s";
        }
    }else{
        $format=$formatDest;
    }
    $newValFrom=date_int($format,$valueFrom,$langDest);

    $valueTo=datetoint($to,$formatSource,$langSource);
    if(is_numeric($formatDest)){
        if ($formatDest == 3) {
            $format = "Y/m/d";
        } else {
            $format = "Y/m/d H:i:s";
        }
    }else{
        $format=$formatDest;
    }
    $newValTo=date_int($format,$valueTo,$langDest);

    return [$newValFrom,$newValTo];
}

function date_int($format, $int = '', $lang = '0')
{
    if ($int == '') {
        $int = time();
    }
    if ($format == '') {
        $format = "Y/m/d H:i:s";
    }

	if ($lang == "0" && defined("JK_LANG")) {
		$lang = JK_LANG;
	}

	if (!is_numeric($int)) {
		$int = strtotime($int);
	}
	if ($lang == 'fa') {
		return tr_num(jdate($format, $int));
	} else {
		return date($format, $int);
	}
}

function field_date($options = [])
{
	global $data;
	global $View;
	$return = '';
	$option = [
		"title" => '',
		"form-group" => true,
		"form-group-class" => '',
		"value" => '',
		"forceVal" => false,
		"name" => '',
		"format" => 3,
		"inLine" => "false",
		"position" => 'bottom',
		"lang" => 0,
		"disabled" => false,
		"attr" => [
			"data-msg" => __("this field is required")
		],
		"ColType" => '12,12',
		"help" => '',
		"required" => false,
		"direction" => "ltr",
		"disableBeforeToday" => "false",
		"rangeSelector" => false
	];
	if (sizeof($option) >= 1) {
		foreach ($option as $key => $opt) {
			if (isset($options[$key])) {
				$option[$key] = $options[$key];
			}
		}
	}
	if (!isset($options['title'])) {
		$option['title'] = $option['name'];
	}
	if (!isset($options['value'])) {
		if (isset($data[$option['name']])) {
			$option['value'] = $data[$option['name']];
		}
	}

	$atts = '';
	if (sizeof($option['attr']) >= 1) {
		foreach ($option['attr'] as $key => $v) {
			$atts.= ' ' . $key . '="' . $v . '" ';
		}
	}
	if ($option['format'] == 3) {
		$format = "Y/m/d";
	} else {
		$format = "Y/m/d H:i:s";
	}
	if($option['value']!=""){
	    if($option['forceVal']!=true){
        $value = date_int($format, $option['value']);
        }else{
	        $value=$option['value'];
        }
    }else{
	    $value='';
    }
	$optioncols = explode(',', $option['ColType']);
	setDatetime([
		"name" => $option['name'],
		"format" => $option['format'],
		"inLine" => $option['inLine'],
		"position" => $option['position'],
		"disabled" => $option['disabled'],
		"rangeSelector" => $option['rangeSelector'],
		"lang" => $option['lang'],
		"disableBeforeToday" => $option['disableBeforeToday'],
	]);
	if ($option['form-group'] == true) {
		$return .= '
        <div class="form-group  '.$option['form-group-class'].'">
        <div class=""><div class="row">
        <label class="col-md-' . $optioncols[0] . ' control-label">
            ' . $option['title'] . '
        </label>

        <div class="col-md-' . $optioncols[1] . '">
        ';
	}

    $requ = '';
	if ($option['required'] == true) {
		$requ = 'required="required"';
	}
	$read='';
	if($option['disabled']=="true"){
	    $read="readonly";
    }

    $return .= '
                <input type="text" data-offset="-50%" class="form-control w-100  ' . $option['direction'] . '" '.$read.' readonly name="' . $option['name'] . '"  id="' . $option['name'] . '" value="' . $value . '"  ' . $requ . ' ' . $atts . ' >
    ';

	if ($option['form-group'] == true) {
		if ($option['help'] != '') {
			$return .= '
        <span class="help-block ' . $option['direction'] . '">' . $option['help'] . '</span>
        ';
		}
		$return .= '</div></div></div>
    </div>';
	}

	return $return;

}

function field_time($options = [])
{
	global $data;
	global $View;
	$return = '';
	$option = [
		"title" => '',
		"form-group" => true,
		"value" => '',
		"name" => '',
		"format" => 'HH:mm',
		"attr" => [
			"data-msg" => __("this field is required")
		],
		"ColType" => '4,8',
		"help" => '',
		"required" => false,
		"direction" => JK_DIRECTION
	];
	if (sizeof($option) >= 1) {
		foreach ($option as $key => $opt) {
			if (isset($options[$key])) {
				$option[$key] = $options[$key];
			}
		}
	}
	if (!isset($options['title'])) {
		$option['title'] = $option['name'];
	}
	if (!isset($options['value'])) {
		if (isset($data[$option['name']])) {
			$option['value'] = $data[$option['name']];
		}
	}

	$atts = '';
	if (sizeof($option['attr']) >= 1) {
		foreach ($option['attr'] as $key => $v) {
			$atts = ' ' . $key . '="' . $v . '" ';
		}
	}

	$value = rawurldecode($option['value']);

	$optioncols = explode(',', $option['ColType']);

	$View->footer_js('
        <script>
        $(function () {
                $(\'#' . $option['name'] . '\').datetimepicker({
                    format: \'' . $option['format'] . '\',
                });
            });
        </script>
        ');

	if ($option['form-group'] == true) {
		$return .= '
        <div class="form-group">
        <label class="col-md-' . $optioncols[0] . ' control-label">
            ' . $option['title'] . '
        </label>

        <div class="col-md-' . $optioncols[1] . '">
        ';
	}
	$requ = '';
	if ($option['required'] == true) {
		$requ = 'required="required"';
	}
	$return .= '
                <input type="text" class="form-control ' . $option['direction'] . '" name="' . $option['name'] . '"  id="' . $option['name'] . '" value="' . $value . '"  ' . $requ . ' ' . $atts . ' >
    ';

	if ($option['form-group'] == true) {
		if ($option['help'] != '') {
			$return .= '
        <span class="help-block ' . $option['direction'] . '">' . $option['help'] . '</span>
        ';
		}
		$return .= '</div>
    </div>';
	}
	return $return;

}

function dateRange($first, $last, $step = '+1 day', $format = 'Y/m/d')
{

	$dates = array();
	$current = strtotime($first);
	$last = strtotime($last);

	while ($current <= $last) {

		$dates[] = date($format, $current);
		$current = strtotime($step, $current);
	}

	return $dates;
}