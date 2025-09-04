<div>
    <div style="display:flex; gap:8px; align-items:center; margin-bottom: 1rem;">
        <input type="text" wire:model.live="search" placeholder="{{ app()->getLocale()==='de' ? 'Suche…' : 'Search…' }}" style="padding:.5rem; flex:1;">
        <select wire:model.live="sort" style="padding:.5rem;">
            <option value="recent">{{ app()->getLocale()==='de' ? 'Neueste' : 'Most recent' }}</option>
            <option value="az">A–Z</option>
        </select>
    </div>

    @if($templates->count())
        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
            @foreach($templates as $t)
                <a href="{{ $t->demo_url }}" target="_blank" style="border:1px solid #e5e7eb; padding:8px; text-decoration:none; color:inherit; border-radius:8px;">
                    <div style="aspect-ratio: 16/9; background:#f3f4f6; display:flex; align-items:center; justify-content:center; overflow:hidden; border-radius:6px;">
                        @if($t->screenshot_url)
                            <img src="{{ $t->screenshot_url }}" alt="{{ $t->name ?? $t->slug }}" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            <span style="opacity:.6">{{ app()->getLocale()==='de' ? 'Kein Vorschaubild' : 'No screenshot' }}</span>
                        @endif
                    </div>
                    <div style="margin-top:.5rem; font-weight:600;">
                        {{ $t->name ?? $t->slug }}
                    </div>
                    <div style="font-size:.85rem; opacity:.75;">
                        {{ $t->demo_url }}
                    </div>
                </a>
            @endforeach
        </div>

        <div style="margin-top:1rem;">
            {{ $templates->links() }}
        </div>
    @else
        <p style="opacity:.7">{{ app()->getLocale()==='de' ? 'Keine Vorlagen gefunden.' : 'No templates found.' }}</p>
    @endif
</div>
