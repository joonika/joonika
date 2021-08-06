<?php


namespace Joonika\Model;


use Joonika\Seeder\Faker;

trait QueryBuild
{
    protected $guid = 0;
    protected $rightTable = [];
    protected $tempWhereBuildQuery;
    protected $finalJoinWhere = [];

    protected function bindVal(&$mp, $map)
    {
        foreach ($map as $item => $value) {
            if (stripos($mp, $item) !== false) {
                $mp = str_replace($item, "'" . $value[0] . "'", $mp);
            }
        }
    }

    protected function buildWhereQuery($conditions, $table)
    {
        $map = [];
        $mp = $this->whereClause($conditions, $map, $table);
        $this->bindVal($mp, $map);
        $mpEx = explode('WHERE', $mp)[1];
        return $mpEx;
    }

    public function checkUseChar($alias = null)
    {
        $as = is_null($alias) ? strtoupper(Faker::build()->randomLetter . Faker::build()->randomLetter . Faker::build()->randomLetter) : $alias;
        if (!in_array($as, array_keys($this->aliasTable))) {
            $this->useCahr = $as;
        } else {
            $this->checkUseChar();
        }
    }

    final private function whereClause($where, &$map, $table)
    {
        $where_clause = '';
        if (is_array($where)) {
            $where_keys = array_keys($where);
            $conditions = array_diff_key($where, array_flip(
                ['GROUP', 'ORDER', 'HAVING', 'LIMIT', 'LIKE', 'MATCH']
            ));
            if (!empty($conditions)) {
                $where_clause = ' WHERE ' . $this->dataImplode($conditions, $map, ' AND', $table);
            }
            if (isset($where['MATCH']) && $this->type === 'mysql') {
                $MATCH = $where['MATCH'];
                if (is_array($MATCH) && isset($MATCH['columns'], $MATCH['keyword'])) {
                    $mode = '';
                    $mode_array = [
                        'natural' => 'IN NATURAL LANGUAGE MODE',
                        'natural+query' => 'IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION',
                        'boolean' => 'IN BOOLEAN MODE',
                        'query' => 'WITH QUERY EXPANSION'
                    ];
                    if (isset($MATCH['mode'], $mode_array[$MATCH['mode']])) {
                        $mode = ' ' . $mode_array[$MATCH['mode']];
                    }

                    $columns = implode(', ', array_map([$this, 'columnQuote'], $MATCH['columns']));
                    $map_key = $this->mapKey();
                    $map[$map_key] = [$MATCH['keyword'], \PDO::PARAM_STR];

                    $where_clause .= ($where_clause !== '' ? ' AND ' : ' WHERE') . ' MATCH (' . $columns . ') AGAINST (' . $map_key . $mode . ')';
                }
            }

            if (isset($where['GROUP'])) {
                $GROUP = $where['GROUP'];

                if (is_array($GROUP)) {
                    $stack = [];

                    foreach ($GROUP as $column => $value) {
                        $stack[] = $this->columnQuote($value);
                    }

                    $where_clause .= ' GROUP BY ' . implode(',', $stack);
                } elseif ($raw = $this->buildRaw($GROUP, $map)) {
                    $where_clause .= ' GROUP BY ' . $raw;
                } else {
                    $where_clause .= ' GROUP BY ' . $this->columnQuote($GROUP);
                }

                if (isset($where['HAVING'])) {
                    if ($raw = $this->buildRaw($where['HAVING'], $map)) {
                        $where_clause .= ' HAVING ' . $raw;
                    } else {
                        $where_clause .= ' HAVING ' . $this->dataImplode($where['HAVING'], $map, ' AND');
                    }
                }
            }

            if (isset($where['ORDER'])) {
                $ORDER = $where['ORDER'];

                if (is_array($ORDER)) {
                    $stack = [];

                    foreach ($ORDER as $column => $value) {
                        if (is_array($value)) {
                            $stack[] = 'FIELD(' . $this->columnQuote($column) . ', ' . $this->arrayQuote($value) . ')';
                        } elseif ($value === 'ASC' || $value === 'DESC') {
                            $stack[] = $this->columnQuote($column) . ' ' . $value;
                        } elseif (is_int($column)) {
                            $stack[] = $this->columnQuote($value);
                        }
                    }

                    $where_clause .= ' ORDER BY ' . implode(',', $stack);
                } elseif ($raw = $this->buildRaw($ORDER, $map)) {
                    $where_clause .= ' ORDER BY ' . $raw;
                } else {
                    $where_clause .= ' ORDER BY ' . $this->columnQuote($ORDER);
                }

                if (
                    isset($where['LIMIT']) &&
                    in_array($this->type, ['oracle', 'mssql'])
                ) {
                    $LIMIT = $where['LIMIT'];

                    if (is_numeric($LIMIT)) {
                        $LIMIT = [0, $LIMIT];
                    }

                    if (
                        is_array($LIMIT) &&
                        is_numeric($LIMIT[0]) &&
                        is_numeric($LIMIT[1])
                    ) {
                        $where_clause .= ' OFFSET ' . $LIMIT[0] . ' ROWS FETCH NEXT ' . $LIMIT[1] . ' ROWS ONLY';
                    }
                }
            }

            if (isset($where['LIMIT']) && !in_array($this->type, ['oracle', 'mssql'])) {
                $LIMIT = $where['LIMIT'];

                if (is_numeric($LIMIT)) {
                    $where_clause .= ' LIMIT ' . $LIMIT;
                } elseif (
                    is_array($LIMIT) &&
                    is_numeric($LIMIT[0]) &&
                    is_numeric($LIMIT[1])
                ) {
                    $where_clause .= ' LIMIT ' . $LIMIT[1] . ' OFFSET ' . $LIMIT[0];
                }
            }
        } elseif ($raw = $this->buildRaw($where, $map)) {
            $where_clause .= ' ' . $raw;
        }
        return $where_clause;
    }

