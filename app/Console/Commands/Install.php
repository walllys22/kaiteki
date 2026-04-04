<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'example:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Laravel Example';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->call('key:generate');
        $this->call('migrate:fresh');
        $this->call('db:seed');
        $this->call('storage:link');
        // $this->call('vendor:publish', ['--provider' => VoyagerServiceProvider::class, '--tag' => ['config', 'voyager_avatar']]);
        $this->info('Gracias por instalar LaravelTemplate');
    }
}
