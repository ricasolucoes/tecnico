<?php

namespace Tecnico\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Mockery as m;
use Tecnico\Traits\TecnicoGroupTrait;

class TecnicoGroupTraitTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testGetInvites()
    {
        Config::shouldReceive('get')
            ->once()
            ->with('tecnico.invite_model')
            ->andReturn('Invite');

        $stub = m::mock('\Tecnico\Tests\Feature\TestUserGroupTraitStub[hasMany]');
        $stub->shouldReceive('hasMany')
            ->once()
            ->with('Invite', 'group_id', 'id')
            ->andReturn([]);
        $this->assertEquals([], $stub->invites());
    }

    public function testGetUsers()
    {
        Config::shouldReceive('get')
            ->once()
            ->with('tecnico.user_model')
            ->andReturn('User');

        Config::shouldReceive('get')
            ->once()
            ->with('tecnico.group_user_table')
            ->andReturn('GroupUser');

        $stub = m::mock('\Tecnico\Tests\Feature\TestUserGroupTraitStub[belongsToMany,withTimestamps]');

        $stub->shouldReceive('withTimestamps')
            ->once()
            ->andReturn([]);

        $stub->shouldReceive('belongsToMany')
            ->once()
            ->with('User', 'GroupUser', 'group_id', 'user_id')
            ->andReturnSelf();

        $this->assertEquals([], $stub->users());
    }

    public function testGetOwner()
    {
        Config::shouldReceive('get')
            ->once()
            ->with('tecnico.user_model')
            ->andReturn('\Tecnico\Tests\Feature\TestUser');

        $stub = m::mock('\Tecnico\Tests\Feature\TestUserGroupTraitStub[belongsTo]');
        $stub->shouldReceive('belongsTo')
            ->once()
            ->with('\Tecnico\Tests\Feature\TestUser', 'owner_id', 'user_id')
            ->andReturn([]);

        $this->assertEquals([], $stub->owner());
    }

    public function testHasUser()
    {
        $stub = m::mock('\Tecnico\Tests\Feature\TestUserGroupTraitStub[users,first]');

        $user = m::mock('\Tecnico\Tests\Feature\TestUser[getKey]');
        $user->shouldReceive('getKey')
            ->once()
            ->andReturn('key');

        $stub->shouldReceive('first')
            ->once()
            ->andReturn(true);

        $stub->shouldReceive('where')
            ->once()
            ->with('user_id', '=', 'key')
            ->andReturnSelf();

        $stub->shouldReceive('users')
            ->andReturnSelf();

        $this->assertTrue($stub->hasUser($user));
    }

    public function testHasUserReturnsFalse()
    {
        $stub = m::mock('\Tecnico\Tests\Feature\TestUserGroupTraitStub[users,first]');

        $user = m::mock('\Tecnico\Tests\Feature\TestUser[getKey]');
        $user->shouldReceive('getKey')
            ->once()
            ->andReturn('key');

        $stub->shouldReceive('first')
            ->once()
            ->andReturn(false);

        $stub->shouldReceive('where')
            ->once()
            ->with('user_id', '=', 'key')
            ->andReturnSelf();

        $stub->shouldReceive('users')
            ->andReturnSelf();

        $this->assertFalse($stub->hasUser($user));
    }
}

class TestUser extends \Illuminate\Database\Eloquent\Model
{
    public function getKeyName()
    {
        return 'user_id';
    }
}

class TestUserGroupTraitStub extends \Illuminate\Database\Eloquent\Model
{
    use TecnicoGroupTrait;
}
