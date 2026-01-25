<nav class="content-block content-block--menu content-block--menu-{{ $style }}">
    @if($items->isNotEmpty())
        <ul class="menu-list menu-list--{{ $style }}">
            @foreach($items as $item)
                @include('content::components.menu-item', ['item' => $item, 'level' => 0])
            @endforeach
        </ul>
    @endif
</nav>
