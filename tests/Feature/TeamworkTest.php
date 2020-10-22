<?php

namespace Tecnico\Tests\Feature;

use Exception;
use Illuminate\Support\Facades\Config;
use Mockery as m;
use Tecnico\GroupInvite;
use Tecnico\TecnicoGroup;
use Tecnico\Tests\TestCase;
use Tecnico\Tests\User;

class TecnicoTest extends TestCase
{
    protected $user;

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('tecnico.user_model', 'User');

        \Schema::create('users', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->user = new User();
        $this->user->name = 'Marcel';
        $this->user->save();
    }

    protected function getPackageProviders($app)
    {
        return [\Tecnico\TecnicoServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Tecnico' => \Tecnico\Facades\Tecnico::class,
        ];
    }

    protected function createInvite($group = null)
    {
        if (is_null($group)) {
            $group = TecnicoGroup::create(['name' => 'Test-Group 1']);
        }

        $invite = $this->app->make(Config::get('tecnico.invite_model'));
        $invite->user_id = $this->user->getKey();
        $invite->group_id = $group->getKey();
        $invite->type = 'invite';
        $invite->email = 'foo@bar.com';
        $invite->accept_token = md5(uniqid(microtime()));
        $invite->deny_token = md5(uniqid(microtime()));
        $invite->save();

        return $invite;
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testUser()
    {
        $this->assertNull(\Tecnico::user());
        auth()->login($this->user);
        $this->assertEquals($this->user, \Tecnico::user());
    }

    public function testGetInviteFromTokens()
    {
        $invite = $this->createInvite();

        $this->assertEquals($invite->toArray(), \Tecnico::getInviteFromAcceptToken($invite->accept_token)->toArray());
        $this->assertEquals($invite->toArray(), \Tecnico::getInviteFromDenyToken($invite->deny_token)->toArray());
    }

    public function testDenyInvite()
    {
        $invite = $this->createInvite();
        \Tecnico::denyInvite($invite);
        $this->assertNull(GroupInvite::find($invite->getKey()));
    }

    public function testHasPendingInviteFalse()
    {
        $this->assertFalse(\Tecnico::hasPendingInvite('foo@bar.com', 1));
    }

    public function testHasPendingInviteTrue()
    {
        $invite = $this->createInvite();
        $this->assertTrue(\Tecnico::hasPendingInvite($invite->email, $invite->group_id));
    }

    public function testHasPendingInviteFromObject()
    {
        $group = TecnicoGroup::create(['name' => 'Test-Group 1']);
        $invite = $this->createInvite($group);
        $this->assertTrue(\Tecnico::hasPendingInvite($invite->email, $group));
    }

    public function testHasPendingInviteFromArray()
    {
        $group = TecnicoGroup::create(['name' => 'Test-Group 1']);
        $invite = $this->createInvite($group);
        $this->assertTrue(\Tecnico::hasPendingInvite($invite->email, $group->toArray()));
    }

    public function testCanNotInviteToUserWithoutEmail()
    {
        $group = TecnicoGroup::create(['name' => 'Test-Group 1']);
        $this->user->attachGroup($group);
        auth()->login($this->user);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The provided object has no "email" attribute and is not a string.');
        \Tecnico::inviteToGroup($this->user);
    }

    public function testCanAcceptInvite()
    {
        $group = TecnicoGroup::create(['name' => 'Test-Group 1']);
        $invite = $this->createInvite($group);
        auth()->login($this->user);
        \Tecnico::acceptInvite($invite);

        $this->assertCount(1, $this->user->groups);
        $this->assertEquals($group->getKey(), $this->user->current_group_id);

        $this->assertNull(GroupInvite::find($invite->getKey()));
    }

    public function testCanInviteToGroup()
    {
        auth()->login($this->user);

        $email = 'asd@fake.com';
        $group = TecnicoGroup::create(['name' => 'test']);

        $callback = m::mock('stdClass');
        $callback->shouldReceive('callback')->once()
            ->with(m::type(GroupInvite::class))->andReturn();
        \Tecnico::inviteToGroup($email, $group->getKey(), [$callback, 'callback']);

        $this->assertDatabaseHas(config('tecnico.group_invites_table'), [
            'email' => 'asd@fake.com',
            'user_id' => $this->user->getKey(),
            'group_id' => $group->getKey(),
        ]);
    }

    public function testCanInviteToGroupWithObject()
    {
        auth()->login($this->user);

        $email = 'asd@fake.com';
        $group = TecnicoGroup::create(['name' => 'test']);

        $callback = m::mock('stdClass');
        $callback->shouldReceive('callback')->once()
            ->with(m::type(GroupInvite::class))->andReturn();
        \Tecnico::inviteToGroup($email, $group, [$callback, 'callback']);

        $this->assertDatabaseHas(config('tecnico.group_invites_table'), [
            'email' => 'asd@fake.com',
            'user_id' => $this->user->getKey(),
            'group_id' => $group->getKey(),
        ]);
    }

    public function testCanInviteToGroupWithArray()
    {
        auth()->login($this->user);

        $email = 'asd@fake.com';
        $group = TecnicoGroup::create(['name' => 'test']);

        $callback = m::mock('stdClass');
        $callback->shouldReceive('callback')->once()
            ->with(m::type(GroupInvite::class))->andReturn();
        \Tecnico::inviteToGroup($email, $group->toArray(), [$callback, 'callback']);

        $this->assertDatabaseHas(config('tecnico.group_invites_table'), [
            'email' => 'asd@fake.com',
            'user_id' => $this->user->getKey(),
            'group_id' => $group->getKey(),
        ]);
    }

    public function testCanInviteToGroupWithUser()
    {
        auth()->login($this->user);
        $this->user->email = 'asd@fake.com';
        $group = TecnicoGroup::create(['name' => 'test']);

        $callback = m::mock('stdClass');
        $callback->shouldReceive('callback')->once()
            ->with(m::type(GroupInvite::class))->andReturn();
        \Tecnico::inviteToGroup($this->user, $group->toArray(), [$callback, 'callback']);

        $this->assertDatabaseHas(config('tecnico.group_invites_table'), [
            'email' => 'asd@fake.com',
            'user_id' => $this->user->getKey(),
            'group_id' => $group->getKey(),
        ]);
    }

    public function testCanInviteToGroupWithNull()
    {
        auth()->login($this->user);

        $email = 'asd@fake.com';
        $group = TecnicoGroup::create(['name' => 'test']);
        $this->user->attachGroup($group);

        $callback = m::mock('stdClass');
        $callback->shouldReceive('callback')->once()
            ->with(m::type(GroupInvite::class))->andReturn();
        \Tecnico::inviteToGroup($email, null, [$callback, 'callback']);

        $this->assertDatabaseHas(config('tecnico.group_invites_table'), [
            'email' => 'asd@fake.com',
            'group_id' => $group->getKey(),
        ]);
    }

    public function testCanInviteToGroupWithoutCallback()
    {
        auth()->login($this->user);

        $email = 'asd@fake.com';
        $group = TecnicoGroup::create(['name' => 'test']);
        $this->user->attachGroup($group);

        \Tecnico::inviteToGroup($email);

        $this->assertDatabaseHas(config('tecnico.group_invites_table'), [
            'email' => 'asd@fake.com',
            'group_id' => $group->getKey(),
        ]);
    }

    public function testInviteToGroupFiresEvent()
    {
        $this->expectsEvents(\Tecnico\Events\UserInvitedToGroup::class);

        auth()->login($this->user);

        $email = 'asd@fake.com';
        $group = TecnicoGroup::create(['name' => 'test']);
        $this->user->attachGroup($group);

        \Tecnico::inviteToGroup($email, $group, function ($invite) {
        });
    }
}
