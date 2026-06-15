<?php

namespace App\Repositories;

use App\Models\RobotRule;
use Illuminate\Support\Collection;

class RobotRuleRepository
{
    public function getAllActive(): Collection
    {
        return RobotRule::active()->orderBy('id')->get();
    }

    public function getForAgent(string $agent): Collection
    {
        return RobotRule::active()->forAgent($agent)->orderBy('id')->get();
    }

    public function find(int $id): ?RobotRule
    {
        return RobotRule::find($id);
    }

    public function create(array $data): RobotRule
    {
        return RobotRule::create($data);
    }

    public function update(int $id, array $data): ?RobotRule
    {
        $rule = $this->find($id);
        if (!$rule) return null;
        $rule->update($data);
        return $rule->fresh();
    }

    public function delete(int $id): bool
    {
        $rule = $this->find($id);
        if (!$rule) return false;
        return $rule->delete();
    }
}
