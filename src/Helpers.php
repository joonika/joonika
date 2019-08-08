<?php

use Joonika\Modules\Ws\Ws;

if (!defined('jk')) die('Access Not Allowed !');
if (!function_exists('jk_die')) {

    /**
     * @param string $message
     */
    function jk_die($message = "")
    {
        echo $message;
        die;
    }
}

/*------ create event log function --------*/
if (!function_exists('logInsert')) {
    function logInsert($options = [])
    {
        global $database;
        $from = null;
        if (isset($options['from'])) {
            $from = $options['from'];
            if (is_array($from)) {
                $from = json_encode($options['from'], JSON_UNESCAPED_UNICODE);
            }
        }

        $to = null;
        if (isset($options['to'])) {
            $to = $options['to'];
            if (is_array($to)) {
                $to = json_encode($options['to'], JSON_UNESCAPED_UNICODE);
            }
        }
        $options = [
            "userID" => isset($options['userID']) ? $options['userID'] : '',
            "module" => isset($options['module']) ? $options['module'] : '',
            "moduleId" => isset($options['moduleID']) ? $options['moduleID'] : '',
            "type" => isset($options['type']) ? $options['type'] : '',
            "typeID" => isset($options['typeID']) ? $options['typeID'] : '',
            "from" => $from,
            "to" => $to,
            "description" => isset($options['description']) ? $options['description'] : '',
            "createdOn" => date('Y-m-d H:i:s'),
        ];
        $database->insert("jk_logs", $options);
    }
}

/*--------------die and dump function--------------*/
if (!function_exists('dd')) {
    function dd($inp)
    {
        if (isset($inp)) {
            if (is_array($inp) or is_object($inp)) {
                echo '<pre class="text-left " dir="ltr">';
                print_r($inp);
//                var_dump($inp);
                echo '</pre>';
                die();
            } else {
                echo '<pre>' . $inp . '</pre><br>';
                die();
            }
        } else {
            echo "variable {$inp} is not set or null";
            die;
        }
    }
}

/*----------function for test form input data------------*/
if (!function_exists('test_input')) {
    function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}

/*function for create alert message*/
if (!function_exists('message')) {
    function message($msg, $type = 'danger')
    {
        ?>
        <div class="alert alert-<?php echo $type; ?> alert-dismissible">
            <span class="close" data-dismiss="alert">
                &times;
            </span>
            <p><?php echo $msg; ?></p>
        </div>
        <?php
    }
}

//shuffle str argument
if (!function_exists('str_rand')) {
    function str_rand($input)
    {
        return str_shuffle($input);
    }
}

//
if (!function_exists('str_limit')) {
    function str_limit($string, $limit)
    {
        return substr($string, 0, $limit);
    }
}

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}


if (!function_exists('redirect_to')) {
    function redirect_to($url = "")
    {
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('__')) {
    function __($msg, $ucfirst = 0)
    {
        global $translate;
        if (isset($translate[$msg])) {
            if ($ucfirst == 1) {
                $return = ucfirst($translate[$msg]);
            } else {
                $return = $translate[$msg];
            }
        } else {
            if ($ucfirst == 1) {
                $return = ucfirst($msg);
            } else {
                $return = $msg;
            }
        }
        return $return;

    }


}

if (!function_exists('__e')) {
    function __e($msg, $ucfirst = 0)
    {
        echo __($msg, $ucfirst);
    }
}

function error404($return = true)
{
    http_response_code(404);
    if ($return) {
        ?>
        <div id="notfound">
            <div class="notfound">
                <div class="notfound-404">
                    <h1>404</h1>
                </div>
                <h2><?php __e("Oops! Not Found"); ?></h2>
            </div>
        </div>
        <?php
    }
}

function error403($return = true)
{
    http_response_code(403);
    if ($return) {
        ?>
        <div id="accessdenied">
            <div class="accessdenied">
                <div class="accessdenied-403">
                    <h1>403</h1>
                </div>
                <h2><?php __e("Oops! Access Denied"); ?></h2>
            </div>
        </div>
        <?php
    }
}

function loading_fa($options = [])
{
    $opt = [
        "class" => 'text-center',
        "iclass" => '0',
        "iclass-size" => '3',
        "elem" => 'div',
        "text-sr" => __("loading, please wait"),
        "text" => "",
    ];

    if (is_array($options) && sizeof($options) >= 1) {
        foreach ($options as $tr => $val) {
            if (isset($opt[$tr])) {
                $opt[$tr] = $val;
            }
        }
    }
    $butshow_start = '';
    $butshow_end = '';
    if ($opt['elem'] != '') {
        $butshow_start .= '<' . $opt['elem'];
        if ($opt['class'] != '') {
            $butshow_start .= ' class="' . $opt['class'];

            $butshow_start .= '" ';
        }
        if ($opt['iclass'] == 0) {
            $opticlass = 'fa fa-spinner fa-pulse fa-fw fa-' . $opt['iclass-size'] . 'x';
        } else {
            $opticlass = $opt['iclass'];
        }
        if ($opt['text'] != '') {
            $opttext = $opt['text'];
        } else {
            $opttext = '';
        }
        $butshow_start .= '><i class="' . $opticlass . '"></i><span class="sr-only">' . $opt['text-sr'] . ' ...</span>' . $opttext;

        $butshow_end .= '</' . $opt['elem'] . '>';
    }

    $butshow = $butshow_start . $butshow_end;

    return $butshow;
}


function ajax_load($options = [])
{
    $option = [
        "url" => '',
        "on" => '',
        "formID" => '',
        "validate" => false,
        "prevent" => false,
        "nonEmpty" => false,
        "type" => 'post',
        "data" => '$(this).serialize()',
        "success_response" => '',
        "error_response" => '',
        "error_swal" => true,
        "type_response" => 'html',
        "success_response_after" => '',
        "loading" => [],
    ];
    $return = '';
    if (sizeof($option) >= 1) {
        foreach ($option as $key => $opt) {
            if (isset($options[$key])) {
                $option[$key] = $options[$key];
            }
        }
    }
    if (substr($option['formID'], 0, 1) != '[') {
        $option['formID'] = "#" . $option['formID'];
    }
    if ($option['on'] != '') {
        if ($option['formID'] != '') {
            if ($option['prevent'] == true) {
                $return .= '$("' . $option['formID'] . '").on("' . $option['on'] . '",function(e){
            e.preventDefault();
            ';
            } else {
                $return .= '$("' . $option['formID'] . '").on("' . $option['on'] . '",function(){
            ';
            }
        }
    }
    $return .= 'var datas=' . $option['data'] . ';
    ';
    if ($option['loading'] != '') {

        $return .= '$("' . $option['success_response'] . '").' . $option['type_response'] . '(\'' . loading_fa($option['loading']) . '\');';
    }
    $error_response = 'swal ( "' . __("An error occurred") . '" ,  "' . __("loading page failed") . ' \n ' . $option['url'] . '" ,  "error",{
          buttons: "' . __("ok") . '!",
        });';
    if ($option['error_swal'] == false) {
        $error_response = "";
    }
    $error_response .= $option['error_response'];
    if ($option['nonEmpty'] && $option['on'] == 'change') {
        $return .= '
       if($(this).val()!=""){
       ';
    }
    $return .= '
    $.ajax({
        url: "' . $option['url'] . '",
        type: "' . $option['type'] . '",
        data: datas,
        success: function (response) {
            $("' . $option['success_response'] . '").' . $option['type_response'] . '(response);
            ' . $option['success_response_after'] . '
        }, error: function () {
        ' . $error_response . '
        
}
    });
    ';
    if ($option['nonEmpty'] && $option['on'] == 'change') {
        $return .= '
       }else{
        $("' . $option['success_response'] . '").html("");      
       }
       ';
    }

    if ($option['on'] != '') {
        if ($option['formID'] != '') {

            $return .= '
                return false
});
 ';

        }
    }
    return $return;
}

