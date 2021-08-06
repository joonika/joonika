<?php


namespace Joonika\Session;


use Joonika\FS;

class Store
{
    /**
     * Expands a dot notation array into a full multi-dimensional array.
     *
     * @param array $dotNotationArray
     *
     * @return array
     */
    public function undot($key, $value)
    {
        $array = $_SESSION;
        $this->set($array, $key, $value);
        $_SESSION = $array;
        return $_SESSION;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     *
     * @return array
     */
    public function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;
        return $array;
    }

    protected function exists($array, $key)
    {
        if ($array instanceof \ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * Determine whether the given value is array accessible.
     *
     * @param mixed $value
     * @return bool
     */
    protected function accessible($value)
    {
        return is_array($value) || $value instanceof \ArrayAccess;
    }

    public function get($array, $key, $default = null)
    {
        if (!static::accessible($array)) {
            return value($default);
        }

        if (is_null($key)) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (strpos($key, '.') === false) {
            return $array[$key] ?? value($default);
        }

        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return value($default);
            }
        }

        return $array;
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param array $array
     * @param string $prepend
     *
     * @return array
     */
    public function dot($array, $prepend = '')
    {
        $results = [];
        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, static::dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    /**
     * Save the session data to storage.
     *
     * @return void
     */
    public function save()
    {
        $driver = !empty(JK_WEBSITE()['sessionDriver']) ? JK_WEBSITE()['sessionDriver'] : 'storage';
        $path = !empty(JK_WEBSITE()['sessionStoragePath']) ? JK_SITE_PATH() . JK_WEBSITE()['sessionStoragePath'] : JK_SITE_PATH() . 'storage/session';
        if (!FS::isDir($path)) {
            mkdir($path, 0777);
        }
        if ($driver == 'storage') {
            $file = 'user_' . JK_LOGINID() . ".txt";
            $content = 'At time ' . now() . " : " . PHP_EOL . "\t";
            $serilize = json_encode($_SESSION, JSON_UNESCAPED_UNICODE);
            $content .= $serilize . PHP_EOL;
            FS::fileWrite($path . DS() . $file, $content, 'a+');
        }
    }

    /**
     * Get a value from the array, and remove it.
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function pull(&$array, $key, $default = null)
    {
        $value = $this->get($_SESSION, $key, $default);

        $this->forget($array, $key);

        return $value;
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param array $array
     * @param array|string $keys
     * @return void
     */
    public function forget(&$array, $keys)
    {
        $original = &$array;

        $keys = (array)$keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if ($this->exists($array, $key)) {
                unset($array[$key]);

                continue;
            }

            $parts = explode('.', $key);

            // clean up before each pass
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }


    /**
     * Filter the array using the given callback.
     *
     * @param array $array
     * @param callable $callback
     * @return array
     */
    public function where(callable $callback)
    {
        return array_filter($_SESSION, $callback, ARRAY_FILTER_USE_BOTH);
    }


    /**
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param \ArrayAccess|array $array
     * @param string|array $keys
     * @return bool
     */
    public function has($array, $keys)
    {
        $keys = (array)$keys;

        if (!$array || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (static::exists($array, $key)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (static::accessible($subKeyArray) && static::exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }


}