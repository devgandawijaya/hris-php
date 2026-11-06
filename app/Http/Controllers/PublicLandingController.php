<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CmsSlider;
use App\Models\CmsFeature;
use App\Models\CmsModule;
use App\Models\CmsFaq;
use App\Models\CmsTestimonial;
use App\Models\CmsSetting;
use App\Models\CmsContactMessage;
use App\Models\SubscriptionPlan;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PublicLandingController extends Controller
{
    /**
     * Display the dynamic public landing page.
     */
    public function index(): View
    {
        $sliders = CmsSlider::where('is_active', true)
            ->orderBy('order_index')
            ->get();

        $features = CmsFeature::where('is_active', true)
            ->orderBy('order_index')
            ->get();

        $modules = CmsModule::where('is_active', true)
            ->orderBy('order_index')
            ->get();

        $faqs = CmsFaq::where('is_active', true)
            ->orderBy('order_index')
            ->get();

        $testimonials = CmsTestimonial::where('is_active', true)
            ->orderBy('order_index')
            ->get();

        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('price_monthly')
            ->get();

        $settings = CmsSetting::pluck('value', 'key');

        return view('public.landing', compact(
            'sliders',
            'features',
            'modules',
            'faqs',
            'testimonials',
            'plans',
            'settings'
        ));
    }

    /**
     * Handle incoming contact inquiries from landing page.
     */
    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:150',
            'message' => 'required|string|max:2000',
        ]);

        CmsContactMessage::create($validated);

        return back()->with('success', 'Terima kasih! Pesan dan permohonan demo Anda telah kami terima. Tim konsultan RiHRIS akan segera menghubungi Anda.');
    }
}

