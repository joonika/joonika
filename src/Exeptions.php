<?php
namespace Joonika;


class Exeptions extends \Exception {
    protected $error_msg;
    public function errorMessage2() {
        //defining the error message
        $error_msg = 'Error caught on line '.$this->getLine().' in '.$this->getFile() .': <b>'.$this->getMessage().'</b>';
        return $error_msg;
    }
}