<?php


namespace Joonika\Model;

use InvalidArgumentException;
use Joonika\Database;
use Joonika\Seeder\Faker;
use function Composer\Autoload\includeFile;

class Raw
{
    public $map;
    public $value;
}

abstract class Model
{
    use QueryBuild;
    use RelationShips;

    protected $table = null;
    protected $response;
    protected $id;
    protected $hasMany = [];
    protected $query = null;
    protected $getQuery = false;
    protected $type;
    protected $finalWhere;
    protected $join;
    public $lastQuery;

    //-----
    protected $whereJoin = [];
    protected $joined = false;
    protected $joinType = null;
    protected $selectJoin = [];
    protected $useCahr;
    protected $aliasTable = [];
    protected $on = [];
    protected $level = 0;
    protected $levelJoin = 0;
    protected $levelWhere = 0;
    protected $levelCondition = [];
    protected $levelJoinTables = [];
    protected $levelWhereConditions = [];
    protected $limitCondition = null;
    protected $havingTemp = [];
    protected $havingTempMap = [];
    protected $orderPriorities = null;
    protected $modelTableNames = null;
    protected $distinct = false;
    protected $debug = false;
    private $getOneItem = false;
    private $getCountItems = false;


    public function setJoin(string $class, $type, $alias = null)
    {
        $this->joined = true;
        $rightTable = new $class();
        $this->rightTable = $rightTable->table;
        $this->modelTableNames[$class] = $this->rightTable;
        $this->levelJoinTables[$this->levelJoin] = [
            'type' => $type,
            'typeTxt' => $type . ' JOIN',
            'rightTable' => $this->rightTable,
            'leftTable' => $this->table
        ];
        $this->levelJoin++;
        $this->checkUseChar($alias);
        if (!$this->joined) {
            $this->aliasTable[$this->useCahr] = $this->table;
        } else {
            $this->aliasTable[$this->useCahr] = $this->rightTable;
        }
        return $this;
    }

    public function on(array $joinCondition)
    {
        if (checkArraySize($joinCondition)) {
            foreach ($joinCondition as $item => $value) {
                $valEx = explode('|', $value);
                $rightTable = '';
                $rightTableColumn = '';
                if (sizeof($valEx) > 1) {
                    $rightTableColumn = $valEx[1];
                    $rightTable = new $valEx[0];
                    $rightTable = $rightTable->table;
                }
                $itemEx = explode('|', $item);
                $leftTable = $this->table;
                $leftTableColumn = $itemEx[0];
                if (sizeof($itemEx) > 1) {
                    $leftTableColumn = $itemEx[1];
                    $leftTable = new $itemEx[0];
                    $leftTable = $table->table;
                }
                $this->on[$this->level][] = [
                    'left' => [$leftTable, $leftTableColumn],
                    'right' => [$rightTable, $rightTableColumn]
                ];
            }
        }
        $this->level++;
        return $this;
    }

    public function select($select = '*', $alias = null)
    {
        if (is_null($this->useCahr)) {
            $this->checkUseChar($alias);
        }
        if (!$this->joined) {
            $this->selectJoin[$this->table] = $select;
            $this->modelTableNames[get_called_class()] = $this->table;
            $this->aliasTable[$this->useCahr] = $this->table;
            $this->useCahr = null;
        } else {
            if (!is_null($alias)) {
                $this->useCahr = $alias;
            }
            $nSelect = [];
            if (checkArraySize($select)) {
                foreach ($select as $value) {
                    $nSelect[] = $value . " AS " . $this->useCahr . "___" . $value;
                }
                $select = $nSelect;
            }
            $this->selectJoin[$this->rightTable] = $select;
            $this->aliasTable[$this->useCahr] = $this->rightTable;
            $this->useCahr = null;
        }
        return $this;
    }

    public function selectOne($select = '*')
    {
        $this->getOneItem = true;
        $this->checkUseChar();
        if (!$this->joined) {
            $this->selectJoin[$this->table] = $select;
            $this->modelTableNames[get_called_class()] = $this->table;
            $this->aliasTable[$this->useCahr] = $this->table;
        } else {
            $this->selectJoin[$this->rightTable] = $select;
            $this->aliasTable[$this->useCahr] = $this->rightTable;
        }
        return $this;
    }

