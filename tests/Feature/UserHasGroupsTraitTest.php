<?php

namespace Tecnico\Tests\Feature;

use Event;
use Tecnico\TecnicoGroup;
use Tecnico\Tests\TestCase;
use Tecnico\Tests\User;

class UserHasGroupsTraitTest extends TestCase
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
        $app['config']->set('tecnico.user_model', User::class);

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

    public function testNewUserHasNoGroups()
    {
        $user = new User();
        $user->name = 'Marcel';
        $user->save();

        $this->assertCount(0, $user->groups);
        $this->assertEquals(0, $user->current_group_id);
        $this->assertNull($user->currentGroup);
        $this->assertCount(0, $user->ownedGroups);
        $this->assertCount(0, $user->invites);
    }

    public function testAttachingGroupSetsCurrentGroup()
    {
        $group = TecnicoGroup::create(['name' => 'Test-Group']);
        $this->assertNull($this->user->currentGroup);

        $this->user->attachGroup($group);

        $this->assertEquals(1, $this->user->currentGroup->getKey());
    }

    public function testCanAttachGroupToUser()
    {
        $group = TecnicoGroup::create(['name' => 'Test-Group']);

        $this->user->attachGroup($group);

        // Reload relation
        $this->assertCount(1, $this->user->groups);
        $this->assertEquals(TecnicoGroup::find(1)->toArray(), $this->user->currentGroup->toArray());
    }

    public function testCanAttachGroupAsArrayToUser()
    {
        $group = TecnicoGroup::create(['name' => 'Test-Group']);

        $this->user->attachGroup($group->toArray());

        // Reload relation
        $this->assertCount(1, $this->user->groups);
        $this->assertEquals(TecnicoGroup::find(1)->toArray(), $this->user->currentGroup->toArray());
    }

    public function testCanAttachGroupAsIDToUser()
    {
        $group = TecnicoGroup::create(['name' => 'Test-Group']);

        $this->user->attachGroup($group->getKey());

        // Reload relation
        $this->assertCount(1, $this->user->groups);
        $this->assertEquals(TecnicoGroup::find(1)->toArray(), $this->user->currentGroup->toArray());
    }

    public function testCanSetPivotDataOnAttachGroupMethod()
    {
        \Schema::table(config('tecnico.group_user_table'), function ($table) {
            $table->boolean('pivot_set')->default(false);
        });

        $group = TecnicoGroup::create(['name' => 'Test-Group']);
        $pivotData = ['pivot_set' => true];

        $this->user->attachGroup($group, $pivotData);

        $this->assertDatabaseHas(config('tecnico.group_user_table'), [
            'user_id' => $this->user->getKey(),
            'group_id' => $group->getKey(),
            'pivot_set' => true,
        ]);
    }

    public function testIsGroupOwner()
    {
        $group = TecnicoGroup::create(['name' => 'Test-Group']);
        $this->user->attachGroup($group->getKey());

        $this->assertFalse($this->user->isGroupOwner());
        $this->assertFalse($this->user->isOwner());

        $group2 = TecnicoGroup::create(['name' => 'Test-Group', 'owner_id' => $this->user->getKey()]);
        $this->user->attachGroup($group2->getKey());

        $this->assertTrue($this->user->isGroupOwner());
        $this->assertTrue($this->user->isOwner());
    }

    public function testIsOwnerOfGroup()
    {
        $group = TecnicoGroup::create(['name' => 'Test-Group']);
        $this->user->attachGroup($group->getKey());

        $this->assertFalse($this->user->isOwnerOfGroup($group));

        $group = TecnicoGroup::create(['name' => 'Test-Group', 'owner_id' => $this->user->getKey()]);
        $this->user->attachGroup($group->getKey());

        $this->assertTrue($this->user->isOwnerOfGroup($group));
    }

    public function testGetOwnedGroups()
    {
        $group = TecnicoGroup::create(['name' => 'Test-Group', 'owner_id' => $this->user->getKey()]);
        $this->user->attachGroup($group->getKey());
        $this->assertCount(1, $this->user->ownedGroups);
    }

    public function testCanDetachGroup()
    {
        $group1 = TecnicoGroup::create(['name' => 'Test-Group 1']);
        $group2 = TecnicoGroup::create(['name' => 'Test-Group 2']);
        $group3 = TecnicoGroup::create(['name' => 'Test-Group 3']);

        $this->user->attachGroup($group1);
        $this->user->attachGroup($group2);
        $this->user->attachGroup($group3);

        $this->assertCount(3, $this->user->groups()->get());

        $this->user->detachGroup($group2);
        $this->assertCount(2, $this->user->groups()->get());
    }

    public function testDetachGroupResetsCurrentGroup()
    {
        $group = TecnicoGroup::create(['name' => 'Test-Group 1']);

        $this->user->attachGroup($group);

        $this->assertEquals($group->getKey(), $this->user->currentGroup->getKey());

        $this->user->detachGroup($group);
        $this->assertNull($this->user->currentGroup);
    }

    public function testAttachGroupFiresEvent()
    {
        Event::fake();

        $group1 = TecnicoGroup::create(['name' => 'Test-Group 1']);
        $this->user->attachGroup($group1);

        Event::assertDispatched(\Tecnico\Events\UserJoinedGroup::class, function ($e) use ($group1) {
            return $e->getGroupId() === $group1->id && $e->getUser()->id === $this->user->id;
        });
        Event::assertNotDispatched(\Tecnico\Events\UserLeftGroup::class);
    }

    public function testDetachGroupFiresEvent()
    {
        Event::fake();

        $group1 = TecnicoGroup::create(['name' => 'Test-Group 1']);
        $this->user->attachGroup($group1);
        $this->user->detachGroup($group1);

        Event::assertDispatched(\Tecnico\Events\UserLeftGroup::class, function ($e) use ($group1) {
            return $e->getGroupId() === $group1->id && $e->getUser()->id === $this->user->id;
        });
    }

    public function testCanAttachMultipleGroups()
    {
        $group1 = TecnicoGroup::create(['name' => 'Test-Group 1']);
        $group2 = TecnicoGroup::create(['name' => 'Test-Group 2']);
        $group3 = TecnicoGroup::create(['name' => 'Test-Group 3']);

        $this->user->attachGroups([
            $group1,
            $group2,
            $group3,
        ]);

        $this->assertCount(3, $this->user->groups()->get());
    }

    public function testCanDetachMultipleGroups()
    {
        $group1 = TecnicoGroup::create(['name' => 'Test-Group 1']);
        $group2 = TecnicoGroup::create(['name' => 'Test-Group 2']);
        $group3 = TecnicoGroup::create(['name' => 'Test-Group 3']);

        $this->user->attachGroups([
            $group1,
            $group2,
            $group3,
        ]);

        $this->assertCount(3, $this->user->groups()->get());

        $this->user->detachGroups([
            $group1,
            $group3,
        ]);

        $this->assertCount(1, $this->user->groups()->get());
    }

    public function testCurrentGroupGetsResetWhenDetached()
    {
        $group1 = TecnicoGroup::create(['name' => 'Test-Group 1']);
        $group2 = TecnicoGroup::create(['name' => 'Test-Group 2']);
        $group3 = TecnicoGroup::create(['name' => 'Test-Group 3']);

        $this->user->attachGroups([
            $group1,
            $group2,
            $group3,
        ]);

        $this->assertEquals($group1->getKey(), $this->user->currentGroup->getKey());

        $this->user->detachGroup($group1);

        $this->assertNull($this->user->currentGroup);
    }

    public function testUserCanSwitchGroup()
    {
        $group1 = TecnicoGroup::create(['name' => 'Test-Group 1']);
        $group2 = TecnicoGroup::create(['name' => 'Test-Group 2']);
        $group3 = TecnicoGroup::create(['name' => 'Test-Group 3']);

        $this->user->attachGroups([
            $group1,
            $group2,
            $group3,
        ]);
        $this->assertEquals($group1->getKey(), $this->user->currentGroup->getKey());
        $this->user->switchGroup($group2);
        $this->assertEquals($group2->getKey(), $this->user->currentGroup->getKey());
    }

    public function testUserCannotSwitchToInvalidGroup()
    {
        $group1 = TecnicoGroup::create(['name' => 'Test-Group 1']);
        $group2 = TecnicoGroup::create(['name' => 'Test-Group 2']);
        $group3 = TecnicoGroup::create(['name' => 'Test-Group 3']);

        $this->user->attachGroups([
            $group1,
            $group2,
        ]);

        $this->expectException('Tecnico\Exceptions\UserNotInGroupException',
            'The user is not in the group Test-Group 3');
        $this->user->switchGroup($group3);
    }

    public function testUserCannotSwitchToNotExistingGroup()
    {
        $group1 = TecnicoGroup::create(['name' => 'Test-Group 1']);
        $group2 = TecnicoGroup::create(['name' => 'Test-Group 2']);

        $this->user->attachGroups([
            $group1,
            $group2,
        ]);

        $this->expectException('Illuminate\Database\Eloquent\ModelNotFoundException');
        $this->user->switchGroup(3);
    }
}
