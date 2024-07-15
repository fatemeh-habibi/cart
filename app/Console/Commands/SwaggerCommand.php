<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SwaggerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swagger:ge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'swagger generate';

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
     * @return int
     */
    public function handle()
    {
        $this->call('l5-swagger:generate');
    }
}
