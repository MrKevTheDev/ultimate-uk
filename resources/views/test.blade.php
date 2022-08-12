<?php
/**
 * Notes on this sample:
 *
 * - Based on Laravel 7.x
 * - Using PHP 7.3+
 * - $posts is a Paginated Collection
 * - Each $post is an Eloquent model
 * - All Blade and helper functions should be real Laravel helpers
 * - Assume all routes and translation keys exist
 * - Assume all sections and includes are real files
 */
?>

@extend('layouts.default')

@section('content')
    <div id="blog-wrapper" class="blog__wrapper" data-load-msg="Loading blog posts...">
        @if (request()->input('search'))
            <div class="blogs__search">
                {!! request()->input('search') !!}
            </div>
        @endif
        @if ($posts->count())
            @foreach($posts as $post)
                <a href="{!! route('blogs.show', $post->id) !!}" class="blogs__post">
                    <a href="{!! route('blogs.show', $post->id) !!}" class="blogs__title">
                        {{ $post->title }}
                    </a>
                    <div class="blogs__excerpt">
                        {{ $post->excerpt }}
                    </div>
                </a>
            @endforeach
                <div class="blogs-meta">
                    <div class="blogs-meta__info">
                        {!! trans('list.pagination_counts', [
                        'from' => $posts->firstItem(),
                        'to' => $posts->lastItem(),
                        'total' => $posts->total()
                        ]) !!}
                    </div>
                    <div class="blogs-meta__pagination">
                        {!! $posts->append(request()->except('page'))->links() !!}
                    </div>
                </div>
        @else
            <div class="blogs__no-results">
                {!! trans('list.no_results', ['type' => 'blogs']) !!}
            </div>
        @endif
    </div>

    <div class="blogs__actions">
        <a href="{!! route('blogs/create') !!}" class="btn btn--success">
            {!! trans('buttons.add_type', ['type' => 'Blog']) !!}
        </a>
    </div>
@endsection
