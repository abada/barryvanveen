<?php

use Barryvanveen\Pages\Page;
use Barryvanveen\Faker\Providers\LoremMarkdown;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class PagesTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('nl_NL');
        $faker->addProvider(new LoremMarkdown($faker));

        foreach (range(1, 5) as $index) {
            Page::create([
                'title'            => $faker->sentence(),
                'text'             => $faker->markdownText(),
                // 1 on 10 pages is offline
                'online'           => (rand(0, 10) % 10) ? 1 : 0,
            ]);
        }
    }
}