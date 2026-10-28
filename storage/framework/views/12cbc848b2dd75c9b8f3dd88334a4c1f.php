
<?php
    $pesanToast = [];
    foreach (['success', 'error', 'warning', 'info'] as $tipe) {
        if (session($tipe)) {
            $pesanToast[] = ['message' => session($tipe), 'type' => $tipe];
        }
    }
?>

<?php if(count($pesanToast)): ?>
    <script>
        (window.__toastQueue = window.__toastQueue || []).push(...<?php echo json_encode($pesanToast, 15, 512) ?>);
    </script>
<?php endif; ?><?php /**PATH E:\Project_kecil\alnay\resources\views/components/toast.blade.php ENDPATH**/ ?>