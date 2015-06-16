<?php

namespace Yaoi;

use Closure;
use Yaoi\Mock\Exception;
use Yaoi\Storage;
use Yaoi\Storage\PhpVar;
use Yaoi\Storage\Null;

class Mock
{
    const MODE_COMBINED = 0;
    const MODE_PLAY = 1;
    const MODE_CAPTURE = 2;

    public $mode = self::MODE_COMBINED;

    /**
     * @param null $key
     * @param callable $addOnMiss
     * @return mixed
     * @throws Mock\Exception
     */
    public function get($key = null, Closure $addOnMiss = null)
    {
        if (null === $addOnMiss && $this->mode === self::MODE_CAPTURE) {
            throw new Mock\Exception('Reading disabled in capture mode', Mock\Exception::PLAY_REQUIRED);
        }

        if (null === $key) {
            $key = $this->sequenceId++;
        }

        $fullKey = $key;

        if ($this->branchKey) {
            $fullKey = array_merge($this->branchKey, array($key));
        }

        if ($this->mode === Mock::MODE_CAPTURE) {
            $result = $addOnMiss();
            $this->add($result, $key);
        } else {
            $result = $this->storage->get($fullKey);
            if ((null === $result) && !$this->storage->keyExists($fullKey)) {
                if (null === $addOnMiss || $this->mode === self::MODE_PLAY) {
                    throw new Mock\Exception('Record not found: ' . print_r($fullKey, 1), Mock\Exception::KEY_NOT_FOUND);
                } else {
                    $result = $addOnMiss();
                    $this->add($result, $key);
                }
            }
        }

        return $result;
    }

    /**
     * @param $value
     * @param null $key
     * @return $this
     * @throws Exception
     */
    public function add($value, $key = null)
    {
        if ($this->mode === self::MODE_PLAY) {
            throw new Mock\Exception('Writing disabled in play mode', Mock\Exception::CAPTURE_REQUIRED);
        }

        if (null === $key) {
            $key = $this->sequenceId++;
        }

        if ($this->branchKey) {
            $key = array_merge($this->branchKey, array($key));
        }

        $this->storage->set($key, $value);
        return $this;
    }


    protected $temp = array();

    /**
     * Store or retrieve temporary data (or non-storable resources) relevant to current mock/branch
     *
     * @param $key
     * @param null $value
     * @return mixed|null
     */
    public function temp($key, $value = null)
    {
        if (null === $value) {
            if (isset($this->temp[$key])) {
                return $this->temp[$key];
            } else {
                return null;
            }
        } else {
            $this->temp[$key] = $value;
            return $value;
        }
    }

    protected $sequenceId = 0;
    /**
     * @var Storage
     */
    protected $branches;

    /**
     * @var Storage
     */
    protected $storage;

    protected $branchKey = array();

    public function __construct(Storage $storage, $mode = self::MODE_COMBINED)
    {
        $this->storage = $storage;
    }

    public $isEmptyBranch;

    /**
     * @return static
     */
    public function branch()
    {
        $key = func_get_args();
        if (!$key) {
            return $this;
        }

        if (null === $this->branches) {
            $this->branches = new PhpVar();
        }

        if (!$mock = $this->branches->get($key)) {
            $mock = new static($this->storage);
            $mock->mode = $this->mode;
            $mock->isEmptyBranch = true;
            $mock->branchKey = array_merge($this->branchKey, $key);
            $this->branches->set($key, $mock);
        }

        return $mock;
    }

    private static $nullMock;

    /**
     * @return Mock
     */
    public static function getNull()
    {
        if (null === self::$nullMock) {
            self::$nullMock = new self(new Null());
        }
        return self::$nullMock;
    }


}