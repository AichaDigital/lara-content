<div class="content-block content-block--recent-posts">
    @if($posts->isEmpty())
        <p class="content-block__empty">{{ __('content::blocks.recent_posts.empty') }}</p>
    @else
        <ul class="content-block__list">
            @foreach($posts as $post)
                <li class="content-block__item">
                    <article class="post-preview">
                        <h3 class="post-preview__title">
                            <a href="{{ route('content.post', $post) }}">
                                {{ $post->getTranslatedContent('title') }}
                            </a>
                        </h3>

                        @if($showDate && $post->published_at)
                            <time class="post-preview__date" datetime="{{ $post->published_at->toIso8601String() }}">
                                {{ $post->published_at->translatedFormat('d M Y') }}
                            </time>
                        @endif

                        @if($showAuthor && $post->author)
                            <span class="post-preview__author">
                                {{ $post->author->getName() }}
                            </span>
                        @endif

                        @if($showExcerpt && $post->getTranslatedContent('excerpt'))
                            <p class="post-preview__excerpt">
                                {{ $post->getTranslatedContent('excerpt') }}
                            </p>
                        @endif
                    </article>
                </li>
            @endforeach
        </ul>
    @endif
</div>
