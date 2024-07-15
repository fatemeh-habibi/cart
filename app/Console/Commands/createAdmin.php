<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class createAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'create:admin {email} {password} {name}';
    protected $signature = 'create:admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create First Admin';

    /**
     * Create a new command instance.
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
        if(User::all()){
            $currentUser = User::create([
                'first_name' => 'admin',
                'username' => 'admin',
                'mobile' => '09123703808',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('123456'),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'created_user_id' => 1, //because first admin will be create and system create it. //todo
            ]);
            $currentUser->save();
        }
    }
}
