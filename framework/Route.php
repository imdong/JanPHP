<?php


namespace Framework;

/**
 * 路由实现
 *
 * @package Framework
 */
class Route
{
    /**
     * @var self 自身对象
     */
    private static $_instance = null;

    /**
     * @var array 最终的路由实现
     */
    private $routes = [];

    /**
     * @var string[] 允许使用的 Method
     */
    protected $allow_method = ['GET', 'POST', 'ALL'];

    /**
     * @var string[] 一些预定义的规则
     */
    protected $param_rules = [
        'id' => '[0-9]+'
    ];

    /**
     * @var null 请求方式
     */
    private $method = null;

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
     * 解析 Url 规则
     *
     * @param string $uri
     * @return array|null
     */
    public static function parseUrlToRule(string $uri): ?array
    {
        $self = static::getInstance();

        $rule = null;
        foreach ($self->routes as $item) {
            if (
                (self::method($item['method']) || $item['method'] == 'ALL')
                && preg_match(sprintf('#^%s$#', $item['url_rule']), $uri, $matchs)
            ) {
                $args = [];
                foreach ($matchs as $key => $value) {
                    if(is_string($key)) {
                        $args[$key] = $value;
                    }
                }
                $item['args'] = $args;

                if ($item['method'] != 'ALL') {
                    $rule = $item;
                    break;
                } else if (is_null($rule)) {
                    $rule = $item;
                }
            }
        }

        return $rule;
    }

    /**
     * 解析路由描述
     *
     * @param string $uri
     * @param string $rule
     */
    public function set(string $uri, string $rule)
    {
        // 解析路由规则
        $info = explode('#', $rule);
        if (!isset($info['1'])) {
            $info['1'] = $info['0'];
            $info['0'] = 'ALL';
        }

        $to      = explode('@', $info['1']);
        $methods = explode(',', $info['0']);

        foreach ($methods as $method) {
            $this->addRoute($method, $uri, $to['0'], $to['1'] ?? 'index');
        }
    }

    /**
     * 生成最终的路由
     *
     * @param string     $method
     * @param string     $uri
     * @param string     $controller
     * @param string     $action
     * @param array|null $params
     * @return array|null
     */
    protected function addRoute(string $method, string $uri, string $controller, string $action, array $params = null): ?array
    {
        // 过滤 Method
        $method = strtoupper($method);
        if (!in_array($method, $this->allow_method)) {
            return null;
        }

        // 过滤 Controller
        if (strpos($controller, '\\') === false) {
            $controller = sprintf('App\Controller\%s', $controller);
        }

        // 生成 Uri 规则
        $callable = function (array $matches) use ($params) {
            return sprintf(
                '(?:%s(?<%s>%s))%s',
                $matches['prefix'] ?: '',
                $matches['name'],
                $params[$matches['name']] ?? '.+',
                $matches['required']
            );
        };
        $uri      = preg_replace_callback('#(?<prefix>.)?{(?<name>[a-z0-9_]+)(?<required>\??)}#', $callable, $uri);

        // 保存规则
        $rule           = [
            'url_rule'   => $uri,
            'method'     => $method,
            'controller' => $controller,
            'action'     => $action
        ];
        $this->routes[] = $rule;

        return $rule;
    }

    /**
     * GET 路由
     *
     * @param string     $uri
     * @param string     $controller
     * @param string     $action
     * @param array|null $params
     * @return array|null
     */
    public static function get(string $uri, string $controller, string $action, array $params = null): ?array
    {
        return static::getInstance()->addRoute('GET', $uri, $controller, $action, $params);
    }

    /**
     * POST 路由
     *
     * @param string     $uri
     * @param string     $controller
     * @param string     $action
     * @param array|null $params
     * @return array|null
     */
    public static function POST(string $uri, string $controller, string $action, array $params = null): ?array
    {
        return static::getInstance()->addRoute('GET', $uri, $controller, $action, $params);
    }

    /**
     * 返回当前的请求方式
     *
     * @param null $methods
     * @return string|bool
     */
    public static function method($methods = null)
    {
        if (is_null(static::getInstance()->method)) {
            static::getInstance()->method = $_SERVER['REQUEST_METHOD'];
        }

        $method = static::getInstance()->method;
        if (is_null($methods)) {
            return $method;
        }

        return in_array($method, is_string($methods) ? [$methods] : $methods);
    }
}