    public function count($select = '*')
    {
        $this->getCountItems = true;
        $this->checkUseChar();
        if (!$this->joined) {
            $this->selectJoin[$this->table] = $select;
            $this->modelTableNames[get_called_class()] = $this->table;
            $this->aliasTable[$this->useCahr] = $this->table;
        } else {
            $this->selectJoin[$this->rightTable] = $select;
            $this->aliasTable[$this->useCahr] = $this->rightTable;
        }
        return $this;
    }

    public function exec()
    {
        if (is_null($this->query)) {
            $this->query = 'SELECT';
            if ($this->distinct) {
                $this->query .= ' DISTINCT ';
            }
            $this->aliasTable = array_flip($this->aliasTable);

            $select = ' ';

            if (checkArraySize($this->selectJoin)) {
                foreach ($this->selectJoin as $item => $value) {
                    $alias = $this->aliasTable[$item];
                    if (checkArraySize($value)) {
                        foreach ($value as $v => $si) {
                            if (is_string($v) && $v == 'raw' && checkArraySize($si)) {
                                foreach ($si as $sii) {
                                    $select .= str_replace($sii['column'], $alias . '.' . $sii['column'], $sii['raw']) . ' AS ' . $sii['alias'] . ',';
                                    $sii['withAlias'] = str_replace($sii['column'], $alias . '.' . $sii['column'], $sii['raw']);
                                    $sii['alias'] = $alias . '.' . $sii['column'];
                                    $this->havingTemp[$item] = $sii;
                                }
                            } elseif (is_string($v)) {
                                $select .= $alias . '.' . $v . ' AS ' . $si . ',';
                            } else {
                                $select .= $alias . '.' . $si . ',';
                            }
                        }
                    } else {
                        if ($this->getCountItems) {
                            $select = ' count(*) ';
                        } else {
                            $select = ' * ';
                        }
                    }
                }
            } else {
                if ($this->getCountItems) {
                    $select = ' count(*) ';
                } else {
                    $select = ' * ';
                }
            }

            $select = rtrim($select, ',');
            $this->query .= $select . ' FROM ' . $this->table . ' AS ' . $this->aliasTable[$this->table];

            if (checkArraySize($this->levelJoinTables)) {
                $on = [];
                $onOut = '';
                for ($iii = 0; $iii < sizeof($this->levelJoinTables); $iii++) {
                    $this->query .= ' ' . $this->levelJoinTables[$iii]['typeTxt'] . ' ' . $this->levelJoinTables[$iii]['rightTable'] . ' AS ' . $this->aliasTable[$this->levelJoinTables[$iii]['rightTable']] . ' ';
                    if (checkArraySize($this->on)) {
                        $onN = $this->on[$iii];
                        foreach ($onN as $item => $value) {
                            if (checkArraySize($value)) {
                                $on[] = $this->aliasTable[$value['left'][0]] . '.' . $value['left'][1] . '=' . $this->aliasTable[$value['right'][0]] . '.' . $value['right'][1];
                            }
                        }

                        if (checkArraySize($on)) {
                            $onOut = '';
                            for ($i = 0; $i < sizeof($on); $i++) {
                                $onOut .= $on[$i];
                                if ($i != sizeof($on) - 1) {
                                    $onOut .= ' AND ';
                                }
                            }
                        }
                        $on = [];
                        $this->query .= 'ON ' . $onOut;
                    }
                }
            }

            $wQ = '';
            $ANDConditions = [];
            $LimitConditions = null;
            $OrderConditions = null;
            $GroupConditions = null;
            $HavingConditions = null;
            $OtherConditions = null;
            if (checkArraySize($this->whereJoin)) {
                foreach ($this->whereJoin as $item => $value) {
                    if (checkArraySize($value)) {
                        foreach ($value as $ii => $vv) {
                            if (strtoupper($ii) == 'AND') {
                                $ANDConditions[$item] = $vv;
                            } elseif (in_array($ii, ['GROUP', 'ORDER', 'LIMIT', 'HAVING'])) {
                                if (strtoupper($ii) == 'LIMIT') {
                                    $LimitConditions = [$ii => $vv];
                                } elseif (strtoupper($ii) == 'ORDER') {
                                    if (checkArraySize($vv)) {
                                        foreach ($vv as $singleOrderV => $singleOrderI) {
                                            if (is_string($singleOrderV) && $singleOrderV == "FIELD" && checkArraySize($singleOrderI)) {
                                                foreach ($singleOrderI as $singleOrderII => $singleOrderIV) {
                                                    $OrderConditions[$item]["FIELD"][] = [
                                                        $singleOrderIV[0],
                                                        $this->aliasTable[$item] . '.' . $singleOrderIV[1],
                                                        $singleOrderIV[2][0]
                                                    ];
                                                }
                                            } else {
                                                $OrderConditions[$item][] = [$this->aliasTable[$item] . '.' . array_keys($singleOrderI)[0] => array_values($singleOrderI)[0]];
                                            }
                                        }
                                    }


                                    /*  if (checkArraySize($vv)) {
                                          foreach ($vv as $singleOrder) {
                                              $OrderConditions[] = [$this->aliasTable[$item] . '.' . array_keys($singleOrder)[0] => array_values($singleOrder)[0]];
                                          }
                                      }*/

                                } elseif (strtoupper($ii) == 'GROUP') {
                                    $GroupConditions[$item] = $vv;
                                } elseif (strtoupper($ii) == 'HAVING') {
                                    $HavingConditions[$item] = $vv;
                                }
                            } else {
                                $ANDConditions[$item][$ii] = $vv;
                            }
                        }
                    }
                }

                if (!is_null($this->limitCondition)) {
                    $LimitConditions["LIMIT"] = $this->limitCondition;
                }

                $finalANDConditions = '';
                if (checkArraySize($ANDConditions)) {
                    $iiii = 0;
                    foreach ($ANDConditions as $item => $value) {
                        $finalANDConditions .= '(';
                        $finalANDConditions .= $this->buildWhereQuery($value, $item);
                        $finalANDConditions .= ')';
                        if ($iiii != sizeof($ANDConditions) - 1) {
                            $finalANDConditions .= " AND ";
                        }
                        $iiii++;
                    }
                    $wQ .= " WHERE " . $finalANDConditions;
                }
            }

            $finalOrderByCondition = '';
            if (checkArraySize($GroupConditions)) {
                $finalOrderByCondition .= " GROUP BY ";
                foreach ($GroupConditions as $table => $tG) {
                    if (checkArraySize($tG)) {
                        foreach ($tG as $t) {
                            $finalOrderByCondition .= $this->aliasTable[$table] . '.' . $t . ',';
                        }
                    }
                }
                $wQ .= rtrim($finalOrderByCondition, ',');
            }

            $finalHavingCondition = '';
            if (checkArraySize($HavingConditions)) {
                $finalHavingCondition .= " HAVING ";
                $jjjj = 0;
                foreach ($HavingConditions as $item => $value) {
                    $finalHavingCondition .= '(';
                    if (checkArraySize($this->havingTemp) && isset($this->havingTemp[$item])) {
                        foreach ($value as $hvvv => $ivvv) {
                            if (stripos($hvvv, $this->havingTemp[$item]['raw']) !== false) {
                                $hvv = str_replace($this->havingTemp[$item]['raw'], $this->havingTemp[$item]['column'], $hvvv);
                                $value[$hvv] = $ivvv;
                                $hEx = explode('[', $hvvv);
                                $hName = $hEx[0];
                                $this->havingTempMap[$this->aliasTable[$item]] = [
                                    $this->aliasTable[$item] . '.' . $this->havingTemp[$item]['column'],
                                    $this->havingTemp[$item]['withAlias']
                                ];
                                unset($value[$hvvv]);
                            }
                        }
                    }

                    $finalHavingCondition .= $this->buildWhereQuery($value, $item);
                    $finalHavingCondition .= ')';
                    if ($jjjj != sizeof($HavingConditions) - 1) {
                        $finalHavingCondition .= " AND ";
                    }
                    $jjjj++;
                }
                if (checkArraySize($this->havingTempMap)) {
                    foreach ($this->havingTempMap as $hiT => $hiv) {
                        if (stripos($finalHavingCondition, $hiv[0]) !== false) {
                            $finalHavingCondition = str_replace($hiv[0], $hiv[1], $finalHavingCondition);
                        }
                    }
                }
                $wQ .= $finalHavingCondition;
            }

            $finalOrderByCondition = '';
            if (checkArraySize($OrderConditions)) {
                $newOrderConditions = [];
                if (is_null($this->orderPriorities)) {
                    foreach ($OrderConditions as $nocv => $nocvV) {
                        if (checkArraySize($nocvV)) {
                            foreach ($nocvV as $nocv => $nocvVv) {
                                $newOrderConditions[$nocv] = $nocvVv;
                            }
                        }
                    }
                    $OrderConditions = $newOrderConditions;
                } else {
                    foreach ($this->orderPriorities as $priority) {
                        if (isset($OrderConditions[$this->modelTableNames[$priority]])) {
                            $newOrderConditions[] = $OrderConditions[$this->modelTableNames[$priority]];
                        }
                    }
                    $OrderConditions = [];
                    foreach ($newOrderConditions as $newOrderConditionV) {
                        if (checkArraySize($newOrderConditionV)) {
                            foreach ($newOrderConditionV as $nocv => $nocvV) {
                                $OrderConditions[$nocv] = $nocvV;
                            }
                        }
                    }
                }

                $finalOrderByCondition .= " ORDER BY ";
                $orderByIndex = 0;
                foreach ($OrderConditions as $oI => $oV) {
                    if (is_string($oI) && $oI == "FIELD" && checkArraySize($oV)) {
                        foreach ($oV as $oVI => $oVV) {
                            $exFieldOrderString = '';
                            foreach (explode(',', $oVV[2]) as $exFieldOrderStringI) {
                                $exFieldOrderString .= "'" . $exFieldOrderStringI . "',";
                            }
                            $exFieldOrderString = rtrim($exFieldOrderString, ',');
                            $finalOrderByCondition .= 'FIELD(' . $oVV['1'] . "," . $exFieldOrderString . ') ' . $oVV[0];
                        }
                    } else {
                        $i = array_values($oV)[0];
                        $k = array_keys($oV)[0];
                        $finalOrderByCondition .= $k . ' ' . $i;
                    }
                    $orderByIndex++;
                    if ($orderByIndex != sizeof($OrderConditions)) {
                        $finalOrderByCondition .= ',';
                    }
                }
                $wQ .= $finalOrderByCondition;
            }

            $finalLimitCondition = '';
            if ($this->getOneItem) {
                $finalLimitCondition .= " LIMIT 1 ";
                $wQ .= $finalLimitCondition;
            } elseif (checkArraySize($LimitConditions)) {
                $finalLimitCondition .= " LIMIT ";
                if (sizeof($LimitConditions['LIMIT']) == 2) {
                    $finalLimitCondition .= $LimitConditions['LIMIT'][0] . ',' . $LimitConditions['LIMIT'][1];
                } elseif (sizeof($LimitConditions['LIMIT']) == 1) {
                    $finalLimitCondition .= $LimitConditions['LIMIT'][0];
                }
                $wQ .= $finalLimitCondition;
            }

            $this->query .= $wQ . ';';

            $matches = [];
            preg_match_all("'\'[A-Z a-z 0-9]*\.[A-Z a-z 0-9]*\''s", $this->query, $matches);
            if (!empty($matches[0]) && checkArraySize($matches[0])) {
                foreach ($matches[0] as $match) {
                    $this->query = str_replace($match, str_replace("'", '', $match), $this->query);
                }
            }

        }

        if ($this->getQuery) {
            return $this->query;
        } else {
            if ($this->debug) {
                $this->response = Database::debug()->query($this->query)->fetchAll(\PDO::FETCH_ASSOC);
            } else {
                $firstResponse = Database::query($this->query)->fetchAll(\PDO::FETCH_ASSOC);

                $this->response = [];
                if (checkArraySize($firstResponse)) {
                    $i = 0;
                    foreach ($firstResponse as $value) {
                        if (checkArraySize($value)) {
                            foreach ($value as $k => $v) {
                                $ex = explode('___', $k);
                                if (sizeof($ex) == 2) {
                                    $this->response[$i][$ex[0]][$ex[1]] = $v;
                                } else {
                                    $this->response[$i][$k] = $v;
                                }
                            }
                        }
                        $i++;
                    }
                }

                $this->lastQuery = Database::last();
            }
            return $this;
        }
    }

