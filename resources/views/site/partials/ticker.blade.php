@if (
    $ticker && $ticker['lines']->isNotEmpty()
)
    <div class="ticker" style="background: {{ $ticker['bg'] }}; color: {{ $ticker['color'] }};">
        <div class="ticker-badge">عاجل</div>
        <div class="ticker-window" dir="ltr">
            <div class="ticker-track" style="animation-duration: {{ $ticker['speed'] }}s;">
                @foreach ([0, 1] as $copy)
                    <div class="ticker-seq">
                        @foreach ($ticker['lines'] as $line)
                            <span class="ticker-item">{{ $line }}</span>
                            <span aria-hidden="true">◆</span>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif