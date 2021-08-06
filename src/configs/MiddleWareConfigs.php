<?php

namespace Joonika\configs;

class MiddleWareConfigs
{
    protected $middleWaresPriorities = [];

    public function __construct()
    {
        $methods = ['beforeBoot','boot','run'];
        foreach ($methods as $method){
            if (method_exists($this, $method)) {
                $this->$method();
            }
        }
    }

    /**
     * @return array
     */
    public function getMiddleWaresPriorities(): array
    {
        return $this->middleWaresPerioties;
    }

    /**
     * @param array $middleWaresPerioties
     */
    public function setMiddleWaresPriorities(array $middleWaresPerioties): void
    {
        $this->middleWaresPerioties = $middleWaresPerioties;
    }


}