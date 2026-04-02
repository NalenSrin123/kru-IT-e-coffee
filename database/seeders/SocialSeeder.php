<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Social; // Make sure your model points to social_links table

class SocialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $socials = [
            [
                'name' => 'Facebook',
                'url' => 'https://www.facebook.com/share/1CU1gPtPrE/?mibextid=wwXIfr',
                'img' => public_path('public/logoes/fb.png'),
            ],
            [
                'name' => 'Instagram',
                'url' => 'https://www.instagram.com/roses_are_rosie?igsh=MWd3bmVjZmRyb3BqNw%3D%3D&utm_source=qr',
                'img' => public_path('public/logoes/ig.png'),
            ],
            [
                'name' => 'Twitter',
                'url' => 'https://twitter.com/yourpage',
                'img' => public_path('public/logoes/tw.png'), // Example of static image path
            ],
            [
                'name' => 'TikTok',
                'url' => 'https://www.tiktok.com/yourpage',
                'img' => public_path('public/logoes/in.png'),
            ],
            [
                'name' => 'YouTube',
                'url' => 'https://youtube.com/@roses_are_rosie?si=cw2RsP1bkYQ87AEu',
                'img' => public_path('public/logoes/yt.png'),
            ],
        ];

        foreach ($socials as $social) {
            Social::create($social);
        }
    }
}