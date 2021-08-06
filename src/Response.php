<?php


namespace Joonika;


class Response
{

    use \Joonika\Traits\Response;

    public function __construct($code, $msg = null, $output = null, $showDataOnly = false, $outType = 'json', $header = 'application/json')
    {
        $this->header = $header;
        $this->response($code, $msg, $output, $showDataOnly, $outType, $header);
        $this->prepareOutPut();
    }

    public function send()
    {
        echo $this->output;
    }
}