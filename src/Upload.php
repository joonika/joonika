<?php
namespace Joonika\Upload;

function field_upload($options=[])
{
    global $View;
    global $data;
    $return='';

    $option=[
        "title"=>'',
        "value"=>'',
        "type"=>'text',
        "maxfiles"=>4,
        "form-group"=>true,
        "theme"=>false,
        "afterSuccess"=>'',
        "module"=>'blog_post',
        "name"=>'',
        "thMaker"=>1,
        "id"=>'',
        "attr"=>[
            "data-msg"=>__("this field is required")
        ],
        "ColType"=>'4,8',
        "help"=>'',
        "required"=>false,
        "direction"=>JK_DIRECTION,
        "text"=>__('Drop files to upload'),
    ];
    if(sizeof($option)>=1){
        foreach ($option as $key=>$opt){
            if(isset($options[$key])){
                $option[$key]=$options[$key];
            }
        }
    }

    if(!isset($option['title'])){
        $option['title']=$option['name'];
    }
    if($option['id']==""){
        $option['id']=$option['name'];
    }
    if($option['value']==""){
        if(isset($data[$option['name']])){
            $option['value']=$data[$option['name']];
        }
    }
    $atts='';
    if(sizeof($option['attr'])>=1){
        foreach ($option['attr'] as $key=>$v){
            $atts=' '.$key.'="'.$v.'" ';
        }
    }

    $value = rawurldecode($option['value']);

    $optioncols=explode(',',$option['ColType']);

    if($option['form-group']==true){
        $return.='
        <div class="form-group">
        <label class="col-md-'.$optioncols[0].' control-label">
            '.$option['title'].'
        </label>

        <div class="col-md-'.$optioncols[1].'">
        ';
    }
    $requ='';
    if($option['required']==true){
        $requ='required="required"';
    }

    $return.='
                <input type="hidden"  class="form-control '.$option['direction'].'" name="'.$option['name'].'"  id="'.$option['name'].'" value="'.$value.'"  '.$requ.' '.$atts.' >
                
                <div id="myId_upload_'.$option['name'].'" class="dropzone">
        <div class="fallback">
            <input class="" name="file" type="file" multiple  />
        </div>
    </div>
    ';

    if($option['form-group']==true) {
        if ($option['help'] != '') {
            $return .= '
        <span class="help-block ' . $option['direction'] . '">' . $option['help'] . '</span>
        ';
        }
        $return.='</div>
    </div>';
    }
$befores=[];
if($value!=""){
    $befores=explode(',',$value);
}
    $View->footer_js('<script>
'.dropzone_script([
            "maxfiles"=>$option['maxfiles'],
            "module"=>$option['module'],
            "dest_id"=>$option['id'],
            "name"=>$option['name'],
            "thMaker"=>$option['thMaker'],
            "befores"=>$befores,
            "afterSuccess"=>$option['afterSuccess'],
            "text"=>$option['text'],
        ]).'
</script>');



    return $return;
}

