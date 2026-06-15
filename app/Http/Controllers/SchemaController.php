<?php

namespace App\Http\Controllers;

use App\Services\DescriptionGeneratorService;
use Illuminate\Http\Request;

class SchemaController extends Controller
{
    public function generateDescription(Request $request, DescriptionGeneratorService $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string',
            'language' => 'nullable|in:french,arabic,darija,english',
        ]);

        $name = ucwords(strtolower($validated['name']));
        $type = $validated['type'];
        $language = $validated['language'] ?? 'french';

        $description = $service->generate($name, $type, $language);

        return response()->json([
            'description' => $description,
        ]);
    }
}