    public function buildQ($type)
    {
        $this->type = $type;
        return $this;
    }

    public function where($conditions)
    {
//        $this->checkUseChar();
        if (!$this->joined) {
            $this->whereJoin[$this->table] = $conditions;
//            $this->aliasTable[$this->useCahr] = $this->table;
        } else {
            $this->whereJoin[$this->rightTable] = $conditions;
//            $this->aliasTable[$this->useCahr] = $this->rightTable;
        }
        return $this;
    }

    public function with($model)
    {
        $this->join = true;
        if (method_exists($this, $model)) {
            $this->withRelation = true;
            $this->$model();
        } else {
            return null;
        }
        return $this;
    }

    public function just($model)
    {
        $this->join = true;
        if (method_exists($this, $model)) {
            $this->withRelation = true;
            $this->justRelation = true;
            $this->$model();
        } else {
            return null;
        }
        return $this;
    }

    public function __construct()
    {
        if (is_null($this->table)) {
            $table = explode('\\', get_class($this));
            $table = $table[sizeof($table) - 1];
            $this->table = $table . 's';
        }
        return $this;
    }

    private function reset()
    {
        $this->table = null;
        $this->response = [];
        $this->id = null;
        $this->hasMany = [];
        $this->query = null;
        $this->getQuery = false;
        $this->type = null;
        $this->finalWhere = [];
        $this->join = null;
        $this->lastQuery = null;
        $this->whereJoin = [];
        $this->joined = false;
        $this->joinType = null;
        $this->selectJoin = [];
        $this->useCahr = null;
        $this->aliasTable = [];
        $this->on = [];
        $this->level = 0;
        $this->levelJoin = 0;
        $this->levelWhere = 0;
        $this->levelCondition = [];
        $this->levelJoinTables = [];
        $this->levelWhereConditions = [];
        $this->limitCondition = null;
        $this->havingTemp = [];
        $this->havingTempMap = [];
        $this->orderPriorities = null;
        $this->modelTableNames = null;
        $this->distinct = false;
        $this->debug = false;
        $this->getOneItem = false;
        $this->getCountItems = false;
    }

