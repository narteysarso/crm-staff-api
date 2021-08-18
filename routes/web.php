<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->group(['prefix' => 'admin', 'middleware' => 'auth:api'], function ($router) {

    $router->group(['prefix' => 'staffs'], function ($router) {
        $router->get('/birthday', 'AdminStaffsController@birthday');

        $router->get('index[/{offset}]', 'AdminStaffsController@index');
        $router->get('show/{id}', 'AdminStaffsController@show');
        $router->post('create', 'AdminStaffsController@create');
        $router->post('edit', 'AdminStaffsController@edit');
        $router->post('delete', 'AdminStaffsController@delete');
        $router->get('search', 'AdminStaffsController@search');
        $router->get('searchbybranch', 'AdminStaffsController@searchBranchGroupRole');
        $router->post('updateaccesslevel', 'AdminStaffsController@updateAccessLevel');

        $router->group(['prefix' => 'job'], function ($router) {
            $router->get('index', 'AdminJobStaffController@index');
            $router->get('show/{id}', 'AdminJobStaffController@show');
            $router->post('create', 'AdminJobStaffController@create');
            $router->post('edit', 'AdminJobStaffController@edit');
            $router->get('staff/{id}', 'AdminJobStaffController@staffJob');
            $router->post('delete', 'AdminJobStaffController@delete');
        });

        $router->group(['prefix' => 'wage'], function ($router) {
            $router->get('index', 'AdminWageStaffController@index');
            $router->get('show/{id}', 'AdminWageStaffController@show');
            $router->post('create', 'AdminWageStaffController@create');
            $router->post('edit', 'AdminWageStaffController@edit');
            $router->post('delete', 'AdminWageStaffController@delete');
        });

        $router->group(['prefix' => 'emergency'], function ($router) {
            $router->get('index', 'AdminEmergencyStaffController@index');
            $router->get('show/{id}[/{offset}]', 'AdminEmergencyStaffController@show');
            $router->post('create', 'AdminEmergencyStaffController@create');
            $router->post('edit', 'AdminEmergencyStaffController@edit');
            $router->post('delete', 'AdminEmergencyStaffController@delete');
        });

        $router->group(['prefix' => 'employeestatus'], function ($router) {
            $router->get('index', 'AdminEmployeeStatusController@index');
            $router->get('show/{id}', 'AdminEmployeeStatusController@show');
            $router->post('create', 'AdminEmployeeStatusController@create');
            $router->post('edit', 'AdminEmployeeStatusController@edit');
            $router->post('delete', 'AdminEmployeeStatusController@delete');
        });

        $router->group(['prefix' => 'education'], function ($router) {
            $router->get('index', 'AdminEducationStaffController@index');
            $router->get('show/{id}/detailed', 'AdminEducationStaffController@showDetailed');
            $router->get('show/{id}[/{offset}]', 'AdminEducationStaffController@show');
            $router->post('create', 'AdminEducationStaffController@create');
            $router->post('edit', 'AdminEducationStaffController@edit');
            $router->post('delete', 'AdminEducationStaffController@delete');
        });

        $router->group(['prefix' => 'asset'], function ($router) {
            $router->get('index', 'AdminAssetStaffController@index');
            $router->get('show/{id}', 'AdminAssetStaffController@show');
            $router->post('create', 'AdminAssetStaffController@create');
            $router->post('edit', 'AdminAssetStaffController@edit');
            $router->post('delete', 'AdminAssetStaffController@delete');
        });

        $router->group(['prefix' => 'dependant'], function ($router) {
            $router->get('index', 'AdminDependantsStaffController@index');
            $router->get('show/{id}/detailed', 'AdminDependantsStaffController@showDetailed');
            $router->get('show/{id}[/{offset}]', 'AdminDependantsStaffController@show');
            $router->post('create', 'AdminDependantsStaffController@create');
            $router->post('edit', 'AdminDependantsStaffController@edit');
            $router->post('delete', 'AdminDependantsStaffController@delete');
        });

        $router->group(['prefix' => 'relation'], function ($router) {
            $router->get('index', 'AdminStaffsController@relations');
        });

        $router->group(['prefix' => 'comment'], function ($router) {
            $router->get('index', 'AdminCommentStaffController@index');
            $router->get('show/{id}[/{offset}]', 'AdminCommentStaffController@show');
            $router->post('create', 'AdminCommentStaffController@create');
            $router->post('edit', 'AdminCommentStaffController@edit');
            $router->post('delete', 'AdminCommentStaffController@delete');
        });

        $router->group(['prefix' => 'document'], function ($router) {
            $router->get('index', 'AdminStaffController@index');
            $router->get('show/{id}', 'AdminStaffController@show');
            $router->post('create', 'AdminStaffController@create');
            $router->post('edit', 'AdminStaffsController@edit');
            $router->post('delete', 'AdminStaffController@delete');
        });
        // $router->group(['prefix' => ''], function ($router) {
        //     $router->get('index', 'AdminStaffController@index');
        //     $router->get('show/{id}', 'AdminStaffController@show');
        //     $router->post('create', 'AdminStaffController@create');
        //     $router->post('edit', 'AdminStaffsController@edit');
        //     $router->post('delete', 'AdminStaffController@delete');
        // });
    });

    $router->group(['prefix' => 'assetstaff'], function ($router) {
        $router->get('index', 'AdminAssetStaffController@index');
        $router->get('show/{id}', 'AdminAssetStaffController@show');
        $router->post('create', 'AdminAssetStaffController@create');
        $router->post('edit', 'AdminAssetStaffController@edit');
        $router->post('delete', 'AdminAssetStaffController@delete');
    });

    $router->get('roles', 'AdminRoleController@index');

});


$router->group(['prefix' => 'staffs'], function ($router) {
    $router->post('login', 'AuthController@login');
    $router->get('/birthday', 'StaffsController@birthday');
});


$router->group(['middleware' => 'auth:staff'], function ($router) {

    $router->group(['prefix' => 'staffs', ], function ($router) {
    //
        $router->post('auth', 'StaffsController@auth');
        $router->get('profile', 'StaffsController@profile');
        $router->get('index[/{offset}]', 'StaffsController@index');
        $router->post('edit', 'StaffsController@edit');
        $router->post('show/{id}', 'StaffsController@show');
        $router->post('create', 'StaffsController@create');
        $router->post('delete', 'StaffsController@delete');
        $router->get('branch', 'StaffsController@currentBranch');
        $router->get('group', 'StaffsController@currentGroup');
        $router->get('role', 'StaffsController@currentRole');

        $router->post('changepassword', 'StaffsController@updatepassword');

        $router->group(['prefix' => 'coworkers'], function ($router) {
            //
            $router->get('/{offset?}', 'StaffsController@branchCoWorkers');
            $router->get('branch[/{offset}]', 'StaffsController@branchCoWorkers');
            $router->get('group[/{offset}]', 'StaffsController@groupCoWorkers');
            $router->get('role[/{offset}]', 'StaffsController@roleCoWorkers');
        });
    });

});

$router->post('/resetpassword', 'Auth\ResetStaffPassword@reset');
