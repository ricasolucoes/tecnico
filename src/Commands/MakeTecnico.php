<?php

namespace Tecnico\Commands;

use Illuminate\Console\Command;

class MakeTecnico extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:tecnico {--views : Only scaffold the tecnico views}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Tecnico scaffolding files.';

    protected $views = [
        'emails/invite.blade.php' => 'tecnico/emails/invite.blade.php',
        'members/list.blade.php' => 'tecnico/members/list.blade.php',
        'create.blade.php' => 'tecnico/create.blade.php',
        'edit.blade.php' => 'tecnico/edit.blade.php',
        'index.blade.php' => 'tecnico/index.blade.php',
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->createDirectories();

        $this->exportViews();

        if (! $this->option('views')) {
            $this->info('Installed GroupController.');
            file_put_contents(
                app_path('Http/Controllers/Tecnico/GroupController.php'),
                $this->compileControllerStub('GroupController')
            );

            $this->info('Installed GroupMemberController.');
            file_put_contents(
                app_path('Http/Controllers/Tecnico/GroupMemberController.php'),
                $this->compileControllerStub('GroupMemberController')
            );

            $this->info('Installed AuthController.');
            file_put_contents(
                app_path('Http/Controllers/Tecnico/AuthController.php'),
                $this->compileControllerStub('AuthController')
            );

            $this->info('Installed JoinGroupListener');
            file_put_contents(
                app_path('Listeners/Tecnico/JoinGroupListener.php'),
                str_replace(
                    '{{namespace}}',
                    $this->getNamespace(),
                    file_get_contents(__DIR__.'/../../stubs/listeners/JoinGroupListener.stub')
                )
            );

            $this->info('Updated Routes File.');
            file_put_contents(
               // app_path('Http/routes.php'),
               base_path('routes/web.php'),
                file_get_contents(__DIR__.'/../../stubs/routes.stub'),
                FILE_APPEND
            );
        }
        $this->comment('Tecnico scaffolding generated successfully!');
    }

    /**
     * Create the directories for the files.
     *
     * @return void
     */
    protected function createDirectories()
    {
        if (! is_dir(app_path('Http/Controllers/Tecnico'))) {
            mkdir(app_path('Http/Controllers/Tecnico'), 0755, true);
        }
        if (! is_dir(app_path('Listeners/Tecnico'))) {
            mkdir(app_path('Listeners/Tecnico'), 0755, true);
        }
        if (! is_dir(base_path('resources/views/tecnico'))) {
            mkdir(base_path('resources/views/tecnico'), 0755, true);
        }
        if (! is_dir(base_path('resources/views/tecnico/emails'))) {
            mkdir(base_path('resources/views/tecnico/emails'), 0755, true);
        }
        if (! is_dir(base_path('resources/views/tecnico/members'))) {
            mkdir(base_path('resources/views/tecnico/members'), 0755, true);
        }
    }

    /**
     * Export the authentication views.
     *
     * @return void
     */
    protected function exportViews()
    {
        foreach ($this->views as $key => $value) {
            $path = base_path('resources/views/'.$value);
            $this->line('<info>Created View:</info> '.$path);
            copy(__DIR__.'/../../stubs/views/'.$key, $path);
        }
    }

    /**
     * Compiles the HTTP controller stubs.
     *
     * @param $stubName
     * @return string
     */
    protected function compileControllerStub($stubName)
    {
        return str_replace(
            '{{namespace}}',
            $this->getNamespace(),
            file_get_contents(__DIR__.'/../../stubs/controllers/'.$stubName.'.stub')
        );
    }

    protected function getNamespace()
    {
        return  app()->getNamespace();
    }
}
