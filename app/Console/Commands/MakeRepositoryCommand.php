<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeRepositoryCommand extends Command
{
    protected $signature = 'make:repo {name}';
    protected $description = 'Create a new repository with interface and register it';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $name = $this->argument('name');
        $this->createInterface($name);
        $this->createRepository($name);
        $this->registerInServiceProvider($name);
        $this->info("Repository and interface created and registered successfully.");
    }

    protected function createInterface($name)
    {
        $interfaceTemplate = str_replace(
            '{{name}}',
            $name,
            file_get_contents(resource_path('stubs/interface.stub'))
        );
        if (!file_exists($path = app_path('/Repositories/Contracts'))) {
            mkdir($path, 0777, true);
        }
        file_put_contents(app_path("/Repositories/Contracts/{$name}RepositoryInterface.php"), $interfaceTemplate);
    }

    protected function createRepository($name)
    {
        $repositoryTemplate = str_replace(
            ['{{name}}', '{{interface}}'],
            [$name, $name . 'RepositoryInterface'],
            file_get_contents(resource_path('stubs/repository.stub'))
        );
        if (!file_exists($path = app_path('/Repositories'))) {
            mkdir($path, 0777, true);
        }
        file_put_contents(app_path("/Repositories/{$name}Repository.php"), $repositoryTemplate);
    }

    protected function registerInServiceProvider($name)
    {
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');
        $content = file_get_contents($providerPath);

        $needle = '//:end-bindings:';
        $replacement = "\$this->app->singleton(\\App\\Repositories\\Contracts\\{$name}RepositoryInterface::class, \\App\\Repositories\\{$name}Repository::class);\n        //:end-bindings:";

        $content = str_replace($needle, $replacement, $content);
        file_put_contents($providerPath, $content);
    }
}
