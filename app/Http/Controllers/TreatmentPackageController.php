<?php

namespace App\Http\Controllers;

use App\Models\TreatmentPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TreatmentPackageController extends Controller
{
    public function __construct()
    {
        // Admin-only, but still under auth + clinic context like other modules.
        $this->middleware(['auth', 'clinic.context', 'role:admin']);
    }

    public function index(): View
    {
        $packages = TreatmentPackage::query()
            ->orderByDesc('created_at')
            ->get();

        return view('treatment-packages.index', compact('packages'));
    }

    public function create(): View
    {
        return view('treatment-packages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sessions' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        TreatmentPackage::create($data);

        return redirect()->route('treatment-packages.index')
            ->with('success', 'Treatment package created successfully.');
    }

    public function edit(TreatmentPackage $treatment_package): View
    {
        return view('treatment-packages.edit', ['package' => $treatment_package]);
    }

    public function update(Request $request, TreatmentPackage $treatment_package): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sessions' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $treatment_package->update($data);

        return redirect()->route('treatment-packages.index')
            ->with('success', 'Treatment package updated successfully.');
    }

    public function destroy(TreatmentPackage $treatment_package): RedirectResponse
    {
        $treatment_package->delete();

        return redirect()->route('treatment-packages.index')
            ->with('success', 'Treatment package deleted successfully.');
    }
}