    public function toJson()
    {
        $data = json_encode($this->response, JSON_UNESCAPED_UNICODE);
        $this->reset();
        return $data;
    }

    public function toArray()
    {
        $data = $this->response;
        $this->reset();
        return $data;
    }

    public function toObj()
    {
        $data = new Data($this->response, $this->query,$this->aliasTable);
        $this->reset();
        return $data;
    }

    public function getQuery()
    {
        $this->getQuery = true;
        return $this;
    }

    public function asc($column)
    {
        if (!$this->joined) {
            $this->whereJoin[$this->table]['ORDER'][] = [$column => "ASC"];
        } else {
            $this->whereJoin[$this->rightTable]['ORDER'][] = [$column => "ASC"];
        }
        /*if (checkArraySize($this->response)) {
            $keys = array_column($this->response, $ascBy);
            array_multisort($keys, SORT_ASC, $this->response);
        }*/
        return $this;
    }

    public function desc($column)
    {
        if (!$this->joined) {
            $this->whereJoin[$this->table]['ORDER'][] = [$column => "DESC"];
        } else {
            $this->whereJoin[$this->rightTable]['ORDER'][] = [$column => "DESC"];
        }
        /*if (checkArraySize($this->response)) {
            $keys = array_column($this->response, $descBy);
            array_multisort($keys, SORT_DESC, $this->response);
        }*/
        return $this;
    }