function dropzone_script($options=[]){
    global $View;
    $option=[
        "module"=>"",
        "name"=>"",
        "befores"=>[],
        "date"=>1,
        "input"=>"file",
        "return"=>"id",
        "thMaker"=>1,
        "maxfiles"=>1,
        "afterSuccess"=>"",
        "max"=>5,
        "dest_id"=>"upload_address",
        "text"=>__('Drop files to upload').'<span>'.__('or CLICK').'</span>',
    ];
    if(sizeof($option)>=1){
        foreach ($option as $key=>$opt){
            if(isset($options[$key])){
                $option[$key]=$options[$key];
            }
        }
    }

    $mock='';
    if(is_array($option['befores']) && sizeof($option['befores'])>=1){
        $getfilemock='';
        foreach ($option['befores'] as $before){
            $getmock=getfile($before,false,'original','*');
            if(isset($getmock['id'])){
                $sizefile=@filesize(JK_SITE_PATH.$getmock['file']);
                $mockdel='';
                if(in_array($getmock['mime'],['image/jpeg','image/gif','image/png','image/vnd.microsoft.icon'])){
                    $mockdel='this.emit("thumbnail", mockFile, "'.JK_DOMAIN.$getmock['file'].'");';
                }
                $mock.='
var mockFile = { name: "'.$getmock['name'].'", size: '.$sizefile.',fileid:'.$getmock['id'].' , isMock : true};
this.emit("addedfile", mockFile);
this.emit("complete", mockFile);
this.files.push(mockFile);
this.options.maxFiles = this.options.maxFiles - 1;
'.$mockdel.'
              ';
            }
        }
    }
    $return='
    Dropzone.autoDiscover = false;

    $("#myId_upload_'.$option['name'].'").dropzone({ url: "'.JK_DOMAIN_LANG.'cp/main/upload/new" ,
                paramName: "'.$option['input'].'", // The name that will be used to transfer the file.
                params: {
                    module: "'.$option['module'].'",
                    thMaker: "'.$option['thMaker'].'",
                    return: "'.$option['return'].'",
                    date: "'.$option['date'].'",
                },
                addRemoveLinks:true,
                dictDefaultMessage: "'.$option['text'].'",
                dictRemoveFile: "'.__("remove file").'",
                dictMaxFilesExceeded: "'.__("You can not upload any more files.").'",
                uploadMultiple:false,
                maxFiles:'.$option['maxfiles'].',
                maxFilesize: '.$option['max'].', // MB
                init: function() {
                '.$mock.'

                    this.on("success", function (file, response) {
                        obj = JSON.parse(response);
                        var beforeval=$(\'#'.$option['dest_id'].'\').val();
                        if(beforeval!=""){
                            beforeval=beforeval+",";
                        }
                        beforeval=beforeval+obj.filename;
                        $(\'#'.$option['dest_id'].'\').val(beforeval);
                        '.$option['afterSuccess'].'
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
                    

var array = $("#'.$option['dest_id'].'").val().split(",");    
   $.each(array,function(i){
   if(this!=removeid){
   newstring+=this+",";
   }
   });
   newstring=newstring.replace(/,+$/,\'\');  
   $("#'.$option['dest_id'].'").val(newstring);    
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

function dropzone_load(){
    global $View;
    $View->footer_js_files("/modules/cp/assets/dropzone/dropzone.js");
    $View->header_styles_files("/modules/cp/assets/dropzone/dropzone.css");
}

function upload_folder($folder = NULL,$date=true)
{

    if ($folder != NULL) {
        $th = $folder.'/';
    } else {
        $th = '';
    }
    if($date){
        $th=$th . date('Y') . '/' . date('m') . '/' . date('d').'/';
    }
    if (!file_exists('files/'.$th)) {
        mkdir('files/'.$th, 0777, true);
    }

    return 'files/'.$th;
}

function getfile($fileID,$url=true,$type='original',$col='file',$default=""){
    global $database;

    if($type=='original'){
        $get=$database->get('jk_uploads',$col,[
            "AND"=>[
                "id"=>$fileID,
                "source"=>$type,
            ]
        ]);
    }else{
        $get=$database->get('jk_uploads',$col,[
            "AND"=>[
                "parent"=>$fileID,
                "source"=>$type,
            ]
        ]);

    }
    if($url==true){
        if($get!=''){
            $get=JK_DOMAIN.$get;
        }else{
            $get=$default;
        }
    }
    if($get=="" && $default!=""){
        $get=$default;
    }
    return $get;
}
function getDataThumbail($dataID,$thName="",$url=true,$default=""){
    global $database;
    $back=$default;
    $thID=$database->get('jk_thumbnails','id',[
        "name"=>$thName
    ]);
    $image=\Joonika\Modules\Blog\getDataTh($dataID,$thID);
    if($image && $image!=""){
        $back = \Joonika\Upload\getfile($image, false);
    }
return $back;
}

function getFileInfo($id,$type='original'){
    global $database;
    if($type=='original') {
        $getInfo = $database->get("jk_uploads", "*",
            [
                "AND" => [
                    "id" => $id,
                    "source" => $type,
                ]
            ]);
    }else{
        $getInfo = $database->get("jk_uploads", "*",
            [
                "AND" => [
                    "parent" => $id,
                    "source" => $type,
                ]
            ]);
    }
    return $getInfo;
}

function getThumbnails($websiteID=""){
    $back=[];
    if($websiteID=="" && defined('JK_WEBSITE_ID')){
        $websiteID=JK_WEBSITE_ID;
    }
    if($websiteID!=""){
        global $database;
        $ths=$database->select('jk_thumbnails','*',[
            "websiteID"=>$websiteID
        ]);
        if(sizeof($ths)>=1){
            foreach ($ths as $th){
                array_push($back,[
                    "id"=>$th['id'],
                    "name"=>$th['name'],
                    "w"=>$th['width'],
                    "h"=>$th['height'],
                ]);
            }
        }
    }
        return $back;
}