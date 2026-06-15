<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Repositories\RobotRuleRepository;
use App\Services\RobotsService;
use Illuminate\Http\Request;

class RobotsController extends Controller
{
    protected RobotsService $robots;
    protected RobotRuleRepository $rules;

    public function __construct(RobotsService $robots, RobotRuleRepository $rules)
    {
        $this->robots = $robots;
        $this->rules = $rules;
    }

    public function show()
    {
        $content = $this->robots->getCachedRobots();
        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_agent' => 'required|string|max:100',
            'rule_type' => 'required|in:allow,disallow',
            'path' => 'required|string|max:500',
            'crawl_delay' => 'nullable|integer|min:1|max:60',
            'sitemap_url' => 'nullable|url|max:500',
            'is_active' => 'boolean',
        ]);

        if (!$this->robots->validateRule($data['path'])) {
            return response()->json(['error' => 'Path must start with /'], 422);
        }

        $rule = $this->rules->create($data);
        $this->robots->clearCache();

        return response()->json($rule, 201);
    }

    public function update(Request $request, int $id)
    {
        $rule = $this->rules->find($id);
        if (!$rule) {
            return response()->json(['error' => 'Rule not found'], 404);
        }

        $data = $request->validate([
            'user_agent' => 'sometimes|string|max:100',
            'rule_type' => 'sometimes|in:allow,disallow',
            'path' => 'sometimes|string|max:500',
            'crawl_delay' => 'nullable|integer|min:1|max:60',
            'sitemap_url' => 'nullable|url|max:500',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($data['path']) && !$this->robots->validateRule($data['path'])) {
            return response()->json(['error' => 'Path must start with /'], 422);
        }

        $this->rules->update($id, $data);
        $this->robots->clearCache();

        return response()->json($this->rules->find($id));
    }

    public function destroy(int $id)
    {
        if (!$this->rules->delete($id)) {
            return response()->json(['error' => 'Rule not found'], 404);
        }
        $this->robots->clearCache();
        return response()->json(['message' => 'Rule deleted']);
    }

    public function preview(Request $request)
    {
        $content = $this->robots->buildRobotsContent();
        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }

    public function export()
    {
        $content = $this->robots->buildRobotsContent();
        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="robots.txt"',
        ]);
    }
}
