<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationStep;
use Illuminate\Http\Request;

class RegistrationStepController extends Controller
{
    public function index()
    {
        $steps = RegistrationStep::ordered()->get();

        return view('admin.registration-steps.index', compact('steps'));
    }

    public function create()
    {
        return view('admin.registration-steps.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['sort_order'] ??= (int) RegistrationStep::max('sort_order') + 1;

        RegistrationStep::create($data);

        return redirect()->route('admin.registration-steps.index')
            ->with('success', 'Langkah berhasil ditambahkan.');
    }

    public function edit(RegistrationStep $step)
    {
        return view('admin.registration-steps.edit', compact('step'));
    }

    public function update(Request $request, RegistrationStep $step)
    {
        $data = $this->validated($request);
        $data['sort_order'] ??= $step->sort_order;

        $step->update($data);

        return redirect()->route('admin.registration-steps.index')
            ->with('success', 'Langkah berhasil diubah.');
    }

    public function destroy(RegistrationStep $step)
    {
        $step->delete();

        return redirect()->route('admin.registration-steps.index')
            ->with('success', 'Langkah berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'between:0,999'],
        ], [], [
            'title' => 'judul langkah',
            'description' => 'keterangan',
            'sort_order' => 'urutan',
        ]);

        $data['description'] = $data['description'] ?? null;
        $data['sort_order'] = $data['sort_order'] ?? null;
        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        return $data;
    }
}