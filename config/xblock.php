<?php

return [
    /**
     * 定义block的生成方式 分为 database | class
     * 其中database需要安装系统所需要的数据表
     */
    'driver' => 'class',

    /**
     * 业务代码的存放目录
     */
    'block_path' => [
        base_path('app')
    ],

    /**
     * 配置系统注册类
     */
    'register' => [
        'menu' => \XBlock\Kernel\MenuRegister::class,
        'permission' => \XBlock\Kernel\PermissionRegister::class,
        'hook' => \XBlock\Kernel\GlobalHookRegister::class,
        'dict' => null
    ],

    /**
     * 权限模块相关配置
     */
    'access' => [

    ],
    /**
     * 内置路由前缀
     */
    'prefix' => 'api/xblock',

    /**
     * 默认的认证中间件
     */
    'middleware' => 'auth:api',

    /**
     *  block注册列表
     */
    'blocks' => [

    ]
];