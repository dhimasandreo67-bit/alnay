{{-- Toast Container --}}
<div id="toast-container" class="toast-container" aria-live="polite" aria-atomic="true"></div>

@if (session('success') || session('error') || session('warning') || session('info'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if (session('success')) showToast(@json(session('success')), 'success'); @endif
        @if (session('error'))   showToast(@json(session('error')), 'error'); @endif
        @if (session('warning')) showToast(@json(session('warning')), 'warning'); @endif
        @if (session('info'))    showToast(@json(session('info')), 'info'); @endif
    });
</script>
@endif