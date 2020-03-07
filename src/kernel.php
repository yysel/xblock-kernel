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
    'core_part' => base_path('core'),

    /**
     * 注册系统菜单的类
     */
    'register' => [
        'menu' => \Core\MenuRegister::class,
        'permission' => \Core\PermissionRegister::class,
        'hook' => \XBlock\Kernel\GlobalHookRegister::class
    ],

    /**
     * 内置路由前缀
     */
    'prefix' => 'api/xblock',

    /**
     *  block注册列表
     */
    'blocks' => [

    ]
];