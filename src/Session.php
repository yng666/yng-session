<?php
declare(strict_types=1);

namespace Yng\Session;

use Yng\Utils\Arr;

/**
 * @class   Session
 * @author  Yng
 * @date    2022/04/30
 * @time    20:59
 * @package Yng\Session
 */
class Session
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var \SessionHandlerInterface
     */
    protected \SessionHandlerInterface $handler;

    /**
     * @var array
     */
    protected array $options = [
        'class'   => 'Yng\Session\Handlers\File',
        'options' => ['path' => '/tmp', 'ttl' => 1440,],
    ];

    /**
     * @param array $config
     */
    public function __construct(array $options)
    {
        $this->options = array_replace_recursive($this->options, $options['handler']);
        $handler       = $this->options['class'];
        $options       = $this->options['options'];
        $this->handler = new $handler($options);
    }

    /**
     * @return void
     */
    public function initialize()
    {
        $data = $this->handler->read($this->id);
        if (is_string($data)) {
            $data = \unserialize($data) ?: [];
        }

        $this->data = (array)$data;
    }

    /**
     * @return void
     */
    public function save()
    {
        $this->handler->write($this->id, \serialize($this->data));
    }

    /**
     * @return \SessionHandlerInterface|mixed
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key)
    {
        return isset($this->data[$key]);
    }

    /**
     * @param string $key
     * @param        $default
     *
     * @return array|\ArrayAccess|mixed
     */
    public function get(string $key, $default = null)
    {
        return Arr::get($this->data, $key, $default);
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return array
     */
    public function set(string $key, $value)
    {
        return Arr::set($this->data, $key, $value);
    }

    /**
     * @param string $key
     * @param        $default
     *
     * @return array|\ArrayAccess|mixed
     */
    public function pull(string $key, $default = null)
    {
        return Arr::pull($this->data, $key, $default);
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function remove(string $key)
    {
        return Arr::forget($this->data, $key);
    }

    /**
     * @return void
     */
    public function destroy()
    {
        $this->data = [];
        $this->handler->destroy($this->id);
    }

    /**
     * @param \SessionHandlerInterface $handler
     *
     * @return void
     */
    public function setHandler(\SessionHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param $method
     * @param $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->handler->{$method}(...$args);
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        if (isset($this->id)) {
            return $this->id;
        }
        return $this->refreshId();
    }

    /**
     * @return string
     */
    public function refreshId()
    {
        return $this->id = md5(microtime(true) . session_create_id());
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }
}
