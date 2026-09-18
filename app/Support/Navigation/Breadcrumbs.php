<?php

namespace App\Support\Navigation;

use App\Models\User;
use Illuminate\Http\Request;

final class Breadcrumbs
{
    /**
     * Build the parent links for the current page. The current-page label is
     * supplied by the Blade view so page titles remain the single source of truth.
     *
     * @return list<array{label: string, url: string}>
     */
    public function parents(Request $request, User $user): array
    {
        $routeName = $request->route()?->getName();

        if (! is_string($routeName) || $routeName === 'dashboard') {
            return [];
        }

        $parents = [$this->item('แดชบอร์ด', 'dashboard')];
        $section = $this->sectionParent($routeName, $user);

        if ($section !== null) {
            $parents[] = $section;
        }

        return $parents;
    }

    /** @return array{label: string, url: string}|null */
    private function sectionParent(string $routeName, User $user): ?array
    {
        return match ($routeName) {
            'equipment.create', 'equipment.show', 'equipment.edit' => $user->can('equipment.view')
                ? $this->item('รายการอุปกรณ์', 'equipment.index')
                : null,
            'category.create', 'category.edit' => $user->can('category.view')
                ? $this->item('หมวดหมู่อุปกรณ์', 'category.index')
                : null,
            'maintenance.create', 'maintenance.resolve' => $user->can('maintenance.view')
                ? $this->item('บำรุงรักษาและเหตุขัดข้อง', 'maintenance.index')
                : null,
            'borrow.edit' => $user->can('borrow.view-own')
                ? $this->item('คำขอยืมของฉัน', 'borrow.mine')
                : null,
            'borrow.show' => $this->borrowRequestParent($user),
            'admin.users.edit' => $this->item('ผู้ใช้งาน', 'admin.users'),
            'admin.roles.create', 'admin.roles.edit' => $this->item('บทบาท', 'admin.roles'),
            default => null,
        };
    }

    /** @return array{label: string, url: string}|null */
    private function borrowRequestParent(User $user): ?array
    {
        if ($user->can('borrow.view')) {
            return $this->item('คำขอยืมทั้งหมด', 'borrow.index');
        }

        return $user->can('borrow.view-own')
            ? $this->item('คำขอยืมของฉัน', 'borrow.mine')
            : null;
    }

    /** @return array{label: string, url: string} */
    private function item(string $label, string $routeName): array
    {
        return [
            'label' => $label,
            'url' => route($routeName),
        ];
    }
}