function ajax_validate($options = [])
{
    $option = [
        "url" => '',
        "on" => '',
        "formID" => '',
        "validate" => false,
        "prevent" => false,
        "ckeditor" => false,
        "type" => 'post',
        "data" => '$(form).serialize()',
        "type_response" => 'html',
        "success_response" => '',
        "success_response_after" => '',
        "loading" => false,
        "error_swal" => true,
        "error_response" => "",
    ];
    $return = '';
    if (sizeof($option) >= 1) {
        foreach ($option as $key => $opt) {
            if (isset($options[$key])) {
                $option[$key] = $options[$key];
            }
        }
    }
    if (substr($option['formID'], 0, 1) != '[') {
        $option['formID'] = "#" . $option['formID'];
    }
    $return .= '
$(document).ready(function() {

            $("' . $option['formID'] . '").validate({
                onfocusout: false,
            ignore: \'.select2-search__field\', // ignore hidden fields
        errorClass: \'validation-error-label\',
        successClass: \'validation-valid-label\',
        highlight: function(element, errorClass) {
            $(element).removeClass(errorClass);
        },
        unhighlight: function(element, errorClass) {
            $(element).removeClass(errorClass);
        },

        // Different components require proper error label placement
        errorPlacement: function(error, element) {

            // Styled checkboxes, radios, bootstrap switch
            if (element.parents(\'div\').hasClass("checker") || element.parents(\'div\').hasClass("choice") || element.parent().hasClass(\'bootstrap-switch-container\') ) {
                if(element.parents(\'label\').hasClass(\'checkbox-inline\') || element.parents(\'label\').hasClass(\'radio-inline\')) {
                    error.appendTo( element.parent().parent().parent().parent() );
                }
                 else {
                    error.appendTo( element.parent().parent().parent().parent().parent() );
                }
            }

            // Unstyled checkboxes, radios
            else if (element.parents(\'div\').hasClass(\'checkbox\') || element.parents(\'div\').hasClass(\'radio\')) {
                error.appendTo( element.parent().parent().parent() );
            }

            // Input with icons and Select2
            else if (element.parents(\'div\').hasClass(\'has-feedback\') || element.hasClass(\'select2-hidden-accessible\')) {
                error.appendTo( element.parent() );
            }

            // Inline checkboxes, radios
            else if (element.parents(\'label\').hasClass(\'checkbox-inline\') || element.parents(\'label\').hasClass(\'radio-inline\')) {
                error.appendTo( element.parent().parent() );
            }

            // Input group, styled file input
            else if (element.parent().hasClass(\'uploader\') || element.parents().hasClass(\'input-group\')) {
                error.appendTo( element.parent().parent() );
            }

            else {
                error.insertAfter(element);
            }
        },
        validClass: "validation-valid-label",
         success: function(label) {
            label.addClass("validation-valid-label")
        },
  submitHandler: function(form) {

';
    if ($option['ckeditor']) {
        $return .= '

for (instance in CKEDITOR.instances) {
                    CKEDITOR.instances[instance].updateElement();
                }

                        ';
    }

    $return .= 'var datas=' . $option['data'] . ';
    ';
    if ($option['loading'] != false) {
        $return .= '$("' . $option['success_response'] . '").' . $option['type_response'] . '(\'' . loading_fa($option['loading']) . '\');';
    }
    $error_response = 'swal ( "' . __("An error occurred") . '" ,  "' . __("loading page failed") . ' \n ' . $option['url'] . '" ,  "error",{
          buttons: "' . __("ok") . '!",
        });';
    if ($option['error_swal'] == false) {
        $error_response = "";
    }
    $error_response .= $option['error_response'];
    $return .= '
    $.ajax({
        url: "' . $option['url'] . '",
        type: "' . $option['type'] . '",
        data: datas,
        success: function (response) {
            $("' . $option['success_response'] . '").' . $option['type_response'] . '(response);
            ' . $option['success_response_after'] . '
        }, error: function () {
        ' . $error_response . '
}
    });
    ';


    $return .= '

                }
                 });
                 });

                ';
    global $View;
    $View->footer_js_files('/modules/cp/assets/js/jquery-validation/jquery.validate.min.js');
    return $return;
}

function hashpass($password)
{
    $hash = password_hash($password, PASSWORD_BCRYPT, array("cost" => 10));
    // you can set the "complexity" of the hashing algorithm, it uses more CPU power but it'll be harder to crack, even though the default is already good enough
    return $hash;
}

function redirect_to_js($url = '', $timeout = 0, $withScript = true)
{
    $txt = '';
    if ($withScript) {
        $txt .= '<script>';
    }
    if ($url == "") {
        $txt .= "setTimeout(function() {
            document.location.reload();
        }, " . $timeout . ");";
    } else {
        $txt .= "setTimeout(function() {
            window.location = '" . $url . "'
        }, " . $timeout . ");";
    }

    if ($withScript) {
        $txt .= '</script>';
    }
    return $txt;
}

