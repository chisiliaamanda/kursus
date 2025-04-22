<?php

use Illuminate\Database\Migrations\Migration;
use Modules\RolePermission\Entities\Permission;

class AddPermissionToNotificationSetupModule extends Migration
{
    public function up()
    {
        $routes = [
            ['name' => 'Posted', 'route' => 'notifications.posted.index', 'type' => 2, 'parent_route' => 'notification'],
            ['name' => 'Send Notification', 'route' => 'notifications.posted.create', 'type' => 3, 'parent_route' => 'notifications.posted.index'],
            ['name' => 'Delete', 'route' => 'notifications.posted.destroy', 'type' => 3, 'parent_route' => 'notifications.posted.index'],
        ];
        foreach ($routes as $route) {
            Permission::updateOrCreate([
                'route' => $route['route'],
            ], [
                    'name' => $route['name'],
                    'route' => $route['route'],
                    'parent_route' => $route['parent_route'],
                    'type' => $route['type'],
                    'module' => null
                ]
            );
        }
    }

    public function down()
    {

    }
}
