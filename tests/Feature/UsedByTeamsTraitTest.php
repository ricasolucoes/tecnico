<?php

namespace Tecnico\Tests\Feature;

use Tecnico\TecnicoGroup;
use Tecnico\Tests\Task;
use Tecnico\Tests\TestCase;
use Tecnico\Tests\User;

class UsedByGroupsTraitTest extends TestCase
{
    protected $user;
    protected $group;

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

        \Schema::create('tasks', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('group_id');
            $table->timestamps();
        });
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->user = new User();
        $this->user->name = 'Marcel';
        $this->user->save();

        $this->group = TecnicoGroup::create(['name' => 'Test-Group']);
        $this->user->attachGroup($this->group);
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

    public function testThrowsExceptionWhenUnauthorized()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No authenticated user with selected group present.');

        $task = new Task();
        $task->name = 'Buy milk';
        $task->save();
    }

    public function testGetsCurrentGroupTasks()
    {
        auth()->login($this->user);

        $task = new Task();
        $task->group_id = $this->user->currentGroup->getKey();
        $task->name = 'Buy milk';
        $task->save();

        $task2 = new Task();
        $task2->group_id = $this->user->currentGroup->getKey() + 1;
        $task2->name = 'Buy steaks';
        $task2->save();

        $tasks = Task::all();
        $this->assertCount(1, $tasks);
        $this->assertEquals($task->id, $tasks->first()->id);
        $this->assertEquals($task->group_id, $tasks->first()->group_id);
        $this->assertEquals($task->name, $tasks->first()->name);
    }

    public function testGetsAllTasks()
    {
        auth()->login($this->user);

        $task = new Task();
        $task->group_id = $this->user->currentGroup->getKey();
        $task->name = 'Buy milk';
        $task->save();

        $task2 = new Task();
        $task2->group_id = $this->user->currentGroup->getKey() + 1;
        $task2->name = 'Buy steaks';
        $task2->save();

        $tasks = Task::allGroups()->get();
        $this->assertCount(2, $tasks);
    }

    public function testScopeAutomaticallyAddsCurrentGroup()
    {
        auth()->login($this->user);

        $task = new Task();
        $task->name = 'Buy milk';
        $task->save();

        $this->assertDatabaseHas('tasks', [
            'name' => 'Buy milk',
            'group_id' => $this->user->currentGroup->getKey(),
        ]);
    }
}
