<?php

namespace Yng\Session\Handlers;

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use Psr\SimpleCache\CacheInterface;

/**
 * @class   Cache
 * @author  Yng
 * @date    2022/04/23
 * @time    18:10
 * @package Yng\Session\Handlers
 */
class Cache implements \SessionHandlerInterface
{
    /**
     * @var \Yng\Cache\Cache
     */
    protected $handler;

    /**
     * @var array|int[]
     */
    protected array $options = [
        'ttl' => 3600
    ];

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_replace_recursive($this->options, $options);
        $this->handler = make(CacheInterface::class);
    }

    /**
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * @param $id
     *
     * @return bool|void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function destroy($id)
    {
        $this->handler->delete($id);
    }


    /**
     * @param $YngLifeTime
     *
     * @return bool
     */
    public function gc($YngLifeTime)
    {
        return true;
    }

    /**
     * @param $path
     * @param $name
     *
     * @return bool
     */
    public function open($path, $name)
    {
        return true;
    }

    /**
     * @param $id
     *
     * @return array|false|mixed|string
     */
    public function read($id)
    {
        return $this->handler->get($id, []) ?: [];
    }

    /**
     * @param $id
     * @param $data
     *
     * @return bool|void
     */
    public function write($id, $data)
    {
        $this->handler->set($id, $data, $this->options['ttl']);
    }
}
