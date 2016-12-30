<?php

use Illuminate\Database\Seeder;

// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;
use App\User;
class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // TestDummy::times(20)->create('App\Post');
         $faker = Faker\Factory::create();
  
        foreach(range(1,5) as $index)
        {
            User::create([                
                'name' => $faker->userName,
                'email' =>$faker->email,
                'password' =>bcrypt('secret')
            ]);
        }
    }
}
