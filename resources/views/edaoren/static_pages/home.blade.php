@extends('edaoren.layouts.default')
@section('content')
    <!-- main start -->
    <section class="main-container">
        <div class="container">
            <!-- 公告横幅 - 独立显示 -->
            <div class="xianyu-notice">
                <div class="notice-wrapper">
                    <!-- 公告图标 -->
                    <div class="notice-icon">
                        <i class="ali-icon">&#xe667;</i>
                    </div>

                    <!-- 公告标签 -->
                    <div class="notice-label">
                        {{ __('dujiaoka.site_announcement') }}
                    </div>

                    <!-- 公告内容轮播 -->
                    <div class="notice-carousel">
                        <div id="noticeCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                @php
                                    $noticeContent = dujiaoka_config_get('notice');
                                    $notices = explode('|', $noticeContent);
                                    if (count($notices) == 1) {
                                        $notices = explode("\n", $noticeContent);
                                    }
                                    $notices = array_filter(array_map('trim', $notices));
                                    if (empty($notices)) {
                                        $notices = [$noticeContent];
                                    }
                                @endphp

                                @foreach($notices as $index => $notice)
                                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                    <div class="notice-content">
                                        {!! $notice !!}
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            @if(count($notices) > 1)
                            <button class="carousel-control-prev" type="button"
                                    data-bs-target="#noticeCarousel" data-bs-slide="prev">
                                <i class="ali-icon">&#xe60d;</i>
                            </button>
                            <button class="carousel-control-next" type="button"
                                    data-bs-target="#noticeCarousel" data-bs-slide="next">
                                <i class="ali-icon">&#xe60e;</i>
                            </button>
                            @endif
                        </div>
                    </div>

                    <!-- 关闭按钮 -->
                    <button class="notice-close" type="button" aria-label="关闭">
                        <i class="ali-icon">&#xe60a;</i>
                    </button>
                </div>
            </div>

            <!-- 分类和商品统一容器 -->
            <div class="xianyu-content-wrapper">
                <!-- 分类导航 -->
                <div class="category">
                    <div class="category-menus">
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a href="#group-all" data-bs-toggle="tab" class="btn btn-outline-secondary active">{{ __('dujiaoka.group_all') }}</a>
                            </li>
                            @foreach($data as  $index => $group)
                                <li class="nav-item">
                                    <a href="#group-{{ $group['id'] }}" data-bs-toggle="tab" class="btn btn-outline-secondary">{{ $group['gp_name'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- 商品列表 -->
                <div class="goods">
                <div class="goods-list mb-5">
                    <div id="goodsTabContent" class="tab-content">

                        <div class="tab-pane fade active show" id="group-all">
                            <div class="masonry-grid">
                                @foreach($data as  $index => $group)
                                    @foreach($group['goods'] as $goods)
                                        <div class="goods-item {{ $goods['in_stock'] <= 0 ? 'out-of-stock' : '' }}">
                                            <div class="card position-relative">
                                                @if($goods['in_stock'] <= 0)
                                                    <div class="out-of-stock-overlay">
                                                        <span class="out-of-stock-badge">{{ __('dujiaoka.out_of_stock') }}</span>
                                                    </div>
                                                @endif
                                                @if($goods['type'] == \App\Models\Goods::AUTOMATIC_DELIVERY)
                                                    <span class="badge bg-success position-absolute top-0 start-0">
                                            <i class="ali-icon">&#xe7db;</i>
                                            {{ __('goods.fields.automatic_delivery') }}
                                        </span>
                                                @else
                                                    <span class="badge bg-warning position-absolute top-0 start-0">
                                                        <i class="ali-icon">&#xe74b;</i>
                                                        {{ __('goods.fields.manual_processing') }}
                                                    </span>
                                                @endif
                                                <img src="{{ picture_ulr($goods['picture']) }}" class="card-img-top" alt="{{ $goods['gd_name'] }}">
                                                <div class="card-body">

                                                    <h6 class="card-title text-truncate">
                                                        {{ $goods['gd_name'] }}
                                                    </h6>

                                                    <button type="button" class="btn btn-sm btn-outline-success">
                                                        <i class="ali-icon">&#xe703;</i>
                                                        <strong>{{ $goods['actual_price'] }}</strong>
                                                    </button>
                                                    @if($goods['wholesale_price_cnf'])
                                                        <button type="button" class="btn btn-sm btn-outline-warning">
                                                            <i class="ali-icon">&#xe77d;</i>
                                                            {{ __('dujiaoka.home_discount') }}
                                                        </button>
                                                    @endif
                                                    <h6 class="mt-2"><small class="text-muted">{{__('goods.fields.in_stock')}}：{{ $goods['in_stock'] }}</small></h6>
                                                    @if($goods['in_stock'] > 0)
                                                        <a href="{{ url("/buy/{$goods['id']}") }}" class="btn btn-primary">
                                                            <i class="ali-icon">&#xe7d8;</i>
                                                            {{ __('dujiaoka.order_now') }}
                                                        </a>
                                                    @else
                                                        <button class="btn btn-primary" disabled>
                                                            <i class="ali-icon">&#xe7d8;</i>
                                                            {{ __('dujiaoka.out_of_stock') }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                                    @endforeach
                                 @endforeach
                            </div>
                        </div>



                        @foreach($data as  $index => $group)
                            <div class="tab-pane fade" id="group-{{ $group['id'] }}">
                                <div class="masonry-grid">
                                    @foreach($group['goods'] as $goods)
                                        <div class="goods-item {{ $goods['in_stock'] <= 0 ? 'out-of-stock' : '' }}">
                                            <div class="card position-relative">
                                                @if($goods['in_stock'] <= 0)
                                                    <div class="out-of-stock-overlay">
                                                        <span class="out-of-stock-badge">{{ __('dujiaoka.out_of_stock') }}</span>
                                                    </div>
                                                @endif
                                                @if($goods['type'] == \App\Models\Goods::AUTOMATIC_DELIVERY)
                                                    <span class="badge bg-success position-absolute top-0 start-0">
                                            <i class="ali-icon">&#xe7db;</i>
                                            {{ __('goods.fields.automatic_delivery') }}
                                        </span>
                                                @else
                                                    <span class="badge bg-warning position-absolute top-0 start-0">
                                        <i class="ali-icon">&#xe74b;</i>
                                        {{ __('goods.fields.manual_processing') }}
                                    </span>
                                                @endif
                                                <img src="{{ picture_ulr($goods['picture']) }}" class="card-img-top" alt="{{ $goods['gd_name'] }}">
                                                <div class="card-body">

                                                    <h6 class="card-title text-truncate">
                                                        {{ $goods['gd_name'] }}
                                                    </h6>

                                                        <button type="button" class="btn btn-sm btn-outline-success">
                                                            <i class="ali-icon">&#xe703;</i>
                                                            <strong>{{ $goods['actual_price'] }}</strong>
                                                        </button>
                                                        @if($goods['wholesale_price_cnf'])
                                                            <button type="button" class="btn btn-sm btn-outline-warning">
                                                                <i class="ali-icon">&#xe77d;</i>
                                                                {{ __('dujiaoka.home_discount') }}
                                                            </button>
                                                        @endif
                                                    <h6 class="mt-2"><small class="text-muted">{{__('goods.fields.in_stock')}}：{{ $goods['in_stock'] }}</small></h6>
                                                    @if($goods['in_stock'] > 0)
                                                        <a href="{{ url("/buy/{$goods['id']}") }}" class="btn btn-primary">
                                                            <i class="ali-icon">&#xe7d8;</i>
                                                            {{ __('dujiaoka.order_now') }}
                                                        </a>
                                                    @else
                                                        <button class="btn btn-primary" disabled>
                                                            <i class="ali-icon">&#xe7d8;</i>
                                                            {{ __('dujiaoka.out_of_stock') }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <!-- 闲鱼风格统一容器结束 -->
        </div>
    </section>
    <!-- main end -->

@stop

@section('js')
    <script>
        $(document).ready(function() {
            // 公告轮播配置
            var noticeCarouselEl = document.getElementById('noticeCarousel');
            if (noticeCarouselEl) {
                var noticeCarousel = new bootstrap.Carousel(noticeCarouselEl, {
                    interval: 5000,
                    wrap: true,
                    pause: 'hover'
                });
            }

            // 关闭公告横幅
            $('.notice-close').on('click', function() {
                $('.xianyu-notice-banner').slideUp(300, function() {
                    localStorage.setItem('noticeHidden', 'true');
                });
            });

            // 检查是否之前已关闭
            if (localStorage.getItem('noticeHidden') === 'true') {
                $('.xianyu-notice-banner').hide();
            }

            // 初始化瀑布流布局
            var $grids = $('.masonry-grid');

            function initMasonry($grid) {
                $grid.imagesLoaded(function() {
                    var msnry = new Masonry($grid[0], {
                        itemSelector: '.goods-item',
                        columnWidth: '.goods-item',
                        percentPosition: true,
                        gutter: 16,
                        transitionDuration: '0.3s'
                    });

                    // 保存 Masonry 实例
                    $grid.data('masonry', msnry);
                });
            }

            // 初始化所有瀑布流容器
            $grids.each(function() {
                initMasonry($(this));
            });

            // Tab 切换时重新布局
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                var targetId = $(e.target).attr('href');
                var $targetGrid = $(targetId).find('.masonry-grid');

                if ($targetGrid.length) {
                    var msnry = $targetGrid.data('masonry');
                    if (msnry) {
                        setTimeout(function() {
                            msnry.layout();
                        }, 100);
                    } else {
                        initMasonry($targetGrid);
                    }
                }
            });

            // 搜索功能
            $("#searchBtn").on("click", function(e) {
                var search_content = $("#searchText").val();
                if($.trim(search_content) != "") {
                    $(".goods-item").hide().filter(":contains('" + search_content + "')").show();

                    // 搜索后重新布局瀑布流
                    $grids.each(function() {
                        var msnry = $(this).data('masonry');
                        if (msnry) {
                            setTimeout(function() {
                                msnry.layout();
                            }, 100);
                        }
                    });
                } else {
                    $(".goods-item").show();

                    // 显示所有商品后重新布局
                    $grids.each(function() {
                        var msnry = $(this).data('masonry');
                        if (msnry) {
                            setTimeout(function() {
                                msnry.layout();
                            }, 100);
                        }
                    });
                }
            });

            // 搜索框回车触发
            $("#searchText").on("keypress", function(e) {
                if(e.which == 13) {
                    $("#searchBtn").click();
                    return false;
                }
            });
        });
    </script>
@stop
