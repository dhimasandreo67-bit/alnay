@props(['id', 'title' => null, 'size' => 'md'])

{{-- Modal universal. Dibuka lewat <button data-modal-open="{{ $id }}"> atau openModal('{{ $id }}').
     Slot: default (isi), $header (opsional, pengganti title), $footer (opsional). --}}
<div id="{{ $id }}" class="ui-modal" data-modal hidden aria-hidden="true">
    <div class="ui-modal__backdrop" data-modal-close></div>

    <div class="ui-modal__panel ui-modal__panel--{{ $size }}" role="dialog" aria-modal="true"
         aria-labelledby="{{ $id }}-title" tabindex="-1">
        <div class="ui-modal__header">
            @if ($title)
                <h3 id="{{ $id }}-title" class="ui-modal__title">{{ $title }}</h3>
            @elseif (isset($header))
                {{ $header }}
            @endif
            <button type="button" class="ui-modal__close" data-modal-close aria-label="Tutup">&times;</button>
        </div>

        <div class="ui-modal__body">{{ $slot }}</div>

        @isset($footer)
            <div class="ui-modal__footer">{{ $footer }}</div>
        @endisset
    </div>
</div>