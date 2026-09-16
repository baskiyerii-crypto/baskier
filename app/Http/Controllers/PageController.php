<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function privacy()
    {
        return view('pages.privacy');
    }

    public function terms()
    {
        return view('pages.terms');
    }

    public function about()
    {
        return view('pages.about');
    }

    public function contact()
    {
        return view('pages.contact', [
            'address' => \App\Models\Setting::get('platform_address'),
            'phone' => \App\Models\Setting::get('platform_phone'),
            'email' => \App\Models\Setting::get('platform_email'),
            'map' => \App\Models\Setting::get('platform_map_embed_url'),
            'instagram' => \App\Models\Setting::get('platform_social_instagram'),
            'website' => \App\Models\Setting::get('platform_social_website'),
        ]);
    }
}
