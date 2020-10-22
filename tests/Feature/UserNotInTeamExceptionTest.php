<?php

namespace Tecnico\Tests\Feature;

use Mockery as m;
use Tecnico\Tests\TestCase;
use Tecnico\Tests\User;

class UserNotInGroupExceptionTest extends TestCase
{
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
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testGetGroup(): void
    {
        $exception = new \Tecnico\Exceptions\UserNotInGroupException();
        $exception->setGroup('Test');
        $this->assertEquals('Test', $exception->getGroup());
    }
}
