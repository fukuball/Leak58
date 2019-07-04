<?php

namespace App\Console\Commands;

use App\User;
use App\Album;
use App\Song;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class LeakTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leak_test_data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memory leak test initial data.';

    protected $mappings = [];

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
        $userFaker = Faker::create('App\User');
        $albumFaker = Faker::create('App\Album');
        $songFaker = Faker::create('App\Song');

        for($i = 0; $i < 10000; $i++) {
            $user = User::create([
                'name' => $userFaker->name,
                'email' => Str::random(8).$userFaker->email,
                'password' => bcrypt('secret')
            ]);

            $album = Album::create([
                'title' => $albumFaker->name,
                'user_id' => $user->id
            ]);

            $song = Song::create([
                'title' => $songFaker->name,
                'album_id' => $album->id,
                'user_id' => $user->id
            ]);

            echo "#executions = " . $i . " - mem: " . memory_get_usage() . "\n";
        }
    }
}