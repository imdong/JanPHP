<?php


namespace Framework;

use PDO;
use PDOStatement;

/**
 * 数据库操作类
 *
 * @package Framework
 */
class DB
{
    /**
     * @var self 自身对象
     */
    private static $_instance = null;

    /**
     * @var PDO 所有的配置文件内容集合
     */
    private $link = null;

    /**
     * Config constructor.
     */
    protected function __construct()
    {
    }

    /**
     * @var array 查询条件
     */
    protected $condition = [
        'sql_raw' => null,
        'method'  => 'SELECT',
        'from'    => null,
        'select'  => ['*'],
        'where'   => [],
        'keys'    => [],
        'values'  => [],
        'order'   => null,
        'limit'   => null,
        'offset'  => null,
        'params'  => [],
    ];

    /**
     * 连接到数据库
     *
     * @param PDO|null $db_link
     * @return PDO
     */
    protected function connection(PDO $db_link = null): PDO
    {
        if (is_null($db_link) && is_null($this->link)) {
            $config = Config::get('database');

            // 创建数据库连接
            $dsn     = sprintf('%s:host=%s;dbname=%s', $config['driver'], $config['hostname'], $config['db_name']);
            $db_link = new PDO($dsn, $config['username'], $config['password']);
        }

        return $this->link = $db_link;
    }

    /**
     * 获取初始化的对象
     */
    public static function getInstance(): self
    {
        if (!static::$_instance) {
            static::$_instance = new static();

            // 连接到数据库
            static::$_instance->connection();
        }

        return static::$_instance;
    }

    /**
     * 查询表
     *
     * @param string $table_name
     * @return DB
     */
    public function from(string $table_name): DB
    {
        $this->condition['from'] = $table_name;

        return $this;
    }

    /**
     * 设置为筛选查询
     *
     * @param string[] $keys
     * @return DB
     */
    public function select($keys = ['*']): DB
    {
        $this->condition['method'] = 'SELECT';

        $select = [];
        foreach ($keys as $as => $key) {
            if (is_numeric($as)) {
                $select[] = $key;
            } else {
                $select[] = sprintf('%s AS %s', $keys, $as);
            }
        }

        $this->condition['select'] = $select;

        return $this;
    }

    /**
     * 插入数据（单条 或 多条）
     *
     * @param array      $keys
     * @param array|null $values 忽略则以 $keys 对照插入单条记录
     * @return false|PDOStatement
     */
    public function insert(array $keys, array $values = null)
    {
        $this->condition['method'] = 'INSERT INTO';

        $value_list = [];
        $key_list   = [];
        if (is_null($values)) {
            foreach ($keys as $key => $value) {
                if (!is_numeric($key)) {
                    $key_list[]   = $key;
                    $value_list[] = $value;
                }
            }
            $value_list = [$value_list];
        } else {
            foreach ($keys as $key) {
                $key_list[] = $key;
            }

            foreach ($values as $value) {
                $value_items = [];
                foreach ($key_list as $key) {
                    $value_items[] = $value[$key] ?? null;
                }
                $value_list[] = $value_items;
            }
        }

        $this->condition['keys']   = $key_list;
        $this->condition['values'] = $value_list;

        // 直接执行吧
        return $this->exec();
    }

    /**
     * 删除记录
     *
     * @return DB
     */
    public function delete(): DB
    {
        $this->condition['method'] = 'DELETE';

        return $this;
    }

    /**
     * 更新记录
     *
     * @param array $set
     * @return DB
     */
    public function update(array $set): DB
    {
        $this->condition['method'] = 'UPDATE';
        $this->condition['values'] = $set;

        return $this;
    }

    /**
     * 排序
     *
     * @param string|array $by 排序对照
     * @param bool         $desc
     * @return DB
     */
    public function order($by, bool $desc = false): DB
    {
        if (is_string($by)) {
            $by = [
                $by => $desc ? 'asc' : 'desc'
            ];
        }

        $order = [];
        foreach ($by as $key => $value) {
            if (is_numeric($key)) {
                $order[$value] = $desc ? 'asc' : 'desc';
            } else {
                $order[$key] = is_bool($value) ? ($value ? 'asc' : 'desc') : ($value == 'desc' ? 'desc' : 'acs');
            }
        }

        $this->condition['order'] = $order;

        return $this;
    }

    /**
     * 限制查询数量
     *
     * @param int      $limit
     * @param int|null $offset
     * @return DB
     */
    public function limit(int $limit, int $offset = null): DB
    {
        $this->condition['limit'] = $limit;

        if (!is_null($offset)) {
            $this->condition['offset'] = $offset;
        }

        return $this;
    }

