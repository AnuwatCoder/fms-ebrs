<div class="settings-overlay" data-close-settings></div>
<div class="settings-panel">
    <div class="settings-section d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-0 fw-bold">ตั้งค่าหน้าจอ</h5>
            <div class="text-muted small mt-1">ปรับรูปแบบการแสดงผล</div>
        </div>
        <button class="btn btn-icon btn-icon-sm" data-close-settings aria-label="ปิด">
            <i data-lucide="x" class="lucide-md"></i>
        </button>
    </div>

    <div class="flex-grow-1 overflow-auto custom-scrollbar">
        <div class="settings-section">
            <h6>โหมดสี</h6>
            <div class="d-flex gap-2">
                <button class="mode-card light active" data-mode="light">
                    <div class="mini-preview">
                        <div class="mini-header"><div class="mini-bar w-18"></div></div>
                        <div class="flex-grow-1 mini-bg-light"></div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <strong class="text-xs">สว่าง</strong>
                    </div>
                </button>
                <button class="mode-card dark" data-mode="dark">
                    <div class="mini-preview">
                        <div class="mini-header"><div class="mini-bar w-18"></div></div>
                        <div class="flex-grow-1 mini-bg-dark"></div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <strong class="text-xs">มืด</strong>
                    </div>
                </button>
            </div>
        </div>

        <div class="settings-section">
            <h6>ชุดสี</h6>
            <div class="swatch-grid">
                @foreach (['navy' => 'Navy', 'blue' => 'Blue', 'green' => 'Emerald', 'orange' => 'Orange', 'red' => 'Rose'] as $color => $label)
                    <button class="color-swatch-btn" data-color="{{ $color }}">
                        <div class="swatch bg-swatch-{{ $color }}"></div>
                        <div class="swatch-label">{{ $label }}</div>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="settings-section">
            <h6>เลย์เอาต์</h6>
            <label class="setting-toggle">
                <span class="toggle-label">ย่อแถบเมนู</span>
                <span class="switch" data-setting="collapsed"></span>
            </label>
        </div>
    </div>

    <div class="settings-footer">
        <button class="btn btn-primary w-100" data-close-settings>บันทึกและปิด</button>
    </div>
</div>
