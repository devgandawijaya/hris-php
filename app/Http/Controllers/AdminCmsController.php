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
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminCmsController extends Controller
{
    public function index(): View
    {
        $sliders = CmsSlider::orderBy('order_index')->get();
        $features = CmsFeature::orderBy('order_index')->get();
        $modules = CmsModule::orderBy('order_index')->get();
        $faqs = CmsFaq::orderBy('order_index')->get();
        $testimonials = CmsTestimonial::orderBy('order_index')->get();
        $settings = CmsSetting::pluck('value', 'key');
        $messages = CmsContactMessage::latest()->take(10)->get();

        return view('super-admin.cms.index', compact(
            'sliders',
            'features',
            'modules',
            'faqs',
            'testimonials',
            'settings',
            'messages'
        ));
    }

    // --- Slider Management ---
    public function storeSlider(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'cta_text' => 'nullable|string|max:100',
            'cta_url' => 'nullable|string|max:255',
            'order_index' => 'required|integer',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        CmsSlider::create($validated);

        return back()->with('success', 'Hero Slider baru berhasil ditambahkan.');
    }

    public function deleteSlider(int $id): RedirectResponse
    {
        CmsSlider::findOrFail($id)->delete();
        return back()->with('success', 'Slide berhasil dihapus.');
    }

    // --- Feature Management ---
    public function storeFeature(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'required|string|max:50',
            'order_index' => 'required|integer',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        CmsFeature::create($validated);

        return back()->with('success', 'Fitur baru berhasil ditambahkan ke landing page.');
    }

    public function deleteFeature(int $id): RedirectResponse
    {
        CmsFeature::findOrFail($id)->delete();
        return back()->with('success', 'Fitur berhasil dihapus.');
    }

    // --- FAQ Management ---
    public function storeFaq(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'order_index' => 'required|integer',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        CmsFaq::create($validated);

        return back()->with('success', 'Pertanyaan FAQ baru berhasil ditambahkan.');
    }

    public function deleteFaq(int $id): RedirectResponse
    {
        CmsFaq::findOrFail($id)->delete();
        return back()->with('success', 'FAQ berhasil dihapus.');
    }

    // --- Settings Update ---
    public function updateSettings(Request $request): RedirectResponse
    {
        $inputs = $request->except(['_token', '_method']);

        foreach ($inputs as $key => $val) {
            CmsSetting::set($key, (string) $val);
        }

        return back()->with('success', 'Konfigurasi website dan informasi kontak berhasil diperbarui.');
    }
}

