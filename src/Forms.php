<?php

namespace Joonika\Forms;

use function Joonika\Upload\field_upload;

if (!defined('jk')) die('Access Not Allowed !');

function form_create($options = [])
{
    $option = [
        "id" => 'form_' . time(),
        "class" => 'form-horizontal',
        "action" => '',
        "method" => "post",
        "return" => "echo",
        "autocomplete" => false,
        "attr" => [
        ],
    ];
    if (sizeof($option) >= 1) {
        foreach ($option as $key => $opt) {
            if (isset($options[$key])) {
                $option[$key] = $options[$key];
            }
        }
    }
    $auto = '';
    if ($option['autocomplete'] == true) {
        $auto = 'on';
    } else {
        $auto = 'off';
    }
    $atts = '';
    if (sizeof($option['attr']) >= 1) {
        foreach ($option['attr'] as $key => $v) {
            $atts .= ' ' . $key . '="' . $v . '" ';
        }
    }
    $return = '<form action="' . $option['action'] . '" autocomplete="' . $auto . '" method="' . $option['method'] . '" id="' . $option['id'] . '" class="' . $option['class'] . '" ' . $atts . '>';
    if ($option['return'] == 'echo') {
        echo $return;
    } else {
        return $return;
    }
}

function form_end($options = [])
{
    $option = [
        "return" => "echo"
    ];
    if (sizeof($option) >= 1) {
        foreach ($option as $key => $opt) {
            if (isset($options[$key])) {
                $option[$key] = $options[$key];
            }
        }
    }
    $return = '</form>';
    if ($option['return'] == 'echo') {
        echo $return;
    } else {
        return $return;
    }
}