    public function ascByField($column, $order)
    {
        if (!$this->joined) {
            $this->whereJoin[$this->table]['ORDER']['FIELD'][] = ["ASC", $column, $order];
        } else {
            $this->whereJoin[$this->rightTable]['ORDER']['FIELD'][] = ["ASC", $column, $order];
        }
        return $this;
    }

    public function descByField($column, $order)
    {
        if (!$this->joined) {
            $this->whereJoin[$this->table]['ORDER']['FIELD'][] = ["DESC", $column, $order];
        } else {
            $this->whereJoin[$this->rightTable]['ORDER']['FIELD'][] = ["DESC", $column, $order];
        }
        return $this;
    }

    public function orderPriority(array $priorities)
    {
        $this->orderPriorities = $priorities;
        return $this;
    }

    public function groupBy($column)
    {
        if (!$this->joined) {
            if (is_array($column)) {
                foreach ($column as $col) {
                    $this->whereJoin[$this->table]['GROUP'][] = $col;
                }
            } else {
                $this->whereJoin[$this->table]['GROUP'][] = $column;
            }
        } else {
            if (is_array($column)) {
                foreach ($column as $col) {
                    $this->whereJoin[$this->rightTable]['GROUP'][] = $col;
                }
            } else {
                $this->whereJoin[$this->rightTable]['GROUP'][] = $column;
            }
        }
        return $this;
    }

