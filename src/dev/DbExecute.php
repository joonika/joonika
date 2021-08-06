<?php


namespace Joonika\dev;


abstract class DbExecute
{
    private $queries = [];

    final public function addTable($query)
    {
        $this->queries[] = $query;
    }

    final public function getQuerires()
    {
        return $this->queries;
    }

    abstract public function installTables();

    abstract public function updateTables();
}