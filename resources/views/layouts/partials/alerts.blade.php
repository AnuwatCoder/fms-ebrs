@if (session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
        <i data-lucide="circle-check" class="lucide-sm"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

@if (session('status'))
    <div class="alert alert-info d-flex align-items-center gap-2" role="status">
        <i data-lucide="info" class="lucide-sm"></i>
        <div>{{ session('status') }}</div>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
        <i data-lucide="circle-alert" class="lucide-sm"></i>
        <div>{{ session('error') }}</div>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <div class="d-flex align-items-center gap-2 fw-semibold mb-2">
            <i data-lucide="circle-alert" class="lucide-sm"></i>
            กรุณาตรวจสอบข้อมูล
        </div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