function nickName($userID, $company = true)
{
    global $database;
    $user = $database->get("jk_users", ['name', 'family', 'companyName', 'status', 'nickname'], [
        "id" => $userID
    ]);
    $stText = "";
    if ($user['companyName'] != "" && $company) {
        $stText .= ' ' . $user['companyName'];
    }

    $nickname = trim($user['name'] . ' ' . $user['family'] . $stText);
    if ($nickname != $user['nickname']) {
        $database->update('jk_users', [
            "nickname" => trim($user['name'] . ' ' . $user['family'])
        ], [
            "id" => $userID
        ]);
    }
    return trim($nickname);
}

function userInfo($userID)
{
    global $database;
    $user = $database->get("jk_users", "*", [
        "id" => $userID
    ]);
    return $user;
}

function userInfoColumn($userID, $column)
{
    global $database;
    $data = $database->get("jk_users", $column, [
        "id" => $userID
    ]);
    return $data;
}

function arrayIfEmptyZero($array = [])
{
    if (sizeof($array) == 0) {
        $array = 0;
    }
    return $array;
}

function datatable_structure($options = [])
{
    $opt = [
        "id" => "id",
        "type" => "html",
        "ajax_type" => "POST",
        "ajax_url" => "",
        "tabIndex" => "",
        "dom" => 'flrtip',
        "exportName" => 'export',
        "drawCallback" => "",
        "columnDefs" => [],
        "iDisplayLength" => "25",
        "lengthMenu" => '[[25, 50,100, -1], [25, 50,100, "All"]]',
        "order" => "[[ 0, 'desc' ]]",
        "columns" => [],
        "buttons" => [],
        "language" => '"decimal": "",
    "emptyTable":     "' . __("No data available in table") . '",
    "info":           "' . __("Showing _START_ to _END_ of _TOTAL_ entries") . '",
    "infoEmpty":      "' . __("Showing 0 to 0 of 0 entries") . '",
    "infoFiltered":   "' . __("&#40; filtered from _MAX_ total entries &#41;") . '",
    "infoPostFix":    "",
    "thousands":      ",",
    "lengthMenu":     "' . __("Show _MENU_ entries") . '",
    "loadingRecords":     "' . __("Loading") . '...",
    "processing":     "' . __("processing") . '...",
    "search":         "' . __("Search") . '",
    "zeroRecords":    "' . __("No matching records found") . '",
    "paginate": {
        "first":      "' . __("First") . '",
        "last":       "' . __("Last") . '",
        "next":       "' . __("Next") . '",
        "previous":   "' . __("Previous") . '"
    },
    "aria": {
        "sortAscending":  ": ' . __("activate to sort column ascending") . '",
        "sortDescending": ": ' . __("activate to sort column descending") . '"
    }',
    ];

    if (sizeof($options) >= 1) {
        foreach ($options as $tr => $val) {
            if (isset($opt[$tr])) {
                $opt[$tr] = $val;
            }
        }
    }

    $typetext = '';
    if ($opt['type'] === 'ajax') {
        $typetext = '"processing": true,
        "serverSide": true,
        "ajax": {
            "url": "' . $opt['ajax_url'] . '",
            "type": "' . $opt['ajax_type'] . '"
        },';
    }
    $lengthMenu = '';
    if ($opt['lengthMenu'] != '') {
        $lengthMenu = '"lengthMenu": ' . $opt['lengthMenu'] . ',';
    }
    $iDisplayLength = '';
    if ($opt['iDisplayLength'] != '') {
        $iDisplayLength = '"iDisplayLength": ' . $opt['iDisplayLength'] . ',';
    }
    $tabIndex = '';
    if ($opt['tabIndex'] != '') {
        $tabIndex = '"tabIndex": ' . $opt['tabIndex'] . ',';
    }
    $order = '';
    if ($opt['order'] != '') {
        $order = '"order": ' . $opt['order'] . ',';
    }

    $columns = '';
    if (is_array($opt['columns'])) {
        if (sizeof($opt['columns']) >= 1) {
            $txtcols = '[';

            foreach ($opt['columns'] as $key => $col) {
                $txtcols .= '{ "data": "' . $col . '" },';
            }
            $txtcols .= ']';
            $columns = '"columns": ' . $txtcols . ',';
        }
    }


    $columnDefs = '';
    if (is_array($opt['columnDefs'])) {
        if (sizeof($opt['columnDefs']) >= 1) {
            $txtcols = '[';

            foreach ($opt['columnDefs'] as $key => $col) {
                $txtcols .= '{ "width": "' . $col . '", "targets": ' . $key . ' },';
            }
            $txtcols .= ']';
            $columnDefs = '"columnDefs": ' . $txtcols . ',';
        }
    } else {
        $columnDefs = '"columnDefs": ' . $opt['columnDefs'] . ',';
    }


    $buttons = '';
    if (sizeof($opt['buttons']) >= 1) {
        $buttons = "buttons: [";
        foreach ($opt['buttons'] as $button) {
            if (in_array($button, ['csv', 'excel', 'pdf', 'pdfHtml5', 'print'])) {
                $tmp = '{
                        "extend": "' . $button . '",
                        "text": "' . __("Export as ") . ' ' . $button . '",
                        "filename": "' . $opt['exportName'] . '",
                        "className": "btn btn-success btn-sm",
                        "charset": "utf-8",
                        "bom": "true",
                        init: function(api, node, config) {
                            $(node).removeClass("btn-default");
                        }
                    }';
                $buttons .= $tmp . ",";
            } else {
                $buttons .= "'" . $button . "',";
            }
        }
        $buttons = rtrim($buttons, ',');
        $buttons .= "],";
        $opt['dom'] = "B" . $opt['dom'];
    }

    $return = '
    
    var table=$(\'#' . $opt['id'] . '\').DataTable( {
        ' . $columnDefs . '
        ' . $typetext . '
        ' . $lengthMenu . '
        ' . $iDisplayLength . '
        ' . $order . '
        ' . $columns . '
        ' . $buttons . '
        ' . $tabIndex . '
        "dom":"' . $opt['dom'] . '",
        language: {
    ' . $opt['language'] . '
},
"drawCallback": function (Settings) {
$(\'[data-popup="tooltip"]\').tooltip();
' . $opt['drawCallback'] . '
},
   } );
    
    
    
    ';

    global $View;
    $View->header_styles_files('/modules/cp/assets/datatable/datatables.min.css');
    $View->footer_js_files('/modules/cp/assets/datatable/datatables.min.js');
    return $return;
}

