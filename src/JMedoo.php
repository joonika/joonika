<?php

namespace Joonika;

use Medoo;
use PDO;
use PDOStatement;


class JMedoo extends Medoo\Medoo
{
    public $cache = false;
    public $q = false;
    public $schema = false;

    public function tableQuote(string $table): string
    {
        if (!preg_match('/^[a-zA-Z0-9_.]+$/i', $table)) {
            throw new InvalidArgumentException("Incorrect table name \"$table\"");
        }
        $websiteDbConfig = JK_WEBSITE()['database'];
        $schema=!empty($websiteDbConfig['db'])?$websiteDbConfig['db']:false;
        if (stripos($table, '.') !== false) {
            $schemaExplode = explode('.', $table);
            $schema = '';
            if (!empty($websiteDbConfig['other'][$schemaExplode[0]])) {
                $schemaF = $websiteDbConfig['other'][$schemaExplode[0]];
                if (is_array($schemaF) && !empty($schemaF['db'])) {
                    $schema = $schemaF['db'];
                } elseif (is_string($schemaF)) {
                    $schema = $schemaF;
                }
            } else {
                $table = $websiteDbConfig['db'] . '.' . $schemaExplode[1];
            }
            if (!empty($schema)) {
                $table = $schema . '.' . $schemaExplode[1];
            }
        }
        $this->schema = !empty($schema) ? $schema : false;
        return $this->prefix . $table;
    }