    /**
     * 限制查询数量
     *
     * @param int      $offset
     * @param int|null $limit
     * @return DB
     */
    public function offset(int $offset, int $limit = null): DB
    {
        $this->condition['offset'] = $offset;

        if (!is_null($limit)) {
            $this->condition['limit'] = $limit;
        }

        return $this;
    }

    /**
     * 设置查询条件
     *
     * @param            $where
     * @param array|null $param_bind
     * @return DB
     */
    public function where($where, array $param_bind = null): DB
    {
        $this->condition['where'] = $where;

        return $this;
    }

    /**
     * 创建一次查询
     *
     * @param string|null $sql
     * @param array|null  $param_bind
     * @return DB
     */
    public static function query(string $sql = null, array $param_bind = null): self
    {
        $db   = new static();
        $link = static::getInstance()->link;

        $db->connection($link);

        if (!is_null($sql)) {
            $db->condition['sql_raw'] = $sql;
        }

        if (!is_null($param_bind)) {
            $db->condition['params'] = $param_bind;
        }

        return $db;
    }

    /**
     * 解析 where 语句
     *
     * @param $where
     * @return string
     */
    private function parseWhere(array $where): string
    {

    }

    /**
     * 生成一个 sql 查询
     *
     * @param array|null $condition
     * @return string
     */
    protected function buildSql(array $condition = null): ?string
    {
        if (is_null($condition)) {
            $condition = $this->condition;
        }

        // 检查最基本的组成元素 是否符合要求
        $method = trim(strtoupper($condition['method']));
        if (!in_array($method, ['SELECT', 'DELETE', 'UPDATE', 'INSERT INTO']) || is_null($condition['from'])) {
            return null;
        }

        switch ($method) {
            // SELECT DATE(`password`), `uid` FROM `cd_users` WHERE `password` < '5' ORDER BY `password` DESC LIMIT 2
            default:
            case 'SELECT':
                $sql = sprintf(
                    '%s %s FROM %s',
                    $method,
                    implode(', ', $this->condition['select']),
                    $this->condition['from']
                );
                break;

            // INSERT INTO `cd_relationships` (`cid`, `mid`) VALUES ('5', '10'), ('7', '20');
            case 'INSERT INTO':
                $params       = [];
                $placeholders = [];
                foreach ($this->condition['values'] as $index => $items) {
                    $values = [];
                    foreach ($items as $key => $item) {
                        $name          = sprintf('v_%s_%s', $index, $key);
                        $params[$name] = $item;
                        $values[]      = sprintf(':%s', $name);
                    }
                    $placeholders[] = sprintf('(%s)', implode(', ', $values));
                }

                $sql                       = sprintf(
                    '%s %s (%s) VALUES %s;',
                    $method,
                    $this->condition['from'],
                    implode(', ', $this->condition['keys']),
                    implode(', ', $placeholders)
                );
                $this->condition['params'] = $params;
                break;

            // UPDATE `cd_relationships` SET `cid` = '14',`mid` = '23' WHERE `cid` = '14' AND `mid` = '23';
            case 'UPDATE':
                $values = [];
                foreach ($this->condition['values'] as $key => $value) {
                    $values[] = sprintf('%s = :%s', $key, $key);
                }
                $sql                       = sprintf(
                    '%s %s SET %s',
                    $method,
                    $this->condition['from'],
                    implode(', ', $values)
                );
                $this->condition['params'] = $this->condition['values'];
                break;

            // DELETE FROM `cd_relationships` WHERE `cid` = '14' AND `mid` = '23';
            case 'DELETE':
                $sql = sprintf(
                    '%s FROM %s',
                    $method,
                    $this->condition['from']
                );
                break;
        }

        echo sprintf("Build SQL: %s\n", $sql);

        return $sql;
    }

    /**
     * 执行查询
     *
     * @return false|PDOStatement
     */
    public function exec()
    {
        $sql  = $this->condition['sql_raw'] ?: $this->buildSql();
        $stmt = $this->link->prepare($sql);

        // 绑定查询结果
        if ($stmt->execute($this->condition['params'])) {
            return $stmt;
        }

        return false;
    }

    /**
     * 查询结构
     *
     * @param array|null $params
     * @return array
     */
    public function get(array $params = null): array
    {
        $this->condition['params'] = $params ?? [];

        $query = $this->exec();

        return $query->fetchAll();
    }
}
