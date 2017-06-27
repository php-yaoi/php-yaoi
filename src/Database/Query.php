<?php
namespace Yaoi\Database;

use Yaoi\Database;
use Yaoi\DependencyRepository;
use Yaoi\Log;
use Yaoi\Sql\Expression;
use Yaoi\Sql\SimpleExpression;

/**
 * Class Database_Sql_Generic_Query
 *
 * db literal query
 * |sql statement
 *  |sql expression
 * |result
 * |db client
 */
class Query implements \Iterator
{
    /**
     * @var SimpleExpression
     */
    private $expression;

    /**
     * @var Driver
     */
    private $driver;

    public function __construct(Expression $expression, Driver $driver)
    {
        $this->expression = $expression;
        $this->driver = $driver;
    }


    /**
     * @return string
     * @throws Exception
     */
    public function build()
    {
        return $this->expression->build($this->driver);
    }

    protected $executed = false;
    protected $result;

    public function execute()
    {
        $query = $this->build();
        $start = microtime(1);

        $this->executed = true;
        if (!$this->result = $this->driver->query($query)) {
            $error = $this->driver->queryErrorMessage($this->result);
            if (null !== $this->logResourceId) {
                /**
                 * @var Log $log
                 */
                $log = DependencyRepository::get($this->logResourceId);
                $log->push('(-1) ' . $query . "\n: " . $error . ' ' . $query, Log::TYPE_ERROR);
            }
            $exception = new Database\Exception($error, Database\Exception::QUERY_ERROR);
            $exception->query = $query;
            throw $exception;
        }

        if (null !== $this->logResourceId
            && DependencyRepository::get($this->logResourceId) !== null
        ) {
            /**
             * @var Log $log
             */
            $log = DependencyRepository::get($this->logResourceId);
            $log->push(round(microtime(1) - $start, 4) . ' s. (' . $this->rowsAffected() . ') ' . $query);
        }

        return $this;
    }

    public function fetchAll($keyField = null, $valueField = null)
    {
        if (!$this->executed) {
            $this->execute();
        }
        $this->driver->rewind($this->result);

        $result = array();

        if ($keyField instanceof Database\Definition\Column) {
            $keyField = $keyField->schemaName;
        }

        if ($valueField instanceof Database\Definition\Column) {
            $valueField = $valueField->schemaName;
        }


        if ($valueField) {
            if ($keyField !== null) {
                while ($r = $this->driver->fetchAssoc($this->result)) {
                    $result [$r[$keyField]] = $r[$valueField];
                }
            } else {
                while ($r = $this->driver->fetchAssoc($this->result)) {
                    $result [] = $r[$valueField];
                }
            }
        }
        elseif ($this->resultClass) {
            /** @var \Yaoi\Mappable\Contract $class */
            $class = $this->resultClass;

            if ($keyField !== null) {
                while ($r = $this->driver->fetchAssoc($this->result)) {
                    $result [$r[$keyField]] = $class::fromArray($r, null, true);
                }
            } else {
                while ($r = $this->driver->fetchAssoc($this->result)) {
                    $result [] = $class::fromArray($r, null, true);
                }
            }

        } else {
            if ($keyField !== null) {
                while ($r = $this->driver->fetchAssoc($this->result)) {
                    $result [$r[$keyField]] = $r;
                }
            } else {
                while ($r = $this->driver->fetchAssoc($this->result)) {
                    $result [] = $r;
                }
            }
        }

        return $result;
    }

    /**
     * @deprecated use fetchAll($keyField, $valueField)
     *
     * @param null $key
     * @param null $value
     * @return array
     */
    public function fetchColumns($key = null, $value = null)
    {
        $this->rewind();

        $result = array();

        if ($key instanceof Database\Definition\Column) {
            $key = $key->schemaName;
        }

        if ($value instanceof Database\Definition\Column) {
            $value = $value->schemaName;
        }

        if (null === $key || null === $value) {
            $r = $this->driver->fetchAssoc($this->result);
            $keys = array_keys($r);
            if (null === $key) {
                $key = $keys[0];
            }
            if (null === $value) {
                $value = $keys[1];
            }
            $result [$r[$key]] = $r[$value];
        }

        while ($r = $this->driver->fetchAssoc($this->result)) {
            $result [$r[$key]] = $r[$value];
        }
        return $result;
    }

    /**
     * @param null $field
     * @return array|object
     * @throws Exception
     */
    public function fetchRow($field = null)
    {
        if (!$this->executed) {
            $this->execute();
        }
        $result = $this->driver->fetchAssoc($this->result);
        if (false === $result || null === $result) {
            return null;
        }
        if ($field) {
            return $result[$field];
        } else {
            if ($this->resultClass) {
                /** @var Entity $class */
                $class = $this->resultClass;
                $result = $class::fromArray($result, null, true);
            }
            return $result;
        }
    }


    protected $current;
    protected $position = -1;
    protected $valid;


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->current = $this->driver->fetchAssoc($this->result);

        if (null === $this->current) {
            $this->valid = false;
            $this->position = -1;
        } else {
            $this->valid = true;
            if ($this->resultClass) {
                /** @var Contract $class */
                $class = $this->resultClass;
                $this->current = $class::fromArray($this->current, null, true);
            }

            ++$this->position;
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->position > 0 ? $this->position : 0;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->valid;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        if (!$this->executed) {
            $this->execute();
        }

        $this->driver->rewind($this->result);
        $this->position = -1;
        $this->next();
    }

    protected $skipAutoExecute = 0;

    public function skipAutoExecute($true = 1)
    {
        $this->skipAutoExecute = $true;
        return $this;
    }

    public function __destruct()
    {
        if (!$this->executed && !$this->skipAutoExecute) {
            $this->execute();
        }
    }

    public function lastInsertId()
    {
        if (!$this->executed) {
            $this->execute();
        }

        return $this->driver->lastInsertId($this->result);
    }

    public function lastInsertIdIn(&$var)
    {
        $var = $this->lastInsertId();
        return $this;
    }

    public function rowsAffected()
    {
        if (!$this->executed) {
            $this->execute();
        }
        return $this->driver->rowsAffected($this->result);
    }

    public function rowsAffectedIn(&$var)
    {
        $var = $this->rowsAffected();
        return $this;
    }


    private $logResourceId;

    public function log(Log $log = null)
    {
        $this->logResourceId = \Yaoi\DependencyRepository::add($log);
        return $this;
    }


    protected $resultClass;

    public function bindResultClass($resultClass = null)
    {
        $this->resultClass = $resultClass;
        return $this;
    }


}
