<?php

namespace App\Policies;

use App\Models\EquipmentCategory;
use App\Models\User;

class EquipmentCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('category.view');
    }

    public function create(User $user): bool
    {
        return $user->can('category.create');
    }

    public function update(User $user, EquipmentCategory $category): bool
    {
        return $user->can('category.update');
    }

    public function delete(User $user, EquipmentCategory $category): bool
    {
        return $user->can('category.delete');
    }
}
