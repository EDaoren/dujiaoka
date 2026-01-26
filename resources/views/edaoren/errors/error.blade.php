@extends('edaoren.layouts.default')

@section('content')
    <div class="page-wrapper">
        <!-- main start -->
        <section class="main-container">
            <div class="container">
                <div class="good-card">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-8">
                            <div class="card mt-3 border-0 shadow-sm">
                                <div class="card-body text-center p-5">
                                    <h2 class="mb-3">😣</h2>
                                    <h4 class="mb-3">{{ $title }}</h4>
                                    <p class="text-muted mb-4">{{ $content }}</p>
                                    <div class="mt-4">
                                        @if(!$url)
                                            <a href="javascript:history.back(-1);" class="btn btn-outline-primary rounded-pill px-4">{{ __('dujiaoka.callback') }}</a>
                                        @else
                                            <a href="{{ $url }}" class="btn btn-outline-primary rounded-pill px-4">{{ __('dujiaoka.callback') }}</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- main end -->
        </div>
@stop
