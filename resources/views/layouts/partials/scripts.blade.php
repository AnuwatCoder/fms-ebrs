<script src="{{ asset('template/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('template/vendor/lucide/lucide.min.js') }}"></script>
@stack('vendor-scripts')
@livewireScripts
<script>
    document.addEventListener('livewire:navigated', () => window.lucide?.createIcons());
</script>
@stack('scripts')
