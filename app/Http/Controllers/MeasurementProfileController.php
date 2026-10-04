<?php

namespace App\Http\Controllers;

use App\Models\MeasurementProfile;
use Illuminate\Http\Request;

class MeasurementProfileController extends Controller
{
    public function index()
    {
        $profiles = auth()->user()->measurementProfiles()->latest()->get();
        $fields = MeasurementProfile::fields();

        return view('account.measurements', compact('profiles', 'fields'));
    }

    public function store(Request $request)
    {
        $rules = ['label' => ['nullable', 'string', 'max:100'], 'notes' => ['nullable', 'string', 'max:500']];
        foreach (array_keys(MeasurementProfile::fields()) as $field) {
            $rules[$field] = ['nullable', 'numeric', 'min:1', 'max:300'];
        }

        $data = $request->validate($rules);

        $clean = collect($data)->filter(fn ($v, $k) => $v !== null && $v !== '')->all();
        $clean['label'] = $clean['label'] ?? t('account.default_profile_label', 'My Measurements');

        auth()->user()->measurementProfiles()->create($clean);

        return back()->with('success', t('flash.measurement_profile_saved', 'Measurement profile saved.'));
    }

    public function update(Request $request, MeasurementProfile $measurementProfile)
    {
        abort_unless($measurementProfile->user_id === auth()->id(), 403);

        $rules = ['label' => ['nullable', 'string', 'max:100'], 'notes' => ['nullable', 'string', 'max:500']];
        foreach (array_keys(MeasurementProfile::fields()) as $field) {
            $rules[$field] = ['nullable', 'numeric', 'min:1', 'max:300'];
        }

        $data = $request->validate($rules);
        $measurementProfile->update(collect($data)->filter(fn ($v) => $v !== null && $v !== '')->all());

        return back()->with('success', t('flash.measurement_profile_updated', 'Measurement profile updated.'));
    }

    public function destroy(MeasurementProfile $measurementProfile)
    {
        abort_unless($measurementProfile->user_id === auth()->id(), 403);

        $measurementProfile->delete();

        return back()->with('success', t('flash.measurement_profile_deleted', 'Measurement profile deleted.'));
    }
}
