@props(['id', 'title' => null, 'size' => 'md'])

<div id="{{ $id }}" class="modal-overlay" data-modal>
    <div class="modal-box modal-{{ $size }}" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
        <div class="modal-header">
            @if ($title)
                <h3 id="{{ $id }}-title" class="modal-title">{{ $title }}</h3>
            @elseif(isset($header))
                {{ $header }}
            @endif
            <button type="button" class="modal-close" data-modal-close aria-label="Tutup">&times;</button>
        </div>

        <div class="modal-body">{{ $slot }}</div>

        @isset($footer)
            <div class="modal-footer">{{ $footer }}</div>
        @endisset
    </div>
</div>