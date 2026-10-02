<?php

namespace Database\Seeders;

use App\Models\InternshipOffer;
use App\Services\InternshipOfferCatalog;
use Illuminate\Database\Seeder;

class InternshipOfferSeeder extends Seeder
{
    public function run()
    {
        foreach (app(InternshipOfferCatalog::class)->defaults() as $offer) {
            InternshipOffer::firstOrCreate(
                ['slug' => $offer['id']],
                [
                    'title' => $offer['title'],
                    'company' => $offer['company'],
                    'domain' => $offer['domain'],
                    'location' => $offer['location'],
                    'duration' => $offer['duration'],
                    'description' => $offer['description'],
                    'skills' => $offer['skills'],
                    'deadline' => $offer['deadline']->toDateString(),
                    'details' => $offer['details'],
                    'is_active' => true,
                ]
            );
        }
    }
}