function datatable_view($options = [])
{
    global $database;
    $opt = [
        "CountAll" => 0,
        "lists" => [],
        "data" => [],
    ];

    if (sizeof($options) >= 1) {
        foreach ($options as $tr => $val) {
            if (isset($opt[$tr])) {
                $opt[$tr] = $val;
            }
        }
    }
    $return = '';


    $results = array(
        "draw" => $_POST["draw"],
        "iTotalRecords" => $opt['CountAll'],
        "iTotalDisplayRecords" => $opt['CountAll'],
        "aaData" => $opt['data']);
    return json_encode($results);
}

function datatable_get_opt()
{
    $return = false;
    if (isset($_POST['columns'])) {

        $start = $_POST['start'];
        $length = $_POST['length'];

        if (!isset($_POST['search']['value']) || $_POST['search']['value'] == "") {
            $search = '';
        } else {
            $search = $_POST['search']['value'];

        }

        $orderby = $_POST['columns'][$_POST['order'][0]['column']]['data'];
        $orderval = strtoupper($_POST['order'][0]['dir']);

        $return = [
            "search" => $search,
            "orderby" => $orderby,
            "orderval" => $orderval,
            "start" => $start,
            "length" => $length,
        ];
    }
    return $return;
}

function datatable_selectAll($name = "def")
{
    global $View;
    $View->footer_js('<script>
$("#datatable_checkbox_' . $name . '_selectAll").on("click",function() {
    if (this.checked) {
           $(\'input[id^=datatable_checkbox_' . $name . ']\').prop(\'checked\', true);
           }else{
            $(\'input[id^=datatable_checkbox_' . $name . ']\').prop(\'checked\', false);

           }
});
</script>');
    $html = '<input type="checkbox" class="checkbox" id="datatable_checkbox_' . $name . '_selectAll" name="datatable_checkbox_' . $name . '_selectAll"> ';

    return $html;
}

function datatable_selectCheck($id, $value, $name = "def")
{
    global $View;
    $View->footer_js('<script>
$("#datatable_checkbox_' . $name . '_' . $id . '").on("click",function() {
    if (this.checked) {
           }else{
           $(\'#datatable_checkbox_' . $name . '_selectAll\').prop(\'checked\', false);

           }
});
</script>');
    $html = '<input type="checkbox" class="checkbox" id="datatable_checkbox_' . $name . '_' . $id . '" value="' . $value . '" name="datatable_checkbox_' . $name . '_' . $id . '"> ';

    return $html;
}

function datatable_selectString($stringName, $formName = "def")
{
    $html = '
    var ' . $stringName . '="";
      $(\'input[id^=datatable_checkbox_' . $formName . ']\').each(function () {
           if (this.checked) {
               ' . $stringName . '+= $(this).val()+",";
           }
});
    ';

    return $html;
}

function alert($options = [])
{
    $opt = [
        "type" => 'info',
        "class" => 'alert text-center IRANSans alert-',
        "elem" => 'div',
        "text" => __("done"),
    ];

    if (sizeof($options) >= 1) {
        foreach ($options as $tr => $val) {
            if (isset($opt[$tr])) {
                $opt[$tr] = $val;
            }
        }
    }
    $butshow = '';
    $butshow_start = '';
    $butshow_end = '';
    if ($opt['elem'] != '') {
        $butshow_start .= '<' . $opt['elem'];
        if ($opt['class'] != '') {
            $butshow_start .= ' class="' . $opt['class'];
            if ($opt['type'] != '') {
                $butshow_start .= $opt['type'];
            }
            $butshow_start .= '" ';
        }
        $butshow_start .= '>';

        $butshow_end .= '</' . $opt['elem'] . '>';
    }

    $butshow = $butshow_start . $opt['text'] . $butshow_end;


    return $butshow;
}

function alertWarning($text = "")
{
    if ($text == "") {
        $text = __("warning");
    }
    return alert([
        "type" => "warning",
        "text" => $text,
    ]);
}

function alertSuccess($text = "")
{
    if ($text == "") {
        $text = __("success");
    }
    return alert([
        "type" => "success",
        "text" => $text,
    ]);
}

function alertDanger($text = "")
{
    if ($text == "") {
        $text = __("danger");
    }
    return alert([
        "type" => "danger",
        "text" => $text,
    ]);
}

function alertInfo($text = "")
{
    if ($text == "") {
        $text = __("info");
    }
    return alert([
        "type" => "info",
        "text" => $text,
    ]);
}

function fault()
{
    return alert([
        "type" => "danger",
        "text" => __("fault"),
        "elem" => "span"
    ]);
}

function modal_create($options = [])
{
    $option = [
        "id" => 'modal_global',
        "size" => '',
        "title" => '',
        "title-size" => 6,
        "text" => loading_fa(),
        "bg" => "",
        "return" => false,
    ];
    if (sizeof($option) >= 1) {
        foreach ($option as $key => $opt) {
            if (isset($options[$key])) {
                $option[$key] = $options[$key];
            }
        }
    }
    if ($option['size'] != '') {
        $option['size'] = 'modal-' . $option['size'];
    }
    if ($option['bg'] != '') {
        $option['bg'] = 'bg-' . $option['bg'];
    }
    $html = '<div id="' . $option['id'] . '" class="modal fade IRANSans">
        <div class="modal-dialog ' . $option['size'] . '">
            <div class="modal-content">
                <div class="modal-header ' . $option['bg'] . '">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>

                    <h' . $option['title-size'] . ' class="modal-title"
                                                           id="' . $option['id'] . '_title">' . $option['title'] . '</h' . $option['title-size'] . '>

                </div>
                <div class="modal-body" id="' . $option['id'] . '_body">
                    ' . $option['text'] . '
                </div>
            </div>
        </div>
    </div>';
    if ($option['return'] == false) {
        echo $html;
    } else {
        return $html;
    }
}

function NestableTableGetData($id, $table, $lang, $parent = 0, $extra_float = "", $module = 0, $extra_title = "", $buttons = true, $options = [])
{
    global $database;

    if ($lang == '') {
        $getCol = ['id', 'title'];
    } else {
        $getCol = ['id'];
    }
    if ($module == "") {
        $selects = $database->select($table, $getCol, [
            "AND" => [
                "parent" => $parent,
                "status" => 'active',
            ],
            "ORDER" => ["parent" => "ASC", "sort" => "ASC"]
        ]);

    } else {
        $selects = $database->select($table, $getCol, [
            "AND" => [
                "parent" => $parent,
                "module" => $module,
                "status" => 'active',
            ],
            "ORDER" => ["parent" => "ASC", "sort" => "ASC"]
        ]);
    }
    if (sizeof($selects) >= 1) {
        ?>
    <ol class="dd-list dd-list-<?php echo JK_DIRECTION; ?>" id="nestable_dd_list_<?php echo $id; ?>">
        <?php
        foreach ($selects as $select) {
            ?>
            <li class="dd-item dd3-item" data-id="<?php echo $select['id'] ?>">
                <div class="dd-handle dd3-handle <?php
                if (isset($options[$select['id']]['icon'])) {
                    echo 'dd3-handle-' . $options[$select['id']]['icon'];
                }

                ?>"></div>
                <div class="dd3-content"><?php
                    $title = "";
                    if (isset($options['showIds'])) {
                        $title .= $select['id'] . '- ';
                    }
                    if (isset($extra_title[$select['id']])) {
                        $title .= $extra_title[$select['id']] . ' ';
                    }
                    if (!isset($options['showTitle']) || $options['showTitle'] != false) {
                        if ($lang == "") {
                            $title .= $select['title'];
                        } else {
                            $title .= langDefineGet($lang, $table, 'id', $select['id']);
                        }
                        if ($title == "") {
                            $title .= '<span class="text-muted">' . __("not defined") . '</span>';
                        }
                    }
                    if (isset($options[$select['id']]['title'])) {
                        $title = $options[$select['id']]['title'];
                    }
                    echo $title;
                    ?>
                    <span class="float-<?php echo JK_DIRECTION_SIDE_R; ?>" style="margin-top: -7px;">
                        <?php
                        if (isset($extra_float[$select['id']])) {
                            echo $extra_float[$select['id']];
                        }
                        if ($buttons) {
                            ?>
                            <a class="btn btn-sm "
                               href="javascript:;"
                               onclick="nestableEdit_<?php echo $id; ?>(<?php echo $select['id']; ?>)">
                                    <i class="fa fa-edit text-info"></i>
                                </a>
                            <a class="btn btn-sm "
                               href="javascript:;"
                               onclick="nestableRemove_<?php echo $id; ?>(<?php echo $select['id']; ?>)">
                                                            <i class="fa fa-times text-danger"></i>
                                                        </a>
                            <?php
                        }
                        ?>
</span>
                </div>
                <?php

                NestableTableGetData($id, $table, $lang, $select['id'], $extra_float, $module, $extra_title, $buttons, $options);

                ?>
            </li>
            <?php

        }
        ?></ol><?php

    }
}

function NestableTableInitHtml($id)
{
    ?>
    <div class="dd" id="nestable_ajax_<?php echo $id; ?>"></div>
    <div id="nestable_sort_result_<?php echo $id; ?>"></div>
    <textarea title="nestable_list_ajax_output_<?php echo $id; ?>" id="nestable_list_ajax_output_<?php echo $id; ?>"
              class="d-none"></textarea>
    <?php
}

function NestableTableJS($id, $sortURL, $group = 1, $maxDepth = 3)
{
    global $View;
    $View->footer_js('
<script>
    $( document ).ready(function() {
        var UINestable = function () {
            var t = function (t) {
                var e = t.length ? t : $(t.target), a = e.data("output");
                window.JSON ? a.val(window.JSON.stringify(e.nestable("serialize"))) : a.val("JSON browser support required for this demo.")
            };
            return {
                init: function () {
                    $("#nestable_ajax_' . $id . '").nestable({group: ' . $group . ',maxDepth:' . $maxDepth . '}).on("change",function(e) {
                       t($("#nestable_ajax_' . $id . '").data("output", $("#nestable_list_ajax_output_' . $id . '")));
                       ' . ajax_load([
            "url" => $sortURL,
            "success_response" => "#nestable_sort_result_" . $id,
            "data" => "{sortval:$(\"#nestable_list_ajax_output_" . $id . "\").val()}",
            "loading" => [
            ]
        ]) . '
                    });
                    $("#list_ajax_menu").on("click", function (t) {
                        var e = $(t.target), a = e.data("action");
                    });
                }
            }
        }();

        UINestable.init();
    });
</script>
');
}

function NesatableUpdateSortData($table, $data)
{
    $jde = json_decode($data, true);
    NesatableUpdateSort($table, 0, $jde);
}

function NesatableUpdateSort($table, $sub, $jde)
{
    global $database;
    $js = 0;
    foreach ($jde as $j) {
        $database->update($table, [
            "parent" => $sub,
            "sort" => $js,
        ], [
            "id" => $j['id']
        ]);
        $js++;
        if (isset($j['children']) && is_array($j['children'])) {
            NesatableUpdateSort($table, $j['id'], $j['children']);
        }

    }
}

function langDefineGet($lang, $table, $column, $var)
{
    global $database;
    $back = '';
    $text = $database->get("jk_lang_defined", ["text"], [
        "AND" => [
            "tableName" => $table,
            "lang" => $lang,
            "varCol" => $column,
            "var" => $var,
        ]
    ]);
    if (isset($text['text'])) {
        $back = $text['text'];
    }
    return $back;
}

function langDefineSet($lang, $table, $column, $var, $text)
{
    global $database;
    $get = $database->get("jk_lang_defined", ["id", "text"], [
        "AND" => [
            "tableName" => $table,
            "lang" => $lang,
            "varCol" => $column,
            "var" => $var,
        ]
    ]);
    if (isset($get['text'])) {
        $database->update('jk_lang_defined', [
            "text" => $text
        ], [
            "id" => $get['id']
        ]);
    } else {
        $database->insert("jk_lang_defined", [
            "tableName" => $table,
            "lang" => $lang,
            "varCol" => $column,
            "var" => $var,
            "text" => $text
        ]);
    }
}

function langDefineSearch($lang, $table, $column, $search)
{
    global $database;
    $searchs = $database->select("jk_lang_defined", "var", [
        "AND" => [
            "tableName" => $table,
            "lang" => $lang,
            "varCol" => $column,
            "text[~]" => $search,
        ]
    ]);
    return $searchs;
}

function tab_menus($menus, $link, $pathCheck = 1)
{

    global $Route;
    global $ACL;
    if (isset($Route->path[$pathCheck])) {
        $active = $Route->path[$pathCheck];
    } else {
        $active = "";
    }

    if ($menus >= 1 && sizeof($menus) >= 1) {
        ?>
        <ul class="nav nav-tabs pr-0 pl-0">
            <?php
            foreach ($menus as $menu) {
                $linkCheck = $menu['link'];
                if (isset($menu['name'])) {
                    $linkCheck = $menu['name'];
                }
                $continue = true;
                if (isset($menu['permissions']) && !$ACL->hasPermission($menu['permissions'])) {
                    $continue = false;
                }
                if ($continue) {
                    ?>
                    <li class="nav-item <?php if ($active == $linkCheck) { ?>active<?php } ?>">
                        <a class="nav-link" href="<?php echo $link . $menu['link']; ?>">
                            <i class="<?php echo $menu['icon']; ?> position-left"></i>
                            <?php echo $menu['title']; ?>
                        </a>
                    </li>
                    <?php
                }
            }
            ?>
        </ul>
        <?php
    }

}

function div_start($class = '', $id = '', $close = false, $html = '', $attr = "")
{
    if ($class != '') {
        $class = ' class="' . $class . '"';
    }
    if ($id != '') {
        $id = ' id="' . $id . '"';
    }
    $append = $html;
    if ($close) {
        $append .= '</div>';
    }
    return '<div' . $class . $id . $attr . ' >' . $append;
}

function div_close($after = '')
{
    return '</div>' . $after;
}

function div_container_row($containerClass = '', $rowClass = '')
{
    return div_start('container-fluid ' . $containerClass) . div_start('row ' . $rowClass);
}

function div_container_row_close()
{
    return div_close() . div_close();
}

function clearfix()
{
    return "<div class='clearfix'></div>";
}

function hr_html()
{
    return "<hr/>";
}

function jk_options_get($name)
{
    global $database;
    return $database->get('jk_options', 'value', [
        "name" => $name
    ]);
}

function jk_options_set($name, $value)
{
    global $database;
    $option = $database->get('jk_options', '*', [
        "name" => $name
    ]);
    if (!isset($option['id'])) {
        $database->insert('jk_options', [
            "name" => $name,
            "value" => $value,
        ]);
        $optionID = $database->id();
        if ($optionID >= 1) {
            $option = $database->get('jk_options', '*', [
                "id" => $optionID
            ]);
        }
    } else {
        $database->update('jk_options', [
            "value" => $value
        ], [
            "name" => $name,
        ]);
        $option = $database->get('jk_options', '*', [
            "name" => $name
        ]);
    }
    return $option['value'];

}

function emailSend($fromEmail, $toMails, $subject, $text, $options = [])
{
    global $database;
    $email = $database->get('jk_emails', "*", [
        "email" => $fromEmail
    ]);
    if (!isset($email['id'])) {
        return 'Email ' . $fromEmail . ' Not Set';
    }
    $SMTPMailServer = $email['server'];
    $SMTPMailPort = $email['port'];
    $SMTPMailUserName = $email['username'];
    $SMTPMailPassword = $email['password'];
    $SMTPMailFromName = $email['fromName'];
    $SMTPMailSecure = $email['secureType'];
    $SMTPMailDebug = $email['debug'];

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);                              // Passing `true` enables exceptions
    try {
        //Server settings
        $mail->SMTPDebug = $SMTPMailDebug;                                 // Enable verbose debug output
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = $SMTPMailServer;  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = $SMTPMailUserName;                 // SMTP username
        $mail->Password = $SMTPMailPassword;                           // SMTP password
        $mail->SMTPSecure = $SMTPMailSecure;                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $SMTPMailPort;                                    // TCP port to connect to
        $mail->CharSet = 'UTF-8';
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );// Set mailer to use SMTP
        //Recipients
        $mail->setFrom($fromEmail, $SMTPMailFromName);
        if (is_array($toMails)) {
            foreach ($toMails as $em) {
                if (is_array($em) && isset($em['name'])) {
                    $name = $em['name'];
                    $email2 = $em['email'];
                } else {
                    $name = $em;
                    $email2 = $name;
                }
                $mail->addAddress($email2, $name);     // Add a recipient
            }
        } else {
            $mail->addAddress($toMails);     // Add a recipient

        }

        //Content
        $mail->isHTML(true);                                  // Set email format to HTML


        if (isset($options['BCC']) && is_array($options['BCC'])) {
            foreach ($options['BCC'] as $bcc) {
                if ($bcc != '') {
                    $mail->addBCC($bcc);
                }
            }
        }

        if (isset($options['embeded'])) {
            $mail->AddEmbeddedImage(JK_DIR_INCLUDES . 'templates' . DS . $options['embeded'], $options['embeded']);
        }


        if (file_exists(JK_DIR_INCLUDES . 'templates' . DS . 'emails' . DS . 'emailTpl-' . JK_DIRECTION . '.html')) {
            $htmlbody = file_get_contents(JK_DIR_INCLUDES . 'templates' . DS . 'emails' . DS . 'emailTpl-' . JK_DIRECTION . '.html');
            $htmlbody = str_replace('%{%text%}%', $text, $htmlbody);
            if (isset($options['autoSender']) && $options['autoSender'] != false) {
                $htmlbody = str_replace('%{%autosender%}%', $options['autoSender'], $htmlbody);
            } else {
                $htmlbody = str_replace('%{%autosender%}%', __("Please do not reply to this email; this address is not monitored. Please use our contact page."), $htmlbody);
            }
        } else {
            $htmlbody = $text;
        }


        if (isset($options['attachments']) && is_array($options['attachments'])) {
            $attachs = $options['attachments'];
            if (sizeof($attachs) >= 1) {
                foreach ($attachs as $attach) {
                    if (isset($attach['path']) && isset($attach['name']))
                        $mail->addAttachment($attach['path'], $attach['name']);
                }
            }

        }

        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body = $htmlbody;
        $mail->AltBody = $text;
        $mail->Timeout = 10;

        $mail->AltBody = $text;

        $mail->send();
        return "sent";
    } catch (Exception $e) {
        return 'Message could not be sent. Mailer Error: ' . $e->getMessage();
    }
}

function emailTemplate($templateName, $lang, $arguments = [])
{
    global $database;
    $text = "";
    $gettext = $database->get('jk_emailTemplate', "*", [
        "AND" => [
            "name" => $templateName,
            "lang" => $lang,
        ]
    ]);
    if (isset($gettext['id'])) {
        $text = $gettext['text'];
        if (sizeof($arguments) >= 1) {
            foreach ($arguments as $key => $arg) {
                $text = str_replace('[%' . $key . '%]', $arg, $text);
            }
        }
    }
    return $text;
}

function tokenGenerate($string = "")
{
    if ($string == "") {
        $string = time();
    }
    $string = md5(uniqid($string . time(), true));
    return $string;
}

function tokenGenerateUsers($userID, $source = "forgotLink", $validToSec = 3600)
{
    global $database;
    $has = true;
    while ($has) {
        $string = md5(uniqid($userID . time(), true));
        $has = $database->has("jk_users_token", [
            "token" => $string
        ]);
    }
    $database->insert('jk_users_token', [
        "userID" => $userID,
        "source" => $source,
        "token" => $string,
        "validUntil" => date("Y/m/d H:i:s", time() + $validToSec),
    ]);
    return $string;
}

function emailsArray()
{
    global $database;
    return $database->select('jk_emails', 'email', [
    ]);
}

function ArrayKeyEqualValue($array)
{
    $back = [];
    if (sizeof($array) >= 1) {
        foreach ($array as $arr) {
            $back[$arr] = $arr;
        }
    }
    return $back;
}

function statusReturnConfirmedUnconfirmed($status)
{
    switch ($status) {
        case "confirmed":
            $st = __("confirmed");;
            break;
        case "unconfirmed":
            $st = __("unconfirmed");
            break;
        default:
            $st = __("unknown");
    }
    return $st;
}

function statusReturnActiveInactive($status, $color = false)
{
    if ($color == false) {
        switch ($status) {
            case "active":
                $st = __("active");;
                break;
            case "inactive":
                $st = __("inactive");
                break;
            default:
                $st = $status;
        }
    } else {
        switch ($status) {
            case "active":
                $st = '<span class="text-success">' . __("active") . '</span>';
                break;
            case "inactive":
                $st = '<span class="text-danger">' . __("inactive") . '</span>';
                break;
            default:
                $st = $status;
        }
    }
    return $st;
}

function statusReturnYesNoInt($status, $color = false)
{
    if ($color == false) {
        switch ($status) {
            case "0":
                $st = __("no");;
                break;
            case "1":
                $st = __("yes");
                break;
            default:
                $st = $status;
        }
    } else {
        switch ($status) {
            case "0":
                $st = '<span class="text-danger">' . __("inactive") . '</span>';
                break;
            case "1":
                $st = '<span class="text-success">' . __("active") . '</span>';
                break;
            default:
                $st = $status;
        }
    }
    return $st;
}


function arrayToKey($array)
{
    $back = [];
    if (sizeof($array) >= 1) {
        foreach ($array as $arr) {
            $back[$arr] = $arr;
        }
    }
    return $back;
}

function arrayTitleIDToKey($array)
{
    $back = [];
    if (sizeof($array) >= 1) {
        foreach ($array as $arr) {
            $back[$arr['id']] = $arr['title'];
        }
    }
    return $back;
}

function arrayTitleIDToArray($array)
{
    $back = [];
    if (sizeof($array) >= 1) {
        foreach ($array as $arrK => $arrV) {
            array_push($back, [
                "id" => $arrK,
                "title" => $arrV,
            ]);
        }
    }
    return $back;
}

function arrayJustKey($array)
{
    $back = [];
    if (sizeof($array) >= 1) {
        foreach ($array as $key => $arr) {
            $back[$key] = $key;
        }
    }
    return $back;
}

function arrayJustVal($array)
{
    $back = [];
    if (sizeof($array) >= 1) {
        foreach ($array as $key => $arr) {
            array_push($arr);
        }
    }
    return $back;
}

function datetime_default()
{
    return date("Y/m/d H:i:s");
}

function ago_time($time_ago)
{
    $cur_time = time();
    $time_elapsed = $cur_time - $time_ago;
    $seconds = $time_elapsed;
    $minutes = round($time_elapsed / 60);
    $hours = round($time_elapsed / 3600);
    $days = round($time_elapsed / 86400);
    $weeks = round($time_elapsed / 604800);
    $months = round($time_elapsed / 2600640);
    $years = round($time_elapsed / 31207680);
// Seconds
    if ($seconds <= 60) {
        return $seconds . ' ' . __("second ago");
    } //Minutes
    else if ($minutes <= 60) {
        if ($minutes == 1) {
            return __("a minute ago");
        } else {
            return $minutes . ' ' . __("minutes ago");
        }
    } //Hours
    else if ($hours <= 24) {
        if ($hours == 1) {
            return __("an hour ago");
        } else {
            return $hours . ' ' . __("hours ago");
        }
    } //Days
    else if ($days <= 7) {
        if ($days == 1) {
            return __("yesterday");
        } else {
            return $days . ' ' . __("days ago");
        }
    } //Weeks
    else if ($weeks <= 4.3) {
        if ($weeks == 1) {
            return __("a week ago");
        } else {
            return $weeks . ' ' . __("weeks ago");
        }
    } //Months
    else if ($months <= 12) {
        if ($months == 1) {
            return __("a month ago");
        } else {
            return $months . ' ' . __("month ago");
        }
    } //Years
    else {
        if ($years == 1) {
            return __("a year ago");
        } else {
            return $years . ' ' . __("year ago");
        }
    }
}
function ago_time_minutes($time_ago)
{
    $cur_time = time();
    $time_elapsed = $cur_time - $time_ago;
    $seconds = $time_elapsed;
    $minutes = intval($time_elapsed / 60);
// Seconds
    return $minutes;
}

function passedSecond($second)
{
    $time_elapsed = $second;
    $seconds = $second;
    $minutes = round($time_elapsed / 60);
    $hours = round($time_elapsed / 3600);
// Seconds
    if ($seconds <= 60) {
        return $seconds . ' ' . __("second");
    } //Minutes
    else if ($minutes <= 60) {
        if ($minutes == 1) {
            return __("a minute");
        } else {
            return $minutes . ' ' . __("minutes");
        }
    } //Hours
    else if ($hours <= 24) {
        if ($hours == 1) {
            return __("an hour");
        } else {
            return $hours . ' ' . __("hours");
        }
    }
}

function timeCalcStart()
{
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;
    return $start;
}

function timeCalcEnd()
{
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    return $time;
}

function timeCalc($start, $finish)
{
    $total_time = round(($finish - $start), 4);
    return $total_time;
}

function verifyStatus($status, $color = false)
{
    $extraText = '';
    switch ($status) {
        case 'wfc':
            $backTXT = __("waiting for confirmation");
            $backColor = "warning";
            break;
        case 'verified':
            $backTXT = __("verified");
            $backColor = "success";
            break;
        case 'blocked':
            $backTXT = __("blocked");
            $backColor = "danger";
            break;
        case 'unVerified':
            $backTXT = __("unverified");
            $backColor = "info";
            break;
        case 'defect':
            $backTXT = __("defect");
            $backColor = "primary";
            break;
        default:
            $backTXT = $status;
            $backColor = "info";
    }
    if (!$color) {
        return $backTXT;
    } else {
        return '<span class="badge badge-' . $backColor . '">' . $backTXT . '</span>';
    }
}


function tr_num_js($idKeyUp)
{
    global $View;
    $View->footer_js('<script>
$("#' . $idKeyUp . '").on("keyup",function() {
    var thisVal=$(this).val();
    var persianNumbers = [/۰/g, /۱/g, /۲/g, /۳/g, /۴/g, /۵/g, /۶/g, /۷/g, /۸/g, /۹/g],
        arabicNumbers  = [/٠/g, /١/g, /٢/g, /٣/g, /٤/g, /٥/g, /٦/g, /٧/g, /٨/g, /٩/g];

  if(typeof thisVal === \'string\')
  {
    for(var i=0; i<10; i++)
    {
      thisVal = thisVal.replace(persianNumbers[i], i).replace(arabicNumbers[i], i);
    }
  }
  $(this).val(thisVal.toString());
})
</script>');
}
