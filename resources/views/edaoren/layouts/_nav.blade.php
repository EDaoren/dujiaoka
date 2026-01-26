<!-- header start -->
<header class="header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="header-right clearfix">
                    <nav class="navbar navbar-expand-lg navbar-light">
                        <a class="navbar-brand d-flex align-items-center" href="/">
                            <img src="{{ picture_ulr(dujiaoka_config_get('img_logo')) }}" alt="Logo" class="me-2" style="height: 40px;">
                            <span>{{ dujiaoka_config_get('text_logo') }}</span>
                        </a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                                data-bs-target="#navbarColor" aria-controls="navbarColor" aria-expanded="false"
                                aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <div class="collapse navbar-collapse" id="navbarColor">
                            <ul class="navbar-nav me-auto">
                                <li class="nav-item">
                                    <a class="nav-link @if(\Illuminate\Support\Facades\Request::path() == '/') active @endif " href="/">{{__('dujiaoka.home_page')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link @if(\Illuminate\Support\Facades\Request::url() == url('order-search')) active @endif" href="{{ url('order-search') }}">{{ __('dujiaoka.order_search') }}</a>
                                </li>
                            </ul>
                            @if(\Illuminate\Support\Facades\Request::path() == '/')
                                <form class="d-flex">
                                    <input class="form-control form-control-sm me-sm-2" id="searchText" type="text" placeholder="{{ __('dujiaoka.search_goods_name') }}">
                                    <button class="btn btn-primary my-2 my-sm-0" type="button" id="searchBtn">
                                        <i class="ali-icon">&#xe65c;</i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- header end -->
