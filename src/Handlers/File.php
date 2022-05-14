<?php

namespace Yng\Session\Handlers;

/**
 * @class   File
 * @author  Yng
 * @date    2022/04/23
 * @time    18:10
 * @package Yng\Session\Handlers
 */
class File implements \SessionHandlerInterface
{
    /**
     * @var array
     */
    protected array $options = [
        'path'           => '/tmp',
        'gc_divisor'     => 100,
        'gc_probability' => 1,
        'gc_maxlifetime' => 1440,
    ];

    /**
     * @param array $options
     *
     * @throws \Exception
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
        try {
            !is_dir($this->options['path']) && mkdir($this->options['path'], 0755, true);
        } finally {
            // 垃圾回收
            if (random_int(1, $this->options['gc_divisor']) <= $this->options['gc_probability']) {
                $this->gc($this->options['gc_maxlifetime']);
            }
        }
    }

    /**
     * @param int $maxLifeTime
     *
     * @return false|int|void
     */
    public function gc($maxLifeTime)
    {
        $now   = time();
        $files = $this->findFiles($this->options['path'], function(\SplFileInfo $item) use ($maxLifeTime, $now) {
            return $now - $maxLifeTime > $item->getMTime();
        });

        foreach ($files as $file) {
            $this->unlink($file->getPathname());
        }
    }

    /**
     * 查找文件
     *
     * @param string   $root
     * @param \Closure $filter
     *
     * @return Generator
     */
    protected function findFiles(string $root, \Closure $filter)
    {
        $items = new \FilesystemIterator($root);

        /** @var \SplFileInfo $item */
        foreach ($items as $item) {
            if ($item->isDir() && !$item->isLink()) {
                yield from $this->findFiles($item->getPathname(), $filter);
            } else {
                if ($filter($item)) {
                    yield $item;
                }
            }
        }
    }

    /**
     * 删除Session
     *
     * @access public
     *
     * @param string $id
     *
     * @return bool
     */
    public function delete(string $id): bool
    {
        try {
            return $this->unlink($this->getSessionFile($id));
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 判断文件是否存在后，删除
     *
     * @access private
     *
     * @param string $file
     *
     * @return bool
     */
    private function unlink(string $file): bool
    {
        return is_file($file) && unlink($file);
    }

    /**
     * @param $id
     *
     * @return string
     */
    protected function getSessionFile($id)
    {
        return rtrim($this->options['path'], '/\/') . '/sess_' . $id;
    }

    /**
     * @param string $id
     *
     * @return false|string
     */
    public function read($id)
    {
        $sessionFile = $this->getSessionFile($id);
        if (\file_exists($sessionFile)) {
            return \file_get_contents($sessionFile) ?: '';
        }

        return '';
    }

    /**
     * @param string $id
     * @param string $data
     *
     * @return bool|void
     */
    public function write($id, $data)
    {
        \file_put_contents($this->getSessionFile($id), $data, LOCK_EX);
    }

    /**
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * @param string $id
     *
     * @return bool|void
     */
    public function destroy($id)
    {
        $this->unlink($this->getSessionFile($id));
    }

    /**
     * @param string $path
     * @param string $name
     *
     * @return bool
     */
    public function open($path, $name)
    {
        return true;
    }
}
