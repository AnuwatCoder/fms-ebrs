<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Administration\UpdateSystemSettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSystemSettingsRequest;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'definitions' => SystemSetting::definitions(),
            'values' => collect(array_keys(SystemSetting::definitions()))
                ->mapWithKeys(fn (string $key): array => [$key => SystemSetting::read($key)]),
        ]);
    }

    public function update(
        UpdateSystemSettingsRequest $request,
        UpdateSystemSettings $updateSystemSettings,
    ): RedirectResponse {
        $updateSystemSettings->execute($request->user(), $request->validated());

        return to_route('admin.settings')->with('success', 'บันทึกการตั้งค่าระบบเรียบร้อยแล้ว');
    }
}
