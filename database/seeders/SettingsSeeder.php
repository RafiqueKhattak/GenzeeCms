<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'site_name' => ['GenzeeLogics', 'identity'],
            'site_tagline' => ['Free, private, in-browser calculators and converters', 'identity'],
            'site_url' => ['https://genzeelogics.com', 'identity'],
            'logo_path' => ['/assets/img/logo-small.png', 'identity'],
            'favicon_path' => ['/favicon.ico', 'identity'],
            'meta_title_suffix' => [' | GenzeeLogics', 'seo'],
            'meta_description' => ['Free, private, in-browser calculators and converters — no signup, no data collection.', 'seo'],
            'og_image' => ['https://genzeelogics.com/assets/logo.png', 'seo'],
            'google_analytics_id' => ['G-2H75C9YXND', 'analytics'],
            'google_site_verification' => ['', 'analytics'],
            'adsense_publisher_id' => ['', 'ads'],
            'ads_txt_content' => ["# publisher line will be added after AdSense approval", 'ads'],
            'contact_email' => ['', 'contact'],
        ];

        foreach ($defaults as $key => [$value, $group]) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        }
    }
}
