@extends('layouts.app')

@section('title', 'ตั้งค่าระบบ')
@section('page-title', 'ตั้งค่าระบบ')

@section('content')
<section>
    <h1 class="h3 fw-bold text-slate-800 mb-1">ตั้งค่าระบบ</h1>
    <p class="text-muted mb-0">กำหนดนโยบายพื้นฐานสำหรับการยืมและข้อมูลติดต่อ</p>
</section>

<form method="POST" action="{{ route('admin.settings.update') }}" class="card p-4">
    @csrf
    @method('PATCH')
    <div class="row g-4">
        @foreach ($definitions as $key => $definition)
            <div class="{{ $definition['type'] === 'boolean' ? 'col-12' : 'col-12 col-lg-6' }}">
                @if ($definition['type'] === 'boolean')
                    <input type="hidden" name="{{ $key }}" value="0">
                    <div class="form-check form-switch">
                        <input id="{{ $key }}" type="checkbox" name="{{ $key }}" value="1" class="form-check-input" @checked((bool) old($key, $values[$key]))>
                        <label for="{{ $key }}" class="form-check-label fw-semibold">{{ $definition['label'] }}</label>
                    </div>
                @else
                    <label for="{{ $key }}" class="form-label">{{ $definition['label'] }}</label>
                    <input
                        id="{{ $key }}"
                        name="{{ $key }}"
                        type="{{ $definition['type'] === 'integer' ? 'number' : ($definition['type'] === 'email' ? 'email' : 'text') }}"
                        class="form-control @error($key) is-invalid @enderror"
                        value="{{ old($key, $values[$key]) }}"
                        @if (isset($definition['min'])) min="{{ $definition['min'] }}" @endif
                        @if (isset($definition['max'])) max="{{ $definition['max'] }}" @endif
                        @if ($key !== 'contact_email') required @endif
                    >
                    @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
                @endif
                <div class="form-text">{{ $definition['help'] }}</div>
            </div>
        @endforeach
    </div>
    <div class="d-flex justify-content-end mt-4 pt-4 border-top">
        <button class="btn btn-primary"><i data-lucide="save" class="lucide-sm me-1"></i>บันทึกการตั้งค่า</button>
    </div>
</form>
@endsection
