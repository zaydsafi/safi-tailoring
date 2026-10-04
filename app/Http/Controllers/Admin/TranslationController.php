<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $group = $request->query('group');

        $translations = Translation::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->query('q') . '%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('key', 'like', $term)
                        ->orWhere('en', 'like', $term)
                        ->orWhere('ps', 'like', $term)
                        ->orWhere('fa', 'like', $term);
                });
            })
            ->when($request->filled('group'), fn ($query) => $query->where('group', $request->query('group')))
            ->orderBy('group')
            ->orderBy('key')
            ->paginate(20)
            ->withQueryString();

        $groups = $this->groups();

        $totalCount = Translation::count();
        $missingPs = Translation::where(fn ($query) => $query->whereNull('ps')->orWhere('ps', ''))->count();
        $missingFa = Translation::where(fn ($query) => $query->whereNull('fa')->orWhere('fa', ''))->count();

        return view('admin.translations.index', compact('translations', 'q', 'group', 'groups', 'totalCount', 'missingPs', 'missingFa'));
    }

    public function create()
    {
        $groups = $this->groups();

        return view('admin.translations.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Translation::create($data);

        return redirect()->route('admin.translations.index')->with('success', 'Translation created.');
    }

    public function edit(Translation $translation)
    {
        $groups = $this->groups();

        return view('admin.translations.edit', compact('translation', 'groups'));
    }

    public function update(Request $request, Translation $translation)
    {
        $data = $this->validated($request, $translation->id);

        $translation->update($data);

        return redirect()->route('admin.translations.index')->with('success', 'Translation updated.');
    }

    public function destroy(Translation $translation)
    {
        $translation->delete();

        return redirect()->route('admin.translations.index')->with('success', 'Translation deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'group' => ['nullable', 'string', 'max:50', 'regex:/^[a-z0-9_-]+$/'],
            'key' => ['required', 'string', 'max:191', 'regex:/^[a-z0-9._-]+$/', 'unique:translations,key' . ($ignoreId ? ',' . $ignoreId : '')],
            'en' => ['nullable', 'string', 'max:20000'],
            'ps' => ['nullable', 'string', 'max:20000'],
            'fa' => ['nullable', 'string', 'max:20000'],
        ]);

        $data['group'] = filled($data['group'] ?? null) ? $data['group'] : 'general';

        foreach (['en', 'ps', 'fa'] as $field) {
            $value = $data[$field] ?? null;
            $data[$field] = filled($value) ? $value : null;
        }

        return $data;
    }

    private function groups()
    {
        return Translation::query()
            ->whereNotNull('group')
            ->where('group', '!=', '')
            ->distinct()
            ->orderBy('group')
            ->pluck('group');
    }
}
