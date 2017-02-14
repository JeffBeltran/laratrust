<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Laratrust\LaratrustRole;
use Mockery as m;

class LaratrustRoleTest extends UserTest
{
    public function testUsers()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $belongsToMany = new stdClass();
        $role = m::mock('RoleTestClass')->makePartial();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $role->shouldReceive('belongsToMany')
            ->with('user_table_name', 'assigned_users_table_name', 'role_id', 'user_id')
            ->andReturn($belongsToMany)
            ->once();

        Config::shouldReceive('get')->once()->with('auth.providers.users.model')
            ->andReturn('user_table_name');
        Config::shouldReceive('get')->once()->with('laratrust.role_user_table')
            ->andReturn('assigned_users_table_name');
        Config::shouldReceive('get')->once()->with('laratrust.role_foreign_key')
            ->andReturn('role_id');
        Config::shouldReceive('get')->once()->with('laratrust.user_foreign_key')
            ->andReturn('user_id');

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertSame($belongsToMany, $role->users());
    }

    public function testPermissions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $belongsToMany = new stdClass();
        $role = m::mock('RoleTestClass')->makePartial();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $role->shouldReceive('belongsToMany')
            ->with('permission_table_name', 'assigned_permissions_table_name', 'role_id', 'permission_id')
            ->andReturn($belongsToMany)
            ->once();

        Config::shouldReceive('get')->once()->with('laratrust.permission')
            ->andReturn('permission_table_name');
        Config::shouldReceive('get')->once()->with('laratrust.permission_role_table')
            ->andReturn('assigned_permissions_table_name');
        Config::shouldReceive('get')->once()->with('laratrust.role_foreign_key')
            ->andReturn('role_id');
        Config::shouldReceive('get')->once()->with('laratrust.permission_foreign_key')
            ->andReturn('permission_id');

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertSame($belongsToMany, $role->permissions());
    }

    public function testHasPermission()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $permA = $this->mockPermission('PermissionA');
        $permB = $this->mockPermission('PermissionB');

        $role = new RoleTestClass();
        $role->permissions = [$permA, $permB];

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        Config::shouldReceive('get')->with('cache.ttl', 60)->times(9)->andReturn('1440');
        Cache::shouldReceive('remember')->times(9)->andReturn($role->permissions);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($role->hasPermission('PermissionA'));
        $this->assertTrue($role->hasPermission('PermissionB'));
        $this->assertFalse($role->hasPermission('PermissionC'));

        $this->assertTrue($role->hasPermission(['PermissionA', 'PermissionB']));
        $this->assertTrue($role->hasPermission(['PermissionA', 'PermissionC']));
        $this->assertFalse($role->hasPermission(['PermissionA', 'PermissionC'], true));
        $this->assertFalse($role->hasPermission(['PermissionC', 'PermissionD']));
    }

    public function testAttachPermission()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $permissionObject = m::mock('Permission');
        $permissionArray = ['id' => 2];

        $role = m::mock('RoleTestClass')->makePartial();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $permissionObject->shouldReceive('getKey')
            ->andReturn(1);

        $role->shouldReceive('permissions')
            ->andReturn($role);
        $role->shouldReceive('attach')
            ->with(1)
            ->once()->ordered();
        $role->shouldReceive('attach')
            ->with(2)
            ->once()->ordered();
        $role->shouldReceive('attach')
            ->with(3)
            ->once()->ordered();

        Cache::shouldReceive('forget')
            ->times(3);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $result = $role->attachPermission($permissionObject);
        $this->assertInstanceOf('RoleTestClass', $result);
        $result = $role->attachPermission($permissionArray);
        $this->assertInstanceOf('RoleTestClass', $result);
        $result = $role->attachPermission(3);
        $this->assertInstanceOf('RoleTestClass', $result);
        $this->setExpectedException(InvalidArgumentException::class);
        $role->attachPermission(true);
    }

    public function testDetachPermission()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $permissionObject = m::mock('Permission');
        $permissionArray = ['id' => 2];

        $role = m::mock('RoleTestClass')->makePartial();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $permissionObject->shouldReceive('getKey')
            ->andReturn(1);

        $role->shouldReceive('permissions')
            ->andReturn($role);
        $role->shouldReceive('detach')
            ->with(1)
            ->once()->ordered();
        $role->shouldReceive('detach')
            ->with(2)
            ->once()->ordered();
        $role->shouldReceive('detach')
            ->with(3)
            ->once()->ordered();

        Cache::shouldReceive('forget')
            ->times(3);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $result = $role->detachPermission($permissionObject);
        $this->assertInstanceOf('RoleTestClass', $result);
        $result = $role->detachPermission($permissionArray);
        $this->assertInstanceOf('RoleTestClass', $result);
        $result = $role->detachPermission(3);
        $this->assertInstanceOf('RoleTestClass', $result);
    }

    public function testAttachPermissions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $role = m::mock('RoleTestClass')->makePartial();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $role->shouldReceive('attachPermission')
            ->with(1)
            ->once()->ordered();
        $role->shouldReceive('attachPermission')
            ->with(2)
            ->once()->ordered();
        $role->shouldReceive('attachPermission')
            ->with(3)
            ->once()->ordered();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $result = $role->attachPermissions([1, 2, 3]);
        $this->assertInstanceOf('RoleTestClass', $result);
    }

    public function testDetachPermissions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $role = m::mock('RoleTestClass')->makePartial();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $role->shouldReceive('detachPermission')
            ->with(1)
            ->once()->ordered();
        $role->shouldReceive('detachPermission')
            ->with(2)
            ->once()->ordered();
        $role->shouldReceive('detachPermission')
            ->with(3)
            ->once()->ordered();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $result = $role->detachPermissions([1, 2, 3]);
        $this->assertInstanceOf('RoleTestClass', $result);
    }


    public function testDetachAllPermissions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $permissionA = $this->mockRole('PermissionA');
        $permissionB = $this->mockRole('PermissionB');

        $role = m::mock('RoleTestClass')->makePartial();
        $role->permissions = [$permissionA, $permissionB];

        $relationship = m::mock('BelongsToMany');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        Config::shouldReceive('get')->with('laratrust.permission')->once()->andReturn('App\Permission');
        Config::shouldReceive('get')->with('laratrust.permission_role_table')->once()->andReturn('permission_role');
        Config::shouldReceive('get')->with('laratrust.role_foreign_key')->once()->andReturn('role_id');
        Config::shouldReceive('get')->with('laratrust.permission_foreign_key')->once()->andReturn('permission_id');

        $relationship->shouldReceive('get')
                     ->andReturn($role->permissions)->once();

        $role->shouldReceive('belongsToMany')
                    ->andReturn($relationship)->once();

        $role->shouldReceive('detachPermission')->twice();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $role->detachPermissions();
    }

    public function testSyncPermissions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $permissionsIds = [1, 2, 3];
        $role = m::mock('RoleTestClass')->makePartial();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $role->shouldReceive('permissions')
            ->andReturn($role);
        $role->shouldReceive('sync')
            ->with($permissionsIds)
            ->once()->ordered();

        Cache::shouldReceive('forget')
            ->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertInstanceOf('RoleTestClass', $role->syncPermissions($permissionsIds));
    }
}

class RoleTestClass extends LaratrustRole
{
    public $users;
    public $permissions;
    public $primaryKey;

    public function __construct()
    {
        $this->primaryKey = 'id';
        $this->setAttribute('id', 4);
    }

    public function getKey()
    {
        return $this->id;
    }

    public function belongsToMany($related, $table = null, $foreignKey = null, $otherKey = null, $relation = null)
    {
    }
}
