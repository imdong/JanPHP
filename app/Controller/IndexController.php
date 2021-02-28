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
}