    protected function mapKey($key)
    {
        return $key . '===' . $this->guid++;
    }

    protected function columnQuote($string)
    {
        if (!preg_match('/^[a-zA-Z0-9_]+(\.?[a-zA-Z0-9_]+)?$/i', $string)) {
            throw new InvalidArgumentException("Incorrect column name \"$string\"");
        }

        /*if (strpos($string, '.') !== false) {
            return '"' . $this->prefix . str_replace('.', '"."', $string) . '"';
        }*/

        return $string;
        return '"' . $string . '"';
    }

    protected function isRaw($object)
    {
        return $object instanceof Raw;
    }

    protected function tableQuote($table)
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/i', $table)) {
            throw new InvalidArgumentException("Incorrect table name \"$table\"");
        }
        return '"' . $this->prefix . $table . '"';
    }

    protected function buildRaw($raw, &$map)
    {
        if (!$this->isRaw($raw)) {
            return false;
        }

        $query = preg_replace_callback(
            '/(([`\']).*?)?((FROM|TABLE|INTO|UPDATE|JOIN)\s*)?\<(([a-zA-Z0-9_]+)(\.[a-zA-Z0-9_]+)?)\>(.*?\2)?/i',
            function ($matches) {
                if (!empty($matches[2]) && isset($matches[8])) {
                    return $matches[0];
                }

                if (!empty($matches[4])) {
                    return $matches[1] . $matches[4] . ' ' . $this->tableQuote($matches[5]);
                }

                return $matches[1] . $this->columnQuote($matches[5]);
            },
            $raw->value);

        $raw_map = $raw->map;

        if (!empty($raw_map)) {
            foreach ($raw_map as $key => $value) {
                $map[$key] = $this->typeMap($value, gettype($value));
            }
        }

        return $query;
    }

    protected function innerConjunct($data, $map, $conjunctor, $outer_conjunctor)
    {
        $stack = [];

        foreach ($data as $value) {
            $stack[] = '(' . $this->dataImplode($value, $map, $conjunctor) . ')';
        }

        return implode($outer_conjunctor . ' ', $stack);
    }

    protected function dataImplode($data, &$map, $conjunctor, $table)
    {
        $stack = [];
        foreach ($data as $key => $value) {
            $type = gettype($value);

            if (
                $type === 'array' &&
                preg_match("/^(AND|OR)(\s+#.*)?$/", $key, $relation_match)
            ) {
                $relationship = $relation_match[1];

                $stack[] = $value !== array_keys(array_keys($value)) ?
                    '(' . $this->dataImplode($value, $map, ' ' . $relationship, $table) . ')' :
                    '(' . $this->innerConjunct($value, $map, ' ' . $relationship, $conjunctor) . ')';

                continue;
            }
            $key = $this->aliasTable[$table] . '.' . $key;
            $map_key = $this->mapKey($key);
            if (
                is_int($key) &&
                preg_match('/([a-zA-Z0-9_\.]+)\[(?<operator>\>\=?|\<\=?|\!?\=)\]([a-zA-Z0-9_\.]+)/i', $value, $match)
            ) {
                $stack[] = $this->columnQuote($match[1]) . ' ' . $match['operator'] . ' ' . $this->columnQuote($match[3]);
            } else {
                preg_match('/([a-zA-Z0-9_\.]+)(\[(?<operator>\>\=?|\<\=?|\!|\<\>|\>\<|\!?~|REGEXP)\])?/i', $key, $match);
                $column = $this->columnQuote($match[1]);

                if (isset($match['operator'])) {
                    $operator = $match['operator'];

                    if (in_array($operator, ['>', '>=', '<', '<='])) {
                        $condition = $column . ' ' . $operator . ' ';

                        if (is_numeric($value)) {
                            $condition .= $map_key;
                            $map[$map_key] = [$value, is_float($value) ? \PDO::PARAM_STR : \PDO::PARAM_INT];
                        } elseif ($raw = $this->buildRaw($value, $map)) {
                            $condition .= $raw;
                        } else {
                            $condition .= $map_key;
                            $map[$map_key] = [$value, \PDO::PARAM_STR];
                        }

                        $stack[] = $condition;
                    } elseif ($operator === '!') {
                        switch ($type) {
                            case 'NULL':
                                $stack[] = $column . ' IS NOT NULL';
                                break;
                            case 'array':
                                $placeholders = [];

                                foreach ($value as $index => $item) {
                                    $stack_key = $map_key . $index . '_i';

                                    $placeholders[] = $stack_key;
                                    $map[$stack_key] = $this->typeMap($item, gettype($item));
                                }

                                $stack[] = $column . ' NOT IN (' . implode(', ', $placeholders) . ')';
                                break;
                            case 'object':
                                if ($raw = $this->buildRaw($value, $map)) {
                                    $stack[] = $column . ' != ' . $raw;
                                }
                                break;
                            case 'integer':
                            case 'double':
                            case 'boolean':
                            case 'string':
                                $stack[] = $column . ' != ' . $map_key;
                                $map[$map_key] = $this->typeMap($value, $type);
                                break;
                        }
                    } elseif ($operator === '~' || $operator === '!~') {
                        if ($type !== 'array') {
                            $value = [$value];
                        }

                        $connector = ' OR ';
                        $data = array_values($value);

                        if (is_array($data[0])) {
                            if (isset($value['AND']) || isset($value['OR'])) {
                                $connector = ' ' . array_keys($value)[0] . ' ';
                                $value = $data[0];
                            }
                        }

                        $like_clauses = [];

                        foreach ($value as $index => $item) {
                            $item = strval($item);

                            if (!preg_match('/(\[.+\]|[\*\?\!\%#^-_]|%.+|.+%)/', $item)) {
                                $item = '%' . $item . '%';
                            }

                            $like_clauses[] = $column . ($operator === '!~' ? ' NOT' : '') . ' LIKE ' . $map_key . 'L' . $index;
                            $map[$map_key . 'L' . $index] = [$item, \PDO::PARAM_STR];
                        }

                        $stack[] = '(' . implode($connector, $like_clauses) . ')';
                    } elseif ($operator === '<>' || $operator === '><') {
                        if ($type === 'array') {
                            if ($operator === '><') {
                                $column .= ' NOT';
                            }

                            $stack[] = '(' . $column . ' BETWEEN ' . $map_key . 'a AND ' . $map_key . 'b)';

                            $data_type = (is_numeric($value[0]) && is_numeric($value[1])) ? \PDO::PARAM_INT : \PDO::PARAM_STR;

                            $map[$map_key . 'a'] = [$value[0], $data_type];
                            $map[$map_key . 'b'] = [$value[1], $data_type];
                        }
                    } elseif ($operator === 'REGEXP') {
                        $stack[] = $column . ' REGEXP ' . $map_key;
                        $map[$map_key] = [$value, \PDO::PARAM_STR];
                    }
                } else {
                    switch ($type) {
                        case 'NULL':
                            $stack[] = $column . ' IS NULL';
                            break;

                        case 'array':
                            $placeholders = [];

                            foreach ($value as $index => $item) {
                                $stack_key = $map_key . $index . '_i';

                                $placeholders[] = $stack_key;
                                $map[$stack_key] = $this->typeMap($item, gettype($item));
                            }

                            $stack[] = $column . ' IN (' . implode(', ', $placeholders) . ')';
                            break;

                        case 'object':
                            if ($raw = $this->buildRaw($value, $map)) {
                                $stack[] = $column . ' = ' . $raw;
                            }
                            break;

                        case 'integer':
                        case 'double':
                        case 'boolean':
                        case 'string':
                            $stack[] = $column . ' = ' . $map_key;
                            $map[$map_key] = $this->typeMap($value, $type);
                            break;
                    }
                }
            }
        }
        return implode($conjunctor . ' ', $stack);
    }

    protected function typeMap($value, $type)
    {
        $map = [
            'NULL' => \PDO::PARAM_NULL,
            'integer' => \PDO::PARAM_INT,
            'double' => \PDO::PARAM_STR,
            'boolean' => \PDO::PARAM_BOOL,
            'string' => \PDO::PARAM_STR,
            'object' => \PDO::PARAM_STR,
            'resource' => \PDO::PARAM_LOB
        ];

        if ($type === 'boolean') {
            $value = ($value ? '1' : '0');
        } elseif ($type === 'NULL') {
            $value = null;
        }

        return [$value, $map[$type]];
    }


}