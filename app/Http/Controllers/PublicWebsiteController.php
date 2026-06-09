<?php

namespace App\Http\Controllers;

use App\Features\PublicWebsite\Queries\PublicAboutQuery;
use App\Features\PublicWebsite\Queries\PublicClassScheduleQuery;
use App\Features\PublicWebsite\Queries\PublicGalleryQuery;
use App\Features\PublicWebsite\Queries\PublicHomeQuery;
use App\Features\PublicWebsite\Queries\PublicProductQuery;
use App\Features\PublicWebsite\Queries\PublicServicesQuery;
use App\Features\PublicWebsite\Queries\PublicSettingsQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PublicWebsiteController extends Controller
{
    public function home(PublicHomeQuery $home): View
    {
        return view('public.home', $home->get());
    }

    public function about(PublicAboutQuery $about): View
    {
        return view('public.about', $about->get());
    }

    public function services(PublicServicesQuery $services): View
    {
        return view('public.services', $services->get());
    }

    public function classes(Request $request, PublicClassScheduleQuery $classes): View
    {
        return view('public.classes', $classes->forIndex($request));
    }

    public function products(Request $request, PublicProductQuery $products): View
    {
        return view('public.products', $products->forIndex($request));
    }

    public function gallery(PublicGalleryQuery $gallery): View
    {
        return view('public.gallery', $gallery->get());
    }

    public function location(PublicSettingsQuery $settings): View
    {
        return view('public.location', [
            'settings' => $settings->get(),
        ]);
    }

    public function bmi(PublicSettingsQuery $settings): View
    {
        return view('public.bmi', [
            'settings' => $settings->get(),
        ]);
    }
}
