<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\RegistrationStep;
use App\Support\RegistrationAge;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegistrationSettingsController extends Controller
{
    public function edit()
    {
        $limits = RegistrationAge::limits();
        $description = RegistrationAge::description();
        $stepCount = RegistrationStep::count();

        return view('admin.registration-settings.edit', compact('limits', 'description', 'stepCount'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'min_age' => ['nullable', 'integer', 'between:1,30'],
            'max_age' => ['nullable', 'integer', 'between:1,30'],
            'age_reference_date' => ['nullable', 'date'],
        ], [], [
            'min_age' => 'usia minimal',
            'max_age' => 'usia maksimal',
            'age_reference_date' => 'tanggal acuan',
        ]);

        $min = $data['min_age'] ?? null;
        $max = $data['max_age'] ?? null;

        if ($min !== null && $max !== null && (int) $max < (int) $min) {
            throw ValidationException::withMessages([
                'max_age' => 'Usia maksimal tidak boleh lebih kecil dari usia minimal.',
            ]);
        }

        AppSetting::put('reg_min_age', $min);
        AppSetting::put('reg_max_age', $max);
        AppSetting::put('reg_age_date', $data['age_reference_date'] ?? null);

        return redirect()->route('admin.registration-settings.edit')
            ->with('success', 'Pengaturan pendaftaran disimpan.');
    }
}