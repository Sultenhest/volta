<?php

use App\User;
use App\Client;
use App\Project;
use Carbon\Carbon;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{   
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $user = factory(User::class)->create([
            'email' => 'tesla@tesla.dk'
        ]);

        $clients = [];

        for ($x = 0; $x <= 10; $x++) {
            $clients[$x] = $user->clients()->create([
                'name'        => $faker->company(),
                'description' => $faker->paragraph($nbSentences = rand(3, 7)),
                'vat_abbr'    => 'DK',
                'vat'         => $faker->numberBetween(10000000, 99999999)
            ]);

            $clients[$x]->recordActivity('created_client');
        }

        foreach($clients as $client) {
            $no_projects = rand(5, 15);

            for ($p = 0; $p <= $no_projects; $p++) {
                $project = $client->projects()->create([
                    'user_id'     => $client->user_id,
                    'title'       => $faker->sentence($nbWords = rand(1, 5)),
                    'description' => $faker->paragraph($nbSentences = rand(2, 10))
                ]);

                $project->recordActivity('created_project');

                for ($t = 0; $t <= rand(10, 25); $t++) {
                    $task = $project->tasks()->create([
                        'user_id'       => $project->user_id,
                        'title'         => $faker->sentence($nbWords = rand(1, 5)),
                        'description'   => $faker->paragraph($nbSentences = rand(1, 3)),
                        'hours_spent'   => $faker->numberBetween(1, 50),
                        'minutes_spent' => $faker->numberBetween(0, 60),
                        'completed_at'  => $this->randomDate(),
                        'billed_at'     => $this->randomDate(),
                        'created_at'    => Carbon::now()->subWeeks(rand(0, 2))
                    ]);

                    $task->recordActivity('created_task');

                    $task->activity()->first()->update([
                        'created_at' => $this->randomDate()
                    ]);
                }
            }
        }
    }

    public function randomDate() {
        if ( rand(0, 1) ) {
            return Carbon::now()->subWeeks(rand(0, 10));
        }
        return NULL;
    }
}
