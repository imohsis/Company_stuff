<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    /*
    protected $except = [
        '/test_stuff','/video/create', '/videos', '/videos/{status}','login','logout',
        'profile/details','/videotype/create','/videotype/edit','/videotype/delete',
        '/videotype/types','/videotype/type' ,'/video','/video/edit','/video/delete',

        '/test_code','/news/create', '/news','/news/item', '/news/{status}','/news/category/news',
        '/news/category/create','/news/category/edit','/news/category/delete','/news/category/'
        ,'/news/category/item' ,'/news','/news/edit','/news/delete'

    ];
    */
    protected $except = [
         '*',
    ];
}
