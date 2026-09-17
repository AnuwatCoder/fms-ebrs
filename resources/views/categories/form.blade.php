@extends('layouts.app')

@php($editing = $category->exists)
@section('title', $editing ? 'แก้ไขหมวดหมู่อุปกรณ์' : 'เพิ่มหมวดหมู่อุปกรณ์')
@section('page-title', $editing ? 'แก้ไขหมวดหมู่อุปกรณ์' : 'เพิ่มหมวดหมู่อุปกรณ์')

@section('content')
<section class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="h3 fw-bold text-slate-800 mb-1">{{ $editing ? 'แก้ไขหมวดหมู่อุปกรณ์' : 'เพิ่มหมวดหมู่อุปกรณ์' }}</h1>
        <p class="text-muted mb-0">กำหนดชื่อ รหัสอ้างอิง และสถานะการใช้งานของหมวดหมู่</p>
    </div>
    <a href="{{ route('category.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
        <i data-lucide="arrow-left" class="lucide-sm"></i>
        กลับไปหน้ารายการ
    </a>
</section>

<form method="POST" action="{{ $editing ? route('category.update', $category) : route('category.store') }}" class="card p-4">
    @csrf
    @if ($editing) @method('PATCH') @endif

    <div class="row g-4">
        <div class="col-12 col-md-7">
            <label for="name" class="form-label">ชื่อหมวดหมู่ <span class="text-danger">*</span></label>
            <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" maxlength="255" required autofocus>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-5">
            <label class="form-label">รหัสหมวดหมู่</label>
            <div class="form-control bg-light-subtle d-flex align-items-center gap-2" data-auto-code="category">
                @if ($editing)
                    <i data-lucide="lock-keyhole" class="lucide-xs text-muted"></i>
                    <span class="fw-semibold text-primary">{{ $category->code }}</span>
                    <span class="text-muted text-xs">กำหนดโดยระบบ</span>
                @else
                    <i data-lucide="sparkles" class="lucide-xs text-primary"></i>
                    <span class="text-muted">ระบบจะสร้างให้อัตโนมัติ เช่น CAT-0001</span>
                @endif
            </div>
        </div>
        <div class="col-12">
            <label for="description" class="form-label">รายละเอียด</label>
            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4" maxlength="2000">{{ old('description', $category->description) }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <input type="hidden" name="active" value="0">
            <div class="form-check form-switch">
                <input id="active" name="active" class="form-check-input" type="checkbox" value="1" @checked((bool) old('active', $category->exists ? $category->active : true))>
                <label for="active" class="form-check-label">เปิดให้เลือกใช้หมวดหมู่นี้</label>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">
        <a href="{{ route('category.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
        <button class="btn btn-primary" type="submit">{{ $editing ? 'บันทึกการแก้ไข' : 'เพิ่มหมวดหมู่' }}</button>
    </div>
</form>
@endsection
