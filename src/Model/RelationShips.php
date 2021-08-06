<?php


namespace Joonika\Model;


use Joonika\Database;

trait RelationShips
{
    protected $withRelation = false;
    protected $justRelation = false;

    protected function hasMany($class, $foreignKey = null, $localKey = 'id', $alias = null, $joinType = 'left')
    {
        if (is_null($foreignKey)) {
            $foreignKey = explode('\\', get_class($this));
            $foreignKey = strtolower(array_pop($foreignKey)) . '_id';
        }
        if ($this->withRelation) {
            $this->prepareWithRelation($class, $foreignKey, $localKey, false);
        } else {
            $this->prepareJoin($class, $joinType, $alias, $localKey, $foreignKey);
        }
        return $this;
    }

    protected function hasOne($class, $foreignKey = null, $localKey = 'id', $alias = null, $joinType = 'left')
    {
        if (is_null($foreignKey)) {
            $foreignKey = explode('\\', get_class($this));
            $foreignKey = strtolower(array_pop($foreignKey)) . '_id';
        }
        if ($this->withRelation) {
            $this->prepareWithRelation($class, $foreignKey, $localKey);
        } else {
            $this->prepareJoin($class, $joinType, $alias, $localKey, $foreignKey);
        }
        return $this;
    }

    protected function belongsTo($class, $foreignKey = null, $localKey = 'id', $alias = null, $joinType = 'left')
    {
        if (is_null($foreignKey)) {
            $foreignKey = explode('\\', get_class($this));
            $foreignKey = strtolower(array_pop($foreignKey)) . '_id';
        }
        if ($this->withRelation) {
            $this->prepareWithRelation($class, $foreignKey, $localKey);
        } else {
            $this->prepareJoin($class, $joinType, $alias, $localKey, $foreignKey);
        }
        return $this;
    }

    private function prepareJoin($class, $joinType, $alias, $localKey, $foreignKey)
    {
        $alias = is_null($alias) ? debug_backtrace()[2]['function'] : $alias;
        $this->hasMany[] = $class;
        switch ($joinType) {
            case "inner":
                $this->setJoin($class, 'INNER', $alias);
                break;
            case "full":
                $this->setJoin($class, 'FULL OUTER', $alias);
                break;
            case "right":
                $this->setJoin($class, 'RIGHT', $alias);
                break;
            default:
                $this->setJoin($class, 'LEFT', $alias);
                break;
        }

        $this->on([$localKey => $class . "|" . $foreignKey]);
    }

    private function prepareWithRelation($class, $foreignKey, $localKey, $returnOne = true)
    {
        $rightTable = new $class();
        $rightClassName = explode('\\', $class);
        $rightClassName = $rightClassName[sizeof($rightClassName) - 1];
        $leftConditions = [];
        $relConditions = [];
        $leftSelection = [];
        if (isset($this->whereJoin[$this->table])) {
            $leftConditions = $this->whereJoin[$this->table];
        }
        if (isset($this->selectJoin[$this->table])) {
            $leftSelection = $this->selectJoin[$this->table];
        }
        if (checkArraySize($leftSelection)) {
            $nlC = [];
            foreach ($leftSelection as $item => $value) {
                if (is_string($item)) {
                    $nlC[] = $item . '(' . $value . ')';
                } else {
                    $nlC[] = $value;
                }
            }
            $leftSelection = $nlC;
        } else {
            $leftSelection = '*';
        }

        $leftResult = Database::select($this->table, $leftSelection, $leftConditions);

        if ($this->justRelation) {
            $justResult = [];
        }
        if (checkArraySize($leftResult)) {
            for ($i = 0; $i < sizeof($leftResult); $i++) {
                if ($this->justRelation) {
                    if ($returnOne) {
                        $relResult = [Database::get($rightTable->table, '*', [$foreignKey => $leftResult[$i][$localKey]])];
                    } else {
                        $relResult = Database::select($rightTable->table, '*', [$foreignKey => $leftResult[$i][$localKey]]);
                    }
                    if (checkArraySize($relResult)) {
                        $justResult[$i] = $relResult;
                    }
                } else {
                    if ($returnOne) {
                        $relResult = [Database::get($rightTable->table, '*', [$foreignKey => $leftResult[$i][$localKey]])];
                    } else {
                        $relResult = Database::select($rightTable->table, '*', [$foreignKey => $leftResult[$i][$localKey]]);
                    }
                    if (checkArraySize($relResult) && checkArraySize($relResult[0])) {
                        $leftResult[$i][$rightClassName] = $relResult;
                    }

                }
            }
        }
        if ($this->justRelation) {
            $this->response = $justResult;
        } else {
            $this->response = $leftResult;
        }
    }

}