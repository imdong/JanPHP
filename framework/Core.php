<?php

namespace Framework;

/**
 * 核心逻辑
 *
 * @package Framework
 */
class Core
{
    /**
     * @var self 自身对象
     */
    private static $_instance = null;

    /**
     * @var array 所有的配置文件
     */
    private $configs = [];

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
     * 初始化框架
     */
    public function init()
    {
        // 初始化目录
        $this->initPath();

        // 初始化路由
        static::getInstance()->initRoute(Config::get('route.'));
    }

    /**
     * 启动框架
     *
     * @param string|null $path
     * @throws Exception
     * @throws \ReflectionException
     */
    public static function start(string $path = null)
    {
        // 初始化基础信息
        static::getInstance()->init();

        // 解析路由
        $route_rule = Route::parseUrlToRule($path);
        if (!$route_rule) {
            throw new Exception('Not Found', 404);
        }

        // 创建控制器
        if (!class_exists($route_rule['controller'])) {
            throw new Exception('Not Found Controller', 404);
        }

        /**
         * @var $controller Controller
         */
        $controller = new $route_rule['controller'];
        if (!method_exists($controller, $route_rule['action'])) {
            throw new Exception('Not Found action', 404);
        }

        // 获取参数列表
        // $class      = new \ReflectionClass($controller);
        // $parameters = $class->getMethod($route_rule['action'])->getParameters();

        $result = call_user_func_array([$controller, $route_rule['action']], $route_rule['args']);

        // 区分返回结果类型进行处理
        if ($result instanceof Controller) {
            $result->sendResponse();
        }else{
            $controller->sendResponse($result);
        }
    }

    /**
     * 初始化 Route
     *
     * @param array $routes
     */
    protected function initRoute(array $routes)
    {
        $route = Route::getInstance();

        // 遍历路由配置文件
        foreach ($routes as $uri => $rule) {
            $route->set($uri, $rule);
        }
    }

    /**
     * 初始化基本目录与常用变量
     */
    protected function initPath()
    {
        // 框架核心目录
        define('FRAMEWORK_PATH', __DIR__);

        // 项目根目录
        define('ROOT_PATH', dirname(FRAMEWORK_PATH));

        /**
         * 配置文件目录
         */
        define('CONFIG_PATH', ROOT_PATH . '/config');

        /**
         * 应用程序默认路径
         */
        define('APP_PATH', ROOT_PATH . '/app');
    }
}
