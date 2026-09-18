@php
    $roleSimulation = request()->attributes->get(\App\Services\Authorization\RoleSimulationService::REQUEST_ATTRIBUTE, []);
@endphp

@if ($roleSimulation['active'] ?? null)
    <div class="alert alert-warning d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3" role="status">
        <div class="d-flex align-items-start gap-2">
            <i data-lucide="scan-eye" class="lucide-sm mt-1 flex-shrink-0"></i>
            <div>
                <strong>โหมดจำลองบทบาท {{ $roleSimulation['active_label'] }}</strong>
                <div class="small">เมนู ข้อมูล และสิทธิ์การใช้งานกำลังแสดงตามมุมมองของบทบาทนี้ โดย role จริงของคุณยังเป็น SuperAdmin</div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.role-simulation.destroy') }}" class="flex-shrink-0">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-warning">กลับเป็น SuperAdmin</button>
        </form>
    </div>
@endif
