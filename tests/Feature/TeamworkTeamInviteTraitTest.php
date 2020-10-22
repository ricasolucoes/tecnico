<?php

namespace Tecnico\Tests\Feature;

use Exception;
use Mockery as m;
use Tecnico\GroupInvite;
use Tecnico\TecnicoGroup;
use Tecnico\Tests\TestCase;
use Tecnico\Tests\User;

class TecnicoGroupInviteTraitTest extends TestCase
{
    protected $user;
    protected $invite;
    protected $group;
    protected $inviter;

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('tecnico.user_model', User::class);

        \Schema::create('users', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->user = new User();
        $this->user->name = 'Julia';
        $this->user->email = 'foo@baz.com';
        $this->user->save();

        $this->inviter = new User();
        $this->inviter->name = 'Marcel';
        $this->inviter->email = 'foo@bar.com';
        $this->inviter->save();

        $this->group = TecnicoGroup::create(['name' => 'Test-Group 2']);

        $this->invite = new GroupInvite();
        $this->invite->group_id = $this->group->getKey();
        $this->invite->user_id = $this->inviter->getKey();
        $this->invite->email = $this->user->email;
        $this->invite->type = 'invite';
        $this->invite->accept_token = md5(uniqid(microtime()));
        $this->invite->deny_token = md5(uniqid(microtime()));
        $this->invite->save();
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testGetGroups()
    {
        $this->assertEquals($this->group->getKey(), $this->invite->group->getKey());
    }

    public function testGetUser()
    {
        $this->assertEquals($this->user->getKey(), $this->invite->user->getKey());
    }

    public function testGetInviter()
    {
        $this->assertEquals($this->inviter->getKey(), $this->invite->inviter->getKey());
    }
}
