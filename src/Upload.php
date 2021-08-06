<?php

namespace Joonika;

use Joonika\Controller\AstCtrl;

class Upload
{
    private $file = null;
    private $directory = null;

    public function __construct($file = null)
    {
        if ($file) {
            $this->file = $file;
        }
    }

//    public function getFile

    public static function dropzone_load()
    {
        modules_assets_to_ctrl('cp/assets/dropzone/dropzone.css');
        modules_assets_to_ctrl('cp/assets/dropzone/dropzone.js');
    }

    public static function getThumbnails($websiteID = "")
    {
        $back = [];
        if ($websiteID == "" && defined('JK_WEBSITE_ID()')) {
            $websiteID = JK_WEBSITE_ID();
        }
        if ($websiteID != "") {
            $ths = Database::connect()->select('jk_thumbnails', '*', [
                "websiteID" => $websiteID
            ]);
            if (sizeof($ths) >= 1) {
                foreach ($ths as $th) {
                    array_push($back, [
                        "id" => $th['id'],
                        "name" => $th['name'],
                        "w" => $th['width'],
                        "h" => $th['height'],
                    ]);
                }
            }
        }
        return $back;
    }

    public static function field_upload($options = [])
    {

        $return = '';
        $option = [
            "title" => '',
            "value" => '',
            "type" => 'text',
            "maxfiles" => 4,
            "form-group" => true,
            "theme" => false,
            "afterSuccess" => '',
            "module" => 'blog_post',
            "name" => '',
            "thMaker" => 1,
            "id" => '',
            "attr" => [
                "data-msg" => __("this field is required")
            ],
            "ColType" => '4,8',
            "help" => '',
            "required" => false,
            "direction" => JK_DIRECTION(),
            "text" => __('Drop files to upload'),
            "max" => 5
        ];
        if (sizeof($option) >= 1) {
            foreach ($option as $key => $opt) {
                if (isset($options[$key])) {
                    $option[$key] = $options[$key];
                }
            }
        }

        if (!isset($option['title'])) {
            $option['title'] = $option['name'];
        }
        if ($option['id'] == "") {
            $option['id'] = $option['name'];
        }
        if ($option['value'] == "") {
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
                <input type="hidden"  class="form-control ' . $option['direction'] . '" name="' . $option['name'] . '"  id="' . $option['name'] . '" value="' . $value . '"  ' . $requ . ' ' . $atts . ' >
                
                <div id="myId_upload_' . $option['name'] . '" class="dropzone">
        <div class="fallback">
            <input class="" name="file" type="file" multiple  />
        </div>
    </div>
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
        $befores = [];
        if ($value != "") {
            $befores = explode(',', $value);
        }
        AstCtrl::ADD_FOOTER_SCRIPTS('<script>
' . self::dropzone_script([
                "maxfiles" => $option['maxfiles'],
                "module" => $option['module'],
                "dest_id" => $option['id'],
                "name" => $option['name'],
                "thMaker" => $option['thMaker'],
                "befores" => $befores,
                "afterSuccess" => $option['afterSuccess'],
                "text" => $option['text'],
                "max" => $option['max'],
            ]) . '
</script>');


        return $return;
    }

