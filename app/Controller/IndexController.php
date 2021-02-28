<?php

namespace App\Controller;

use Framework\Controller;
use Framework\DB;

/**
 * 默认控制器
 */
class IndexController extends Controller
{
    /**
     * 首页
     */
    public function index(): string
    {
        return 'Hi! this is JanPHP, a Simple PHP Framework.';
    }

    /**
     * 用户详情
     *
     * @param int|null    $uid
     * @param string|null $names
     * @return string
     */
    public function info(int $uid = null, string $names = null): string
    {
        // // 插入记录
        // $query = DB::query()->from('cd_relationships')->insert(['cid', 'mid'],[
        //     ['cid' => 15, 'mid' => 7],
        //     ['cid' => 15, 'mid' => 8],
        //     ['cid' => 15, 'mid' => 9],
        //     ['cid' => 15, 'mid' => 10],
        // ]);
        // var_dump($query);

        // 查询记录
        // $res = DB::query('select * from cd_users where `uid` = :uid')->get([
        //     'uid' => 1
        // ]);
        // var_dump($res);

        // 设置查询条件
        /**
         * where (space_id = 1 and status = 1) or (status = 2 and create_id in (1,2,3,4,5))
         */
        DB::query()->from('cd_relationships')->update(['cid' => 15, 'mid' => 7])->where([
            [
                'space_id' => 1,
                'status'   => 1
            ],
            'or',
            [
                'status'    => 2,
                'create_id' => 5
            ],
            ['type', 'IN', [1, 2, 3, 4, 5]],
            ['public_at', '<=', '2019-09-10'],
            ['deleted_at', 'is not', null]
        ]);

        return 'Hi';
    }
}
