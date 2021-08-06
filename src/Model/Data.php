<?php


namespace Joonika\Model;


use Joonika\Database;

class Data
{

    private $query = null;
    private $originalData = null;
    public $data = null;
    private $changedData = ['update' => []];
    private $tables = null;
    private $tempJoin = null;

    public function __construct($data, $query, $tables)
    {
        $this->originalData = $data;
        $this->tables = $tables;
        $this->data = $data;
        $this->query = $query;
    }

    public function save()
    {

//        $this->tables['obj_ww'] = 'obj';
        $this->arrayRecursiveDiff($this->data, $this->originalData);
        if (checkArraySize($this->data) && checkArraySize($this->originalData)) {
            if (checkArraySize($this->changedData['update'])) {
                foreach ($this->changedData['update'] as $item => $value) {
                    if (!empty($this->changedData['update'][$this->getOriginalTable()]) && $item != $this->getOriginalTable() && checkArraySize($value)) {
                        $i = 0;
                        foreach ($value as $i => $v) {
                            if (in_array($v, $this->changedData['update'][$this->getOriginalTable()])) {
                                $index = array_search($v, $this->changedData['update'][$this->getOriginalTable()]);
                                unset($this->changedData['update'][$this->getOriginalTable()][$index]);
                            }
                            $i++;
                        }
                    }
                }
            }
        }

        if (checkArraySize($this->changedData['update'])) {
            $database = Database::connect();
            foreach ($this->changedData['update'] as $item => $value) {
                if (checkArraySize($value)) {
                    foreach ($value as $v) {
                        $data = json_decode($v, true);
                        if (!empty($data['id'])) {
                            $id = $data['id'];
                            unset($data['id']);
                            $database->update($item, $data, ['id' => $id]);
                        }
                    }
                }
            }
        }

    }



    private function arrayRecursiveDiff($aArray1, $aArray2)
    {
        $aReturn = array();
        $i = 0;
        foreach ($aArray1 as $mKey => $mValue) {
            if (array_key_exists($mKey, $aArray2)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = $this->arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                    if (count($aRecursiveDiff)) {
                        if (in_array($mKey, $this->tables)) {
                            $this->tempJoin = $mKey;
                            if (is_string($mKey)) {
                                $this->changedData['update'][$this->getTable($mKey)][] = json_encode($this->checkArray($aArray1[$mKey]), JSON_UNESCAPED_UNICODE);
                                $aReturn[$mKey] = $aRecursiveDiff;
                            }
                        }
                    }
                } else {
                    if ($mValue != $aArray2[$mKey]) {
                        $this->changedData['update'][$this->getOriginalTable()][] = json_encode($this->checkArray($aArray1), JSON_UNESCAPED_UNICODE);
                        $aReturn[$mKey] = $mValue;
                    }
                }
            } else {
                if (in_array($mKey, $this->tables)) {
                    $this->changedData['update'][$this->getTable($mKey)][] = json_encode($aArray1[$mKey], JSON_UNESCAPED_UNICODE);
                    $aReturn[$mKey] = $mValue;
                } else {
//                    $this->changedData['update']['origin'][] = [
//                        json_encode($this->checkArray($aArray1), JSON_UNESCAPED_UNICODE)
//                    ];
//                    $aReturn[$mKey] = $mValue;
                }
            }
            $i++;
        }
        return $aReturn;
    }

    private function checkArray($array)
    {
        $n = [];
        if (checkArraySize($array)) {
            foreach ($array as $item => $value) {
                if (!is_array($value)) {
                    $n[$item] = $value;
                }
            }
        }
        return $n;
    }

    private function getOriginalTable()
    {
        if (checkArraySize($this->tables)) {
            return array_key_first($this->tables);
        }
    }

    private function getTable($name)
    {
        if (checkArraySize($this->tables)) {
            $name = array_search($name, $this->tables);
            if (strlen($name) > 0) {
                return $name;
            } else {
                return null;
            }
        }
    }

}