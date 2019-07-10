<?php

namespace App\Console\Commands;

use App\Album;
use Illuminate\Console\Command;

class LeakTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leak_test {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memory leak test.';

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
        switch ($type = $this->argument('type')) {

            case 'leak':
                $albums = Album::take(10000)->get();

                $i = 1;
                foreach ($albums as $album) {
                    $songs = $album->songs;
                    echo "$album->id start \n";
                    echo "#executions = " . $album->id . " - mem: " . memory_get_usage() . "\n";
                    echo "$album->id end \n";
                    $album->setRelation('songs', null);
                    unset($songs);
                    unset($album);
                    $i++;
                }
            break;

            case 'no_leak':
                $albums = Album::take(10000)->get();

                $i = 1;
                foreach ($albums as $album) {
                    $songs = $album->songs()->get();
                    echo "$album->id start \n";
                    echo "#executions = " . $album->id . " - mem: " . memory_get_usage() . "\n";
                    echo "$album->id end \n";
                    $i++;
                }
            break;

            case 'leak_solve_by_with':
                $albums = Album::with(['songs'])->take(10000)->get();

                $i = 1;
                foreach ($albums as $album) {
                    $songs = $album->songs;
                    echo "$album->id start \n";
                    echo "#executions = " . $album->id . " - mem: " . memory_get_usage() . "\n";
                    echo "$album->id end \n";
                    $i++;
                }
            break;

            case 'leak_weird':
                $albums = Album::take(10000)->get();

                $i = 1;
                foreach ($albums as $album) {
                    $songs = $album->processSomethingToReturn();
                    echo "$album->id start \n";
                    echo "#executions = " . $album->id . " - mem: " . memory_get_usage() . "\n";
                    echo "$album->id end \n";
                    $i++;
                }
                break;

            case 'leak_solve_by_with_weird':
                $albums = Album::with(['songs'])->take(10000)->get();

                $i = 1;
                foreach ($albums as $album) {
                    $songs = $album->processSomethingToReturn();
                    echo "$album->id start \n";
                    echo "#executions = " . $album->id . " - mem: " . memory_get_usage() . "\n";
                    echo "$album->id end \n";
                    $i++;
                }
            break;

            default:
                $this->error("Invalid type [$type]");
            break;

        }
    }
}