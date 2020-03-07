<?php


namespace XBlock\Kernel\Helper;


class  FillContent
{

    const BlockConfig = <<<CON
<?php

namespace {namespace};

use XBlock\Kernel\Blocks\ModelBlock;
use XBlock\Kernel\Elements\Field;
use XBlock\Kernel\Elements\Button;
use XBlock\Kernel\Elements\Component;

class {name} extends ModelBlock
{
    public \$title;
    public \$origin;
    
    public function component()
    {
        return Component::table()->border();
    }
    
    public function header()
    {
        return [
            Field::uuid(),
        ];
    }
    
    public function button()
    {
        return [
            Button::add(),
            Button::edit(),
            Button::delete(),
        ];
    }
}


CON;

    const UserModel = <<<CON
<?php

namespace {namespace};

use Shineiot\Kernel\Models\User as SysUser;

class {name} extends SysUser
{
    protected \$table = 'users';



    public function getLoginUserAttribute()
    {
        return [
            'uuid' => \$this->uuid,
        ];
    }



    //获取当前登录用户的详细信息
    public function getLoginInfoAttribute()
    {
        switch (user('client_type')) {
            case 'android':
                return collect(user('model'))->except('roles');
            case 'web':
            default:
                return collect(user())->except('model', 'all_department_uuid', 'acl');
        }
    }


    public function loginByUsername(\$username)
    {
        \$user = \$this->where('username', \$username)->first();
        if (\$user) {
            if (\$user->checkPassword(request('password', ''))) {
                return \$user;
            } else message('ERROR')->info('密码错误！');
        }
        return message('ERROR')->info('账号不存在！');
    }


    //登录验证的方法 需要登录时在请求header 中加入login-type ;框架就会调用相应的方法验证用户；如下login-type=device_id;就会调用下面的方法loginByDeviceId！
    //登录成功是请返回一个用户模型,登录失败时可以返回false;或者调用message()、modal()、notify()给出具体的错误响应
    //login-type不存在或者相应的方法时未声明时，会调用loginByUsername()验证用户名和密码；
    //可以调用User中的checkPassword（password）来验证密码的正确性；

    public function loginByNoPassword(\$username)
    {
        \$user = \$this->where('username', \$username)->first();
        return \$user ? \$user : message('ERROR')->info('该用户不存在！');
    }
}

CON;
    public $Migration = <<<CON
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class {MigrationClassName} extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::{action}(getTable('{table}'), function(Blueprint \$table)
		{
{content}
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{

	}

}
CON;
    public $DropTable = <<<CON
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class {MigrationClassName} extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::dropIfExists('{table}');
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{

	}

}
CON;


    const AUTH = <<<CON
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session", "token"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => Core\Common\Models\User::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that the reset token should be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
        ],
    ],

];
CON;
    const HANDLER = <<<CON
<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Shineiot\Kernel\Exceptions\NeedApproval;
use Shineiot\Kernel\Exceptions\NoAuthException;
use Shineiot\Kernel\Exceptions\UuidException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected \$dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected \$dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception \$exception
     * @return void
     */
    public function report(Exception \$exception)
    {
        parent::report(\$exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request \$request
     * @param  \Exception \$exception
     * @return \Illuminate\Http\Response
     */
    public function render(\$request, Exception \$exception)
    {
        \$client_type = \$request->header('client-type', 'other');
        if (in_array(\$client_type, ['web', 'app', 'watch', 'ios', 'pad', 'android', 'app-rn', 'android-rn', 'ios-rn']) || !env('APP_DEBUG')) {
            \$Info = [
                'errorInfo' => \$exception->getMessage(),
                'errorPath' => \$exception->getFile(),
                'errorLine' => \$exception->getLine(),
            ];
            switch (\$exception) {
                case (\$exception instanceof MethodNotAllowedHttpException):
                    return response()->json(codeConfig('ERROR_METHOD'));
                case (\$exception instanceof NotFoundHttpException):
                    return response()->json(codeConfig('ERROR_NO_RESOURCES'));
                case (\$exception instanceof NeedApproval):
                    return response()->json(codeConfig('SUCCESS', \$exception->getMessage(), true, 'modal'));
                case(\$exception instanceof AuthenticationException):
                    return response()->json(codeConfig('NO_USER'), 200);
                case(\$exception instanceof NoAuthException):
                    return response()->json(codeConfig('ERROR_AUTH'));
                case(\$exception instanceof QueryException):
                    return response()->json(codeConfig('SQL_ERROR', \$Info));
                case(\$exception instanceof UuidException):
                    return response()->json(codeConfig('ABSENCE_PARAM', \$Info));
//                    return codeConfig('ABSENCE_PARAM');
                case(\$exception instanceof ValidationException):
                    \$error = \$exception->errors();
                    \$error = reset(\$error);
                    return response()->json(codeConfig('ERROR_PARAM', reset(\$error), true));
                default:
                    return response()->json(codeConfig('ERROR_INNER', \$Info));
            }
        } else return parent::render(\$request, \$exception);
    }

    public function unauthenticated(\$request, AuthenticationException \$exception)
    {
        return response()->json(codeConfig('NO_USER'), 200);
    }
}


CON;


}