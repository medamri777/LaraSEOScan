<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Models\SitemapUrl;
use Illuminate\Http\Request;

class SitemapUrlController extends Controller
{
    public function index(Request $request)
    {
        $query = SitemapUrl::query();

        if ($request->has('type')) {
            $query->ofType($request->input('type'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json($query->paginate(50));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loc' => 'required|url|max:500',
            'changefreq' => 'required|in:always,hourly,daily,weekly,monthly,yearly,never',
            'priority' => 'required|numeric|min:0|max:1',
            'lastmod' => 'nullable|date',
            'type' => 'required|string|max:50',
            'is_active' => 'boolean',
            'image_url' => 'nullable|url|max:500',
        ]);

        $entry = SitemapUrl::create($data);

        return response()->json($entry, 201);
    }

    public function update(Request $request, int $id)
    {
        $entry = SitemapUrl::find($id);
        if (!$entry) {
            return response()->json(['error' => 'URL not found'], 404);
        }

        $data = $request->validate([
            'loc' => 'sometimes|url|max:500',
            'changefreq' => 'sometimes|in:always,hourly,daily,weekly,monthly,yearly,never',
            'priority' => 'sometimes|numeric|min:0|max:1',
            'lastmod' => 'nullable|date',
            'type' => 'sometimes|string|max:50',
            'is_active' => 'sometimes|boolean',
            'image_url' => 'nullable|url|max:500',
        ]);

        $entry->update($data);

        return response()->json($entry->fresh());
    }

    public function destroy(int $id)
    {
        $entry = SitemapUrl::find($id);
        if (!$entry) {
            return response()->json(['error' => 'URL not found'], 404);
        }

        $entry->delete();

        return response()->json(['message' => 'URL deleted']);
    }
}