    public static function dropzone_script($options = [])
    {
        $option = [
            "module" => "",
            "name" => "",
            "befores" => [],
            "date" => 1,
            "input" => "file",
            "return" => "id",
            "thMaker" => 1,
            "maxfiles" => 1,
            "afterSuccess" => "",
            "max" => 5,
            "dest_id" => "upload_address",
            "text" => __('Drop files to upload') . '<span>' . __('or CLICK') . '</span>',
        ];
        if (sizeof($option) >= 1) {
            foreach ($option as $key => $opt) {
                if (isset($options[$key])) {
                    $option[$key] = $options[$key];
                }
            }
        }

        $mock = '';
        if (is_array($option['befores']) && sizeof($option['befores']) >= 1) {
            $getfilemock = '';
            foreach ($option['befores'] as $before) {
                $getmock = self::getfile($before, false, 'original', '*');
                if (isset($getmock['id'])) {
                    $sizefile = 0;
                    if (file_exists(JK_SITE_PATH() . $getmock['file'])){
                        $sizefile = @filesize(JK_SITE_PATH() . $getmock['file']);
                    }
                    $mockdel = '';
                    if (in_array($getmock['mime'], ['image/jpeg', 'image/gif', 'image/png', 'image/vnd.microsoft.icon'])) {
                        $mockdel = 'this.emit("thumbnail", mockFile, "' . self::getLink($getmock['id']) . '");';
                    }
                    $mock .= '
var mockFile = { name: "' . $getmock['name'] . '", size: ' . $sizefile . ',fileid:' . $getmock['id'] . ' , isMock : true};
this.emit("addedfile", mockFile);
this.emit("complete", mockFile);
this.files.push(mockFile);
this.options.maxFiles = this.options.maxFiles - 1;
' . $mockdel . '
              ';
                }
            }
        }
        $return = '
    Dropzone.autoDiscover = false;

    $("#myId_upload_' . $option['name'] . '").dropzone({ url: "' . JK_DOMAIN_LANG() . 'cp/main/upload/new" ,
                paramName: "' . $option['input'] . '", // The name that will be used to transfer the file.
                params: {
                    module: "' . $option['module'] . '",
                    thMaker: "' . $option['thMaker'] . '",
                    return: "' . $option['return'] . '",
                    date: "' . $option['date'] . '",
                },
                addRemoveLinks:true,
                dictDefaultMessage: "' . $option['text'] . '",
                dictRemoveFile: "' . __("remove file") . '",
                dictMaxFilesExceeded: "' . __("You can not upload any more files.") . '",
                uploadMultiple:false,
                maxFiles:' . $option['maxfiles'] . ',
                maxFilesize: ' . $option['max'] . ', // MB
                init: function() {
                ' . $mock . '

                    this.on("success", function (file, response) {
                        obj = JSON.parse(response);
                        var beforeval=$(\'#' . $option['dest_id'] . '\').val();
                        if(beforeval!=""){
                            beforeval=beforeval+",";
                        }
                        beforeval=beforeval+obj.filename;
                        $(\'#' . $option['dest_id'] . '\').val(beforeval);
                        ' . $option['afterSuccess'] . '
                    }); 
                    this.on("removedfile", function (file) {
                    var removeid=0;
                    if(file.isMock){
                    this.options.maxFiles = this.options.maxFiles + 1;
                                        removeid=file.fileid;

                    }else{
                    if(file.xhr){
                    var thisidget=file.xhr.response;
                    var nobj = jQuery.parseJSON( thisidget );
                                        removeid=nobj.fileid;
                    }
                    }
                    var newstring="";
                    

var array = $("#' . $option['dest_id'] . '").val().split(",");    
   $.each(array,function(i){
   if(this!=removeid){
   newstring+=this+",";
   }
   });
   newstring=newstring.replace(/,+$/,\'\');  
   $("#' . $option['dest_id'] . '").val(newstring);    
                    });
                    this.on("complete", function (file) {
                        //$("input").remove(".dz-hidden-input");
                        $(\'.dz-hidden-input\').hide();
                    });
                }
});

    ';
        return $return;
    }

    public static function upload_folder($folder = NULL, $date = true)
    {

        if ($folder != NULL) {
            $th = $folder . '/';
        } else {
            $th = '';
        }
        if ($date) {
            $th = $th . date('Y') . '/' . date('m') . '/' . date('d') . '/';
        }
        if (!file_exists(JK_SITE_PATH() . 'storage/files/' . $th)) {
            mkdir(JK_SITE_PATH() . 'storage/files/' . $th, 0777, true);
        }
        return 'storage/files/' . $th;
    }

    public static function getfile($fileID, $url = true, $type = 'original', $col = 'file', $default = "")
    {


        if ($type == 'original') {
            $get = Database::connect()->get('jk_uploads', $col, [
                "AND" => [
                    "id" => $fileID,
                    "source" => $type,
                ]
            ]);
        } else {
            $get = Database::connect()->get('jk_uploads', $col, [
                "AND" => [
                    "parent" => $fileID,
                    "source" => $type,
                ]
            ]);

        }

//        if ($get != "") {
//            if (stripos($get, 'storage/') !== false) {
//                $get = explode('/', $get);
//                array_shift($get);
//                $get = implode('/', $get);
//            }
//        }

        if ($url == true) {
            if ($get != '') {
                $get = JK_DOMAIN() . $get;
            } else {
                $get = $default;
            }
        }
        if ($get == "" && $default != "") {
            $get = $default;
        }
        return $get;
    }

    public static function getLink($fileID, $type = 'original')
    {


        if ($type == 'original') {
            $get = Database::connect()->get('jk_uploads', ['id', 'name'], [
                "AND" => [
                    "id" => $fileID,
                    "source" => $type,
                ]
            ]);
        } else {
            $get = Database::connect()->get('jk_uploads', '*', [
                "AND" => [
                    "parent" => $fileID,
                    "source" => $type,
                ]
            ]);

        }
        if ($get) {
            $get = JK_DOMAIN_LANG() . 'file/show/' . $get['id'] . '/' . $get['name'];
        } else {
            $get = JK_DOMAIN_LANG() . 'file/show/404/404.jpg';
        }
        return $get;
    }

    public static function getDataThumbail($dataID, $thName = "", $url = true, $default = "")
    {
        $back = $default;
        $thID = Database::connect()->get('jk_thumbnails', 'id', [
            "name" => $thName
        ]);
        $image = \Joonika\Modules\Blog\Blog::getDataTh($dataID, $thID);
        if ($image && $image != "") {
            $thumbnail = self::getLink($image);
        }
        return $back;
    }

    public static function getFileInfo($id, $type = 'original')
    {

        if ($type == 'original') {
            $getInfo = Database::connect()->get("jk_uploads", "*",
                [
                    "AND" => [
                        "id" => $id,
                        "source" => $type,
                    ]
                ]);
        } else {
            $getInfo = Database::connect()->get("jk_uploads", "*",
                [
                    "AND" => [
                        "parent" => $id,
                        "source" => $type,
                    ]
                ]);
        }
        return $getInfo;
    }
}
