<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CmsController extends Controller
{
    public function index()
    {
        $appLogo  = AppSetting::get('app_logo');
        $appName  = AppSetting::get('app_name', 'LIM Management');
        $appTagline = AppSetting::get('app_tagline', 'Sistem Penggajian');
        $companies = Company::orderBy('name')->get();

        return view('cms.index', compact('appLogo', 'appName', 'appTagline', 'companies'));
    }

    public function updateAppBranding(Request $request)
    {
        $validated = $request->validate([
            'app_name'    => 'nullable|string|max:100',
            'app_tagline' => 'nullable|string|max:150',
            'app_logo'    => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:1024',
            'remove_logo' => 'nullable|boolean',
        ]);

        AppSetting::set('app_name', $validated['app_name'] ?? 'LIM Management');
        AppSetting::set('app_tagline', $validated['app_tagline'] ?? 'Sistem Penggajian');

        if ($request->boolean('remove_logo')) {
            $old = AppSetting::get('app_logo');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            AppSetting::set('app_logo', null);
            return back()->with('success', 'Logo aplikasi dihapus.');
        }

        if ($request->hasFile('app_logo')) {
            $old = AppSetting::get('app_logo');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('app_logo')->store('branding', 'public');
            AppSetting::set('app_logo', $path);
        }

        return back()->with('success', 'Pengaturan branding aplikasi diperbarui.');
    }

    public function updateCompanyLogo(Request $request, Company $company)
    {
        $request->validate([
            'logo'        => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:1024',
            'remove_logo' => 'nullable|boolean',
        ]);

        if ($request->boolean('remove_logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
                $company->update(['logo' => null]);
            }
            return back()->with('success', "Logo {$company->name} dihapus.");
        }

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $path = $request->file('logo')->store('logos', 'public');
            $company->update(['logo' => $path]);
            return back()->with('success', "Logo {$company->name} diperbarui. Slip gaji akan menggunakan logo baru.");
        }

        return back()->with('warning', 'Tidak ada perubahan.');
    }
}
