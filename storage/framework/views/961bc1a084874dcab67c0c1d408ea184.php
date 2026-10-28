<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['id', 'title' => null, 'size' => 'md']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['id', 'title' => null, 'size' => 'md']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<div id="<?php echo e($id); ?>" class="ui-modal" data-modal hidden aria-hidden="true">
    <div class="ui-modal__backdrop" data-modal-close></div>

    <div class="ui-modal__panel ui-modal__panel--<?php echo e($size); ?>" role="dialog" aria-modal="true"
         aria-labelledby="<?php echo e($id); ?>-title" tabindex="-1">
        <div class="ui-modal__header">
            <?php if($title): ?>
                <h3 id="<?php echo e($id); ?>-title" class="ui-modal__title"><?php echo e($title); ?></h3>
            <?php elseif(isset($header)): ?>
                <?php echo e($header); ?>

            <?php endif; ?>
            <button type="button" class="ui-modal__close" data-modal-close aria-label="Tutup">&times;</button>
        </div>

        <div class="ui-modal__body"><?php echo e($slot); ?></div>

        <?php if(isset($footer)): ?>
            <div class="ui-modal__footer"><?php echo e($footer); ?></div>
        <?php endif; ?>
    </div>
</div><?php /**PATH E:\Project_kecil\alnay\resources\views/components/modal.blade.php ENDPATH**/ ?>