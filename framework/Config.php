<?php

namespace Framework;

/**
 * Class Config
 *
 * @package App
 */
class Config
{
    /**
     * @var self 自身对象
     */
    private static $_instance = null;

    /**
     * @var array 所有的配置文件内容集合
     */
    private $configs = [];

    /**
     * @var array 配置文件列表
     */
    protected $config_files = null;

    /**
     * Config constructor.
     */
    protected function __construct()
    {
    }

    /**
     * 获取初始化的对象
     */
    public static function getInstance(): self
    {
        if (!static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    /**
     * 解析 Key
     *
     * @param string|null $name
     * @return array
     */
    public function parseKey(string $name = null): array
    {
        if (empty($name)) {
            return [
                'file' => null,
                'key'  => null
            ];
        }

        $info = explode('.', $name);

        return [
            'file' => $info['0'] ?? 'app',
            'key'  => ($info['1'] ?? null) ?: null
        ];
    }

    /**
     * 获取所有的配置文件
     *
     * @param string|null $name
     * @return array|string
     */
    public function getConfigFiles(string $name = null)
    {
        if (is_null($this->config_files)) {
            $files = glob(sprintf('%s/*.php', CONFIG_PATH));

            $this->config_files = [];
            foreach ($files as $file) {
                $file_name                      = basename($file, '.php');
                $this->config_files[$file_name] = $file;
            }
        }

        return is_null($name) ? $this->config_files : ($this->config_files[$name] ?? null);
    }

    /**
     * 加载单个文件的配置
     *
     * @param string $name
     * @return array
     */
    public function loadConfigFile(string $name): array
    {
        if (!isset($this->configs[$name])) {
            $file = $this->getConfigFiles($name);
            $this->configs[$name] = require_once $file;

            // 加载完毕就从文件数组中删除
            unset($this->config_files[$name]);
        }

        return $this->configs[$name] ?? [];
    }

    /**
     * 取出配置内容
     *
     * @param string|null $name
     * @param string|null $key
     * @return mixed|string|integer|array|null
     */
    public function getConfig(string $name = null, string $key = null)
    {
        // 获取所有的配置文件
        if (is_null($name)) {
            if (!empty($this->config_files)) {
                $files = $this->getConfigFiles();
                foreach ($files as $name => $file) {
                    if (isset($this->configs[$file])) {
                        $this->loadConfigFile($name);
                    }
                }
            }

            // 获取所有配置时 指定 key 不生效
            return $this->configs;
        }

        // 加载单个配置文件
        $config = $this->loadConfigFile($name);

        return is_null($key) ? $config : ($config[$key] ?? null);
    }

    /**
     * 读取配置文件
     *
     * @param string|null $name
     * @param null        $default
     * @return void|array|string|integer|null
     */
    public static function get(string $name = null, $default = null)
    {
        $info = static::getInstance()->parseKey($name);

        return static::getInstance()->getConfig($info['file'], $info['key']) ?? $default;
    }

    /**
     * 动态调用
     *
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \ErrorException
     */
    public static function __callStatic($name, $arguments)
    {
        $self = static::getInstance();

        if (!method_exists($self, $name)) {
            throw new \ErrorException(
                sprintf('not found %s in %s class', $name, self::class)
            );
        }

        return call_user_func_array([$self, $name], $arguments);
    }
}
