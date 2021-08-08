<?php


namespace Joonika\Traits;


use Joonika\Errors;

trait Response
{
    protected $header = "application/json";
    protected $export = "json";
    protected $errors = [];
    protected $onlyData = false;
    public $output = [
        "success" => false,
    ];
    protected $status = 404;
    protected $success = false;
    protected $data = [];

    public function setResponseSuccess($data = [], $success = true, $onlyData = false,$errors=[])
    {
        $this->status = 200;
        $this->success = $success;
        $this->data = $data;
        $this->onlyData = $onlyData;
        $this->output = [
            "success" => true,
            "data" => $data,
        ];
        if(!empty($errors)){
            $this->output['errors']=$errors;
        }
        $this->view_render(false);
    }

    public function setResponseError($error, $exit = true, $code = null)
    {
        $this->status = empty($code) ? $this->status : $code;
        $this->success = false;
        if (isset($error['message'])) {
            $alertStructure['message'] = $error['message'];
            if (!empty($error['source'])) {
                $alertStructure['source'] = $error['source'];
            }
            if (!empty($error['data'])) {
                $alertStructure['data'] = $error['data'];
            }
            if (!empty($error['code'])) {
                $alertStructure['code'] = $error['code'];
            }
            $this->errors = array_merge($this->errors, [$alertStructure]);

        } else {
            if(!empty($error[0]['message'])){
                foreach ($error as $er){
                    $this->errors = array_merge($this->errors, $er);

                }
            }elseif (!empty($error)) {
                $this->errors = array_merge($this->errors, [["message" => $error]]);
            }
        }
        if ($exit) {
            exit();
        }
    }

    public function prepareOutput()
    {
        $statusMessageCode = Errors::statusCodeMessage($this->status);
        if ($this->export == "json" && !empty($this->Route->isApi)) {
            if(!$this->foundMethod && !empty($this->output['data']['success'])){
                $this->success=false;
                $this->status=404;
                $this->errors=[
                    "message"=>__("not found")
                ];
                $statusMessageCode = Errors::statusCodeMessage($this->status);
            }
            $this->success=!empty($this->output['success'])?$this->output['success']:$this->success;
            header('HTTP/1.1 ' . $this->status . ' ' . $statusMessageCode);
            header('Content-Type: ' . $this->header);
            $this->output['success'] = $this->success;
            if ($this->success) {
                $this->output['data'] = $this->data;
            } else {
                if (empty($this->errors)) {
                    $this->errors[] = ['message' => __("not found")];
                }
                $this->output['errors'] = $this->errors;
            }
        } else if ($this->export == "download") {
            $this->output = null;
        }
        if (!empty($this->onlyData) && !empty($this->data)) {
            $this->output = $this->data;
        }
        return $this->output;
    }

}
