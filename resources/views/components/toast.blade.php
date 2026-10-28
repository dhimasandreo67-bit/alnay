{{-- Taruh SATU kali di layout (sebelum </body>). Container dibuat otomatis oleh toast-modal.js.
     Pesan flash session('success'|'error'|'warning'|'info') ditampilkan sebagai toast. --}}
@php
    $pesanToast = [];
    foreach (['success', 'error', 'warning', 'info'] as $tipe) {
        if (session($tipe)) {
            $pesanToast[] = ['message' => session($tipe), 'type' => $tipe];
        }
    }
@endphp

@if (count($pesanToast))
    <script>
        (window.__toastQueue = window.__toastQueue || []).push(...@json($pesanToast));
    </script>
@endif