function field_text($options = [])
{
    $return = '';

    $option = [
        "title" => '',
        "value" => '',
        "type" => 'text',
        "class" => '',
        "disabled" => false,
        "form-group-class" => '',
        "form-group" => true,
        "name" => '',
        "id" => '',
        "attr" => [
            "data-msg" => __("this field is required")
        ],
        "addon" => '',
        "addon-dir" => 'right',
        "ColType" => '4,8',
        "help" => '',
        "placeholder" => '',
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
    $disabled = '';
    if ($option['disabled']) {
        $disabled = "disabled";
    }
    if (!isset($options['id'])) {
        $option['id'] = $option['name'];
    }
    if (!isset($options['value'])) {
        global $data;
        if (isset($data[$option['name']])) {
            $option['value'] = $data[$option['name']];
        }
    }
    $atts = '';
    if (sizeof($option['attr']) >= 1) {
        foreach ($option['attr'] as $key => $v) {
            $atts .= ' ' . $key . '="' . $v . '" ';
        }
    }

    $value = rawurldecode($option['value']);

    $optioncols = explode(',', $option['ColType']);

    $return .= '
        <label for="' . $option['id'] . '" class="inp ' . $option['direction'] . ' ' . $optioncols[0] . '">
                ';
    $requ = '';
    if ($option['required'] == true) {
        $requ = 'required="required"';
    }

    $return .= '<input type="' . $option['type'] . '" placeholder="' . $option['placeholder'] . '" class=" ' . $option['direction'] . ' ' . $option['class'] . '" ' . $disabled . ' name="' . $option['name'] . '" placeholder="' . $option['placeholder'] . '"  id="' . $option['id'] . '" value="' . $value . '"  ' . $requ . ' ' . $atts . ' >';


    $return .= '
        <span class="label">' . $option['title'] . '</span>
  <span class="border"></span>
                ';

    if ($option['help'] != '') {
        $return .= '
        <span class="help-block ' . $option['direction'] . '">' . $option['help'] . '</span>
        ';
    }
    $return .= '</label>';

    return $return;
}

function field_editor_html($options = [])
{
    global $data;
    global $View;
    $return = '';

    $option = [
        "value" => '',
        "type" => 'editor',
        "name" => '',
        "id" => '',
        "direction" => JK_DIRECTION
    ];
    if (sizeof($option) >= 1) {
        foreach ($option as $key => $opt) {
            if (isset($options[$key])) {
                $option[$key] = $options[$key];
            }
        }
    }
    if (!isset($options['id'])) {
        $option['id'] = $option['name'];
    }
    if (!isset($options['value'])) {
        if (isset($data[$option['name']])) {
            $option['value'] = $data[$option['name']];
        }
    }
    $text_type = $option['type'];
    if (isset($data['id'])) {
        $data['text_html'] = $data['text'];
        $data['text_editor'] = $data['text'];
        $text_type = $data['htmlMode'];
    }
    ?>
    <div class="">


        <div class="btn-group">
            <label for="htmlmode_editor" class="nav-link" onclick="showtab('tab_editor')">
                <input name="htmlmode" id="htmlmode_editor" value="editor"
                       <?php if ('editor' == $text_type) { ?>checked<?php } ?> type="radio"/><?php __e("editor") ?>
            </label>
            <label for="htmlmode_html" class="nav-link" onclick="showtab('tab_html')">
                <input name="htmlmode" id="htmlmode_html" value="html"
                       <?php if ('html' == $text_type) { ?>checked<?php } ?> type="radio"/><?php __e("html") ?>
            </label>
        </div>
        <br/>
        <button type="button" onclick="add_media()" class="btn btn-info"><i
                    class="fa fa-file"></i> <?php __e("add media") ?></button>

        <div id="add_media" class="modal fade IRANSans">
            <div class="modal-dialog ">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h6 class="modal-title" id="add_media_title"><?php __e("add media") ?></h6>
                    </div>
                    <div class="modal-body" id="add_media_body">
                        <div class="text-center"><i class="fa fa-spinner fa-pulse fa-fw fa-3x"></i><span
                                    class="sr-only"><?php __e("add media"); ?></span></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $View->footer_js('<script>
function add_media() {
  $("#add_media").modal("show");
   ' . ajax_load([
                "url" => JK_DOMAIN_LANG . 'cp/main/upload/addMedia',
                "success_response" => "#add_media_body",
                "loading" => [
                ]
            ]) . '
}
</script>');
        ?>

        <div class="tab-editor <?php if ($text_type != 'editor') { ?>d-none<?php } ?>" id="tab_editor">
            <?php
            echo field_editor([
                "title" => __("editor"),
                "name" => "text_editor",
                "ColType" => '12,12',
                "rows" => "18",
            ]);
            ?>

        </div>
        <div class="tab-editor <?php if ($text_type != 'html') { ?>d-none<?php } ?>" id="tab_html">
            <?php
            echo field_textarea([
                "name" => 'text_html',
                "title" => __("html"),
                "ColType" => '12,12',
                "direction" => "ltr",
                "rows" => 3
            ]);
            ?>
        </div>

    </div>
    <?php

    $View->footer_js('<script>
function showtab(id="") {
    $(".tab-editor").addClass("d-none").removeClass("d-block");
    $("#"+ id).addClass("d-block").removeClass("d-none");
}


</script>');
}


function field_editor($options = [])
{
    $return = '';

    $option = [
        "title" => '',
        "value" => '',
        "type" => 'full',
        "form-group" => true,
        "form-group-class" => '',
        "name" => '',
        "attr" => [
            "data-msg" => __("this field is required")
        ],
        "ColType" => '4,8',
        "help" => '',
        "required" => false,
        "direction" => JK_DIRECTION,
        "rows" => 3,
        "lang" => JK_LANG
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
        global $data;
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

    if ($option['form-group'] == true) {
        $return .= '
        <div class="form-group row ' . $option['form-group-class'] . '">
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
                <textarea class="form-control ' . $option['direction'] . '" name="' . $option['name'] . '" rows="' . $option['rows'] . '" id="' . $option['name'] . '" ' . $requ . ' ' . $atts . ' >' . $value . '</textarea>';

    if ($option['form-group'] == true) {
        if ($option['help'] != '') {
            $return .= '
        <span class="help-block ' . $option['direction'] . '">' . $option['help'] . '</span>
        ';
        }
        $return .= '</div>
    </div>';
    }
    global $View;
    $View->footer_js_files("/modules/cp/assets/js/ckeditor/ckeditor.js");
    if ($option['type'] == "simple") {
        $View->footer_js('
<script type="text/javascript">
                CKEDITOR.replace( "' . $option['name'] . '",{
                                    language: "' . $option['lang'] . '",
                uiColor: \'#f0f0f0\',
                skin : \'moono-lisa\',
                toolbarGroups : [
                    { name: \'basicstyles\', groups: [ \'basicstyles\', \'cleanup\' ] },
                    { name: \'links\' },   
                    { name: \'colors\' }
                ]
            });
</script>
    ');


    } else {
        $View->footer_js('
<script type="text/javascript">
                CKEDITOR.replace( "' . $option['name'] . '",{
                                    language: "' . $option['lang'] . '"
                } );
</script>
    ');
    }

    return $return;
}


function slugControl($name, $slug)
{
    global $View;
    global $data;
    $value = '';
    if (isset($data[$slug]) && $data[$slug] != '') {
        $value = '';
    }
    echo field_hidden([
        "name" => "old_s_" . $name,
        "value" => $value,
    ]);
    $View->footer_js('
        <script>
        $("#' . $name . '").on("change keyup",function() {
          var oldsl=$("#old_s_' . $name . '").val();
          if(oldsl==""){
              var text= $(this).val().replace(/ /g, "-");
              $("#' . $slug . '").val(text);
          }
        });
        </script>
        ');
}

function field_info($options = [])
{
    $return = '';

    $option = [
        "title" => '',
        "value" => '',
        "type" => 'text',
        "class" => 'form-control',
        "form-group-class" => '',
        "form-group" => true,
        "name" => '',
        "addon" => '',
        "addon-dir" => 'right',
        "ColType" => '4,8',
        "help" => '',
        "array" => '',
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
        global $data;
        if (isset($data[$option['name']])) {
            if (isset($options['array'][$data[$option['name']]])) {
                $option['value'] = $options['array'][$data[$option['name']]];
            } else {
                $option['value'] = $data[$option['name']];
            }
        }
    }
    $atts = '';
    if (sizeof($option['attr']) >= 1) {
        foreach ($option['attr'] as $key => $v) {
            $atts .= ' ' . $key . '="' . $v . '" ';
        }
    }

    $value = '<label class="col-md-' . rawurldecode($option['value']) . ' control-label">
            ' . $option['value'] . '
        </label>';

    $optioncols = explode(',', $option['ColType']);

    if ($option['form-group'] == true) {
        $return .= '
        <div class="form-group row ' . $option['form-group-class'] . '">
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
    $beforeret = '';
    $afterret = '';
    if ($option['addon'] != '') {

        if ($option['addon-dir'] == 'right') {

            $beforeret .= '<div class="input-group">
											';
            $afterret .= '<span class="input-group-addon ' . $option['direction'] . '">' . $option['addon'] . '</span>
										</div>';
        } else {
            $beforeret .= '<div class="input-group">
<span class="input-group-addon">' . $option['addon'] . '</span>
											';
            $afterret .= '
										</div>';
        }

    }
    $return .= $beforeret . '
                ' . $value . '
    ' . $afterret;

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

function field_textarea($options = [])
{
    $return = '';

    $option = [
        "title" => '',
        "value" => '',
        "form-group" => true,
        "form-group-class" => '',
        "name" => '',
        "id" => '',
        "attr" => [
            "data-msg" => __("this field is required")
        ],
        "ColType" => '12,12',
        "help" => '',
        "required" => false,
        "direction" => JK_DIRECTION,
        "rows" => 3
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
    if (!isset($options['id'])) {
        $option['id'] = $option['name'];
    }
    if (!isset($options['value'])) {
        global $data;
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

    if ($option['form-group'] == true) {
        $return .= '
        <div class="form-group row ' . $option['form-group-class'] . '">
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
    $return .= '<textarea class="form-control ' . $option['direction'] . '" name="' . $option['name'] . '" rows="' . $option['rows'] . '" id="' . $option['id'] . '" ' . $requ . ' ' . $atts . ' >' . $value . '</textarea>';

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

function field_select($options = [])
{
    global $View;
    $return = '';
    $option = [
        "title" => '',
        "function" => '',
        "select2Attr" => '',
        "array" => [],
        "arrayAttr" => [],
        "form-group" => true,
        "form-group-class" => '',
        "name" => '',
        "id" => '',
        "attr" => [
            "data-msg" => __("this field is required"),
            "placeholder" => ""
        ],
        "ColType" => '4,8',
        "help" => '',
        "value" => '',
        "required" => false,
        "multiple" => false,
        "first" => false,
        "firstTitle" => __("please select"),
        "firstValue" => "",
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
    if (!isset($options['id'])) {
        $option['id'] = $option['name'];
    }
    if (!isset($option['value']) || $option['value'] == '') {
        global $data;
        if (isset($data[$option['id']])) {
            $option['value'] = $data[$option['id']];
        }
    }
    $atts = '';
    if (sizeof($option['attr']) >= 1) {
        foreach ($option['attr'] as $key => $v) {
            $atts .= ' ' . $key . '="' . $v . '" ';
        }
    }

    if (!is_array($option['value'])) {
        $valuereq = rawurldecode($option['value']);
    } else {
        $valuereq = $option['value'];
    }
    $optioncols = explode(',', $option['ColType']);


    if ($option['form-group'] == true) {
        $return .= '
        <div class="form-group inpSelect ' . $option['direction'] . ' ' . $option['form-group-class'] . '">
        ';
        if ($option['title'] != "") {
            $return .= '
        <label class="control-label" for="' . $option['id'] . '">

                <div class="">
        ';
        }
    }

    $requ = '';
    if ($option['required'] == true) {
        $requ = 'required="required"';
    }
    $multiple = '';
    if ($option['multiple'] == true) {
        $multiple = 'multiple="multiple"';
    }
    $return .= '<select class="form-control IRANSans-force w-100 ' . $option['direction'] . '" name="' . $option['name'] . '"  id="' . $option['id'] . '" ' . $requ . ' ' . $atts . ' ' . $multiple . ' >';
    if ($option['first'] == true) {
        $return .= '<option value="' . $option['firstValue'] . '">' . $option['firstTitle'] . '</option>';
    }
    foreach ($option['array'] as $key => $value) {
        $selected = '';
        $showop = 1;
        if (substr($key, 0, 7) === "optgrp_") {
            $return .= '<optgroup label="' . $value . '">';
            $showop = 0;
        }
        if (substr($key, 0, 10) === "endoptgrp_") {
            $return .= '</optgroup>';
            $showop = 0;
        }
        if ($showop == 1) {
            if (!is_array($valuereq)) {
                if ($valuereq == $key && $valuereq != '') {
                    $selected = 'selected="selected"';
                }
            } elseif (sizeof($valuereq) >= 1) {
                if (in_array($key, $valuereq)) {
                    $selected = 'selected="selected"';
                }
            }
            $attr = '';
            if (isset($option['arrayAttr'][$key])) {
                $attr = $option['arrayAttr'][$key];
            }
            $return .= '<option ' . $selected . ' ' . $attr . ' value="' . $key . '">' . $value . '</option>';
        }
    }

    $return .= '</select>';

    if ($option['form-group'] == true) {
        if ($option['help'] != '') {
            $return .= '
        <span class="help-block ' . $option['direction'] . '">' . $option['help'] . '</span>
        ';
        }
        if ($option['title'] != "") {
            $return .= '            <div class="label">' . $option['title'] . '</div>
              <span class="border"></span>
              </div>';
        }
        $return .= '
    </div>        </label>
';
    }
    $extText = '';
    if (isset($option['attr']['readonly'])) {
        $extText = ',disabled:true';
    }
    $View->footer_js('
    <script>
    ' . $option['function'] . '
    $("#' . $option['id'] . '").select2({
                    dropdownParent: $(\'#' . $option['id'] . '\').parents(\'.form-group\')
                    ' . $extText . '
                    ' . $option['select2Attr'] . '
    });
    </script>
    ');
    return $return;
}

function field_hidden($options = [])
{
    $return = '';
    $option = [
        "value" => '',
        "name" => '',
        "required" => false,
        "attr" => [
            "data-msg" => __("this field is required")
        ],
    ];
    if (sizeof($option) >= 1) {
        foreach ($option as $key => $opt) {
            if (isset($options[$key])) {
                $option[$key] = $options[$key];
            }
        }
    }
    $atts = '';
    if (sizeof($option['attr']) >= 1) {
        foreach ($option['attr'] as $key => $v) {
            $atts = ' ' . $key . '="' . $v . '" ';
        }
    }

    $requ = '';
    if ($option['required'] == true) {
        $requ = 'required="required"';
    }
    if (!isset($options['value'])) {

        global $data;
        if (isset($data[$option['name']])) {
            $option['value'] = $data[$option['name']];
        }
    }

    $value = rawurldecode($option['value']);

    $return .= '<input type="hidden" name="' . $option['name'] . '" id="' . $option['name'] . '" value="' . $value . '" ' . $requ . ' ' . $atts . '>';
    return $return;
}

function field_submit($options = [])
{
    $return = '';
    $option = [
        "text" => '',
        "name" => 'submit',
        "id" => 'submit',
        "value" => 'submit',
        "btn-class" => '',
        "ColType" => '12,12',
        "title" => '',
        "disabled" => '',
        "cancel-text" => '',
        "cancel-class" => '',
        "cancel-url" => '',
        "cancel-function" => '',
        "icon" => '',
    ];
    if (sizeof($option) >= 1) {
        foreach ($option as $key => $opt) {
            if (isset($options[$key])) {
                $option[$key] = $options[$key];
            }
        }
    }

    $icon = '';
    if (isset($option['icon']) && $option['icon'] != '') {
        $icon = "<b><i class='" . $option['icon'] . "'></i></b> ";
    }
    if (!isset($options['text'])) {
        $option['text'] = __("submit");
    }
    if (!isset($options['btn-class'])) {
        $option['btn-class'] = "btn btn-success btn-labeled";
    }
    if (!isset($options['value'])) {
        $option['value'] = $option['name'];
    }
    if (!isset($options['cancel-class'])) {
        $option['cancel-class'] = "btn btn-default btn-labeled";
    }
    if ($option['cancel-url'] != '') {
        $option['cancel-url'] = "onclick=\"window.location='" . $option['cancel-url'] . "'\"";
    }
    $optioncols = explode(',', $option['ColType']);

    $return .= '<button type="submit" ' . $option['disabled'] . ' value="' . $option['value'] . '" id="' . $option['id'] . '" name="' . $option['name'] . '" class="' . $option['btn-class'] . '">' . $icon . $option['text'] . '</button>';

    if ($option['cancel-text'] != '') {
        $return .= '<button type="reset" ' . $option['cancel-url'] . ' class="' . $option['cancel-class'] . '">' . $icon . $option['cancel-text'] . '</button>';
    }

    return $return;
}

function field_tags($options)
{
    global $View;
    $return_text = field_text($options);
    if (!isset($options['id'])) {
        $options['id'] = $options['name'];
    }
    $return = '<script>

 $(\'#' . $options['id'] . '\').on(\'tokenfield:initialize\', function (e) {
        $(this).parent().find(\'.token\').addClass(\'bg-primary\')
    });

    // Initialize plugin
    $(\'#' . $options['id'] . '\').tokenfield();

    // Add class when token is created
    $(\'#' . $options['id'] . '\').on(\'tokenfield:createdtoken\', function (e) {
        $(e.relatedTarget).addClass(\'bg-primary\')
    });


</script>';

    $View->footer_js($return);
    $View->footer_js_files('/modules/cp/assets/js/tags/tagsinput.min.js');
    $View->footer_js_files('/modules/cp/assets/js/tags/tokenfield.min.js');
    $View->footer_js_files('/modules/cp/assets/js/typeahead/typeahead.bundle.min.js');
    return $return_text;
}

function field_check($options = [])
{
    $return = '';

    $option = [
        "checkType" => 'filled-in', // indeterminate-checkbox , filled-in
        "title" => '',
        "value" => '',
        "id" => '',
        "form-group" => true,
        "form-group-class" => '',
        "name" => '',
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
    if (!isset($options['id'])) {
        $option['id'] = $option['name'];
    }
    if (!isset($options['value'])) {
        global $data;
        if (isset($data[$option['name']])) {
            $option['value'] = $data[$option['name']];
        }
    }
    $atts = '';
    if (sizeof($option['attr']) >= 1) {
        foreach ($option['attr'] as $key => $v) {
            $atts .= ' ' . $key . '="' . $v . '" ';
        }
    }

    $value = rawurldecode($option['value']);
    if ($value == 1) {
        $value = 'checked="checked"';
    }
    $optioncols = explode(',', $option['ColType']);

    if ($option['form-group'] == true) {
        $return .= '
        <div class="form-group row ' . $option['form-group-class'] . '">
        ';
    }
    $requ = '';
    if ($option['required'] == true) {
        $requ = 'required="required"';
    }
    $beforeret = '';
    $afterret = '';

    $return .= $beforeret . '
										<label class="m-0">
											<input type="checkbox" name="' . $option['name'] . '" id="' . $option['id'] . '" ' . $requ . ' class="' . $option['checkType'] . '" ' . $value . ' ' . $atts . ' >
											<span>' . $option['title'] . '</span>
										</label>
    ' . $afterret;

    if ($option['form-group'] == true) {
        if ($option['help'] != '') {
            $return .= '
        <span class="help-block ' . $option['direction'] . '">' . $option['help'] . '</span>
        ';
        }
        $return .= '
    </div>';
    }
    return $return;
}

function field_labelRadio($options = [])
{
    $return = '';

    $option = [
        "checkType" => 'filled-in', // indeterminate-checkbox , filled-in
        "title" => '',
        "class-body" => '',
        "card-style" => '',
        "label-class" => "",
        "label-style" => "",
        "value" => '',
        "flex-row" => true,
        "extraVal" => '',
        "icon" => '',
        "id" => '',
        "name" => '',
        "attr" => [
            "data-msg" => __("this field is required")
        ],
        "ColType" => '4,8',
        "help" => '',
        "required" => false,
        "direction" => JK_DIRECTION,
        "disabled" => false
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
    if (!isset($options['id'])) {
        $option['id'] = $option['name'];
    }
    $checkVal = '';
    if ($option['value'] != "") {
        global $data;
        if (isset($data[$option['name']])) {
            $checkVal = $data[$option['name']];
        }
    }
    $atts = '';
    if (sizeof($option['attr']) >= 1) {
        foreach ($option['attr'] as $key => $v) {
            $atts .= ' ' . $key . '="' . $v . '" ';
        }
    }
    $checked = '';
    if ($checkVal == $option['value']) {
        $checked = 'checked="checked"';
    }

    $requ = '';
    if ($option['required'] == true) {
        $requ = 'required="required"';
    }
    $beforeret = '';
    $afterret = '';
    $icon = '';
    if ($option['icon'] != "") {
        $icon = '<div class="mx-2"><i class="' . $option['icon'] . '"></i></div> ';
    }
    $extraVal = '';
    if ($option['extraVal'] != "") {
        $extraVal = '<div class="">' . $option['extraVal'] . '</div>';
    }
    if ($option['flex-row'] == true) {
        $option['flex-row'] = 'flex-row';
    } else {
        $option['flex-row'] = '';

    }
    $disabled = '';
    if ($option['disabled']) {
        $disabled = 'disabled';
    }

    $return .= $beforeret . '
										<label class="' . $option['label-class'] . '" style="' . $option['label-style'] . '" id="label_'.$option['id'].'">
                        <input type="radio" name="' . $option['name'] . '" class="card-input-element d-none"  id="' . $option['id'] . '" value="' . $option['value'] . '" ' . $requ . ' ' . $checked . ' ' . $atts . ' ' . $disabled . '>
                        <div  class="card card-body ' . $disabled . ' d-flex ' . $option['flex-row'] . ' justify-content-center align-items-center rounded ' . $option['class-body'] . '" style="' . $option['card-style'] . '">
                            ' . $icon . '
                            ' . $option['title'] . '
                            ' . $extraVal . '
                        </div>
                    </label>
    ' . $afterret;

    return $return;
}
