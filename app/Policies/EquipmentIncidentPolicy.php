<?php

namespace App\Policies;

use App\Models\EquipmentIncident;
use App\Models\User;

class EquipmentIncidentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('maintenance.view');
    }

    public function create(User $user): bool
    {
        return $user->can('maintenance.manage');
    }

    public function resolve(User $user, EquipmentIncident $incident): bool
    {
        return $user->can('maintenance.manage') && $incident->resolved_at === null;
    }
}