    public function raw($command, $column, string $alias)
    {
        if (!$this->joined) {
            $this->selectJoin[$this->table]['raw'][] = ['raw' => $command, 'column' => $column, "alias" => $alias];
        } else {
            $this->selectJoin[$this->rightTable]['raw'][] = ['raw' => $command, 'column' => $column, "alias" => $alias];
        }
        return $this;
    }

    public function having($condition)
    {
        if (!$this->joined) {
            $this->whereJoin[$this->table]['HAVING'] = $condition;
        } else {
            $this->whereJoin[$this->rightTable]['HAVING'] = $condition;
        }
        return $this;
    }

    public function limit($start, $offset = null)
    {
        $out = $start;
        if (!is_null($offset)) {
            $out = [$start, $offset];
        }
        $this->limitCondition = $out;
        return $this;
    }

    public function get()
    {
        return $this->response;
    }

    public function filter($filter = null)
    {
        $filtered = [];
        if (checkArraySize($filter)) {
            if (checkArraySize($this->response)) {
                foreach ($this->response as $res) {
                    array_push($filtered, array_intersect_key($res, array_flip($filter)));
                }
                $this->response = $filtered;
            }
        }
        return $this;
    }

    public function first()
    {
        return $this->response[0];
    }

    public function insert(array $inputs)
    {
        $this->response = Database::insert($this->table, $inputs);
        return $this;
    }

    public function lastInsertedId()
    {
        return Database::id();
    }

    public function lastInsertedRecord()
    {
        return Database::get($this->table, '*', ['id' => $this->lastInsertedId()]);
    }

    public function delete($where)
    {
        $delete = Database::delete($this->table, $where);
        if ($delete->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function update($data, $where)
    {
        $update = Database::update($this->table, $data, $where);
        if ($update->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function find($id)
    {
        $this->response = Database::get($this->table, '*', ['id' => $id]);
        return $this;
    }

    public function distinct()
    {
        $this->distinct = true;
        return $this;
    }

    public function debug()
    {
        $this->debug = true;
        return $this;
    }

    public function all()
    {
        $this->response = Database::select($this->table, '*');
        return $this;
    }

    public function allID($active = false)
    {
        if ($active) {
            return Database::select($this->table, 'id', [
                    "status" => "active"
                ]
            );
        } else {
            return Database::select($this->table, 'id');
        }

    }

    public function query($query)
    {
        $this->query = $query;
        return $this;
    }

}