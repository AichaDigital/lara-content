@php
    $hasChildren = $item->children->isNotEmpty();
    $isActive = request()->url() === $item->getUrl();
@endphp

<li class="menu-item menu-item--level-{{ $level }} @if($hasChildren) menu-item--has-children @endif @if($isActive) menu-item--active @endif">
    <a href="{{ $item->getUrl() }}" class="menu-item__link" @if($item->type->isUrl()) target="_blank" rel="noopener" @endif>
        {{ $item->getTranslatedContent('label') }}
    </a>

    @if($hasChildren)
        <ul class="menu-list menu-list--submenu menu-list--level-{{ $level + 1 }}">
            @foreach($item->children as $child)
                @include('content::components.menu-item', ['item' => $child, 'level' => $level + 1])
            @endforeach
        </ul>
    @endif
</li>