    public function queryAndFetch($query, $map = [])
    {
        $raw = $this->raw($query, $map);

        $query = $this->buildRaw($raw, $map);
        return $this->exec($query, $map)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cache()
    {
        $this->cache = true;
        return $this;
    }

    public function str_replace_first($search, $replace, $subject)
    {
        $pos = strpos($subject, $search);
        if ($pos !== false) {
            return substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $subject;
    }

    /**
     * Execute the raw statement.
     *
     * @param string $statement The SQL statement.
     * @param array $map The array of input parameters value for prepared statement.
     * @codeCoverageIgnore
     * @return \PDOStatement|null
     */
    public function exec(string $statement, array $map = [], callable $callback = null): ?PDOStatement
    {
        $durationStart=microtime(true);
        if(!empty($this->schema)){
            if(!empty(Database::$instanceDuration[$this->schema])){
                Database::$instanceDuration[$this->schema]=[];
            }
        }

        $this->statement = null;
        $this->errorInfo = null;
        $this->error = null;
        if ($this->testMode) {
            $this->queryString = $this->generate($statement, $map);
            return null;
        }

        if ($this->q) {
            $this->q = false;
            return $this->generate($statement, $map);
        }

        if ($this->cache) {
            if (substr($statement, 0, strlen('SELECT ') == strlen("SELECT "))) {
                $statement = $this->str_replace_first("SELECT ", "SELECT SQL_CACHE ", $statement);
            }
            $this->cache = false;
        }

        if ($this->debugMode) {
            if ($this->debugLogging) {
                $this->debugLogs[] = $this->generate($statement, $map);
                return null;
            }

            echo $this->generate($statement, $map);

            $this->debugMode = false;

            return null;
        }

        if ($this->logging) {
            $this->logs[] = [$statement, $map];
        } else {
            $this->logs = [[$statement, $map]];
        }


        $statement = $this->pdo->prepare($statement);
        $errorInfo = $this->pdo->errorInfo();

        if ($errorInfo[0] !== '00000') {
            $this->errorInfo = $errorInfo;
            $this->error = $errorInfo[2];

            return null;
        }

        foreach ($map as $key => $value) {
            $statement->bindValue($key, $value[0], $value[1]);
        }

        if (is_callable($callback)) {
            $this->pdo->beginTransaction();
            $callback($statement);
            $execute = $statement->execute();
            $this->pdo->commit();
        } else {
            $execute = $statement->execute();
        }

        $errorInfo = $statement->errorInfo();

        if ($errorInfo[0] !== '00000') {
            $this->errorInfo = $errorInfo;
            $this->error = $errorInfo[2];

            return null;
        }

        if ($execute) {
            if(!empty($this->schema)){
                $duration=microtime(true)-$durationStart;
                Database::$instanceDuration[$this->schema][]=round($duration,6);
            }
            $this->statement = $statement;
        }

        return $statement;
    }


    public function q()
    {
        $this->q = true;
        return $this;
    }

    protected function selectContext(
        string $table,
        array &$map,
        $join,
        &$columns = null,
        array $where = null,
        $columnFn = null
    ): string
    {
        preg_match('/(?<table>[a-zA-Z0-9_.]+)\s*\((?<alias>[a-zA-Z0-9_]+)\)/i', $table, $table_match);
//        dd($table_match);
        if (isset($table_match['table'], $table_match['alias'])) {
            $table = $this->tableQuote($table_match['table']);

            $tableQuery = $table . ' AS ' . $this->tableQuote($table_match['alias']);
        } else {
            $table = $this->tableQuote($table);

            $tableQuery = $table;
        }


        $isJoin = $this->isJoin($join);

        if ($isJoin) {
            $tableQuery .= ' ' . $this->buildJoin($tableAlias ?? $table, $join, $map);
        } else {
            if (is_null($columns)) {
                if (
                    !is_null($where) ||
                    (is_array($join) && isset($columnFn))
                ) {
                    $where = $join;
                    $columns = null;
                } else {
                    $where = null;
                    $columns = $join;
                }
            } else {
                $where = $columns;
                $columns = $join;
            }
        }

        if (isset($columnFn)) {
            if ($columnFn === 1) {
                $column = '1';

                if (is_null($where)) {
                    $where = $columns;
                }
            } elseif ($raw = $this->buildRaw($columnFn, $map)) {
                $column = $raw;
            } else {
                if (empty($columns) || $this->isRaw($columns)) {
                    $columns = '*';
                    $where = $join;
                }

                $column = $columnFn . '(' . $this->columnPush($columns, $map, true) . ')';
            }
        } else {
            $column = $this->columnPush($columns, $map, true, $isJoin);
        }

        return 'SELECT ' . $column . ' FROM ' . $tableQuery . $this->whereClause($where, $map);
    }

    protected function buildJoin(string $table, array $join, array &$map): string
    {
        $tableJoin = [];
        $type = [
            '>' => 'LEFT',
            '<' => 'RIGHT',
            '<>' => 'FULL',
            '><' => 'INNER'
        ];

        foreach ($join as $subtable => $relation) {
            preg_match('/(\[(?<join>\<\>?|\>\<?)\])?(?<table>[a-zA-Z0-9_.]+)\s?(\((?<alias>[a-zA-Z0-9_]+)\))?/', $subtable, $match);

            if ($match['join'] === '' || $match['table'] === '') {
                continue;
            }

            if (is_string($relation)) {
                $relation = 'USING ("' . $relation . '")';
            } elseif (is_array($relation)) {
                // For ['column1', 'column2']
                if (isset($relation[0])) {
                    $relation = 'USING ("' . implode('", "', $relation) . '")';
                } else {
                    $joins = [];

                    foreach ($relation as $key => $value) {
                        if ($key === 'AND' && is_array($value)) {
                            $joins[] = $this->dataImplode($value, $map, ' AND');
                            continue;
                        }

                        $joins[] = (
                            strpos($key, '.') > 0 ?
                                // For ['tableB.column' => 'column']
                                $this->columnQuote($key) :

                                // For ['column1' => 'column2']
                                $table . '.' . $this->columnQuote($key)
                            ) .
                            ' = ' .
                            $this->tableQuote($match['alias'] ?? $match['table']) . '.' . $this->columnQuote($value);
                    }

                    $relation = 'ON ' . implode(' AND ', $joins);
                }
            } elseif ($raw = $this->buildRaw($relation, $map)) {
                $relation = $raw;
            }

            $tableName = $this->tableQuote($match['table']);

            if (isset($match['alias'])) {
                $tableName .= ' AS ' . $this->tableQuote($match['alias']);
            }

            $tableJoin[] = $type[$match['join']] . " JOIN ${tableName} ${relation}";
        }

        return implode(' ', $tableJoin);
    }


}