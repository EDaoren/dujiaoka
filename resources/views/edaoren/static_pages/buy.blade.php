@extends('edaoren.layouts.seo')
@section('content')
    <!-- main start -->
    <section class="main-container">
        <div class="container">
            <div class="good-card">
                        <div class="card mt-3 buy-detail-card">
                            <div class="row g-0">
                                <div class="col-md-5">
                                    <div class="product-image-wrapper">
                                        <img src="{{ picture_ulr($picture) }}"
                                             class="product-image" alt="{{ $gd_name }}">
                                        @if($type == \App\Models\Goods::AUTOMATIC_DELIVERY)
                                            <span class="badge bg-success position-absolute top-0 start-0 m-3">
                                                <i class="ali-icon">&#xe7db;</i>
                                                {{ __('goods.fields.automatic_delivery') }}
                                            </span>
                                        @else
                                            <span class="badge bg-warning position-absolute top-0 start-0 m-3">
                                                <i class="ali-icon">&#xe74b;</i>
                                                {{ __('goods.fields.manual_processing') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="card-body p-4">
                                        <h2 class="product-title mb-3">{{ $gd_name }}</h2>

                                        <!-- 价格区域 -->
                                        <div class="price-section mb-4">
                                            <div class="price-label">{{ __('dujiaoka.price') }}</div>
                                            <div class="price-value">
                                                <span class="currency">{{ __('dujiaoka.money_symbol') }}</span>
                                                <span class="amount">{{ $actual_price }}</span>
                                            </div>
                                        </div>

                                        <!-- 商品信息 -->
                                        <div class="product-info mb-4">
                                            <div class="info-item">
                                                <i class="ali-icon">&#xe703;</i>
                                                <span class="info-label">{{__('goods.fields.in_stock')}}：</span>
                                                <span class="info-value {{ $in_stock > 0 ? 'text-success' : 'text-danger' }}">{{ $in_stock }}</span>
                                            </div>
                                            @if($buy_limit_num > 0)
                                                <div class="info-item">
                                                    <span class="badge bg-danger">
                                                        <i class="ali-icon">&#xe667;</i>
                                                        {{__('dujiaoka.purchase_limit')}}：{{ $buy_limit_num }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- 批发价格 -->
                                        @if(!empty($wholesale_price_cnf) && is_array($wholesale_price_cnf))
                                            <div class="wholesale-section mb-4">
                                                <div class="wholesale-title">
                                                    <i class="ali-icon">&#xe77d;</i> 批发优惠
                                                </div>
                                                <div class="wholesale-list">
                                                    @foreach($wholesale_price_cnf as $ws)
                                                        <div class="wholesale-item">
                                                            <span class="wholesale-qty">{{ $ws['number'] }}{{ __('dujiaoka.or_the_above') }}</span>
                                                            <span class="wholesale-price">{{ __('dujiaoka.each') }}：{{ $ws['price'] }}{{ __('dujiaoka.money_symbol') }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <div class="buy-form mt-4">
                                            <form  action="{{ url('create-order') }}" method="post">
                                                {{ csrf_field() }}
                                                <div class="form-group row g-3">
                                                    <div class="col-12 col-md-6">
                                                        <input type="hidden" name="gid" value="{{ $id }}">
                                                        <label for="email" class="form-label">{{ __('dujiaoka.email') }}</label>
                                                        <input type="email" class="form-control"
                                                               name="email" id="email" required placeholder="{{ __('dujiaoka.email') }}">
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <label for="shop-number" class="form-label">{{ __('dujiaoka.by_amount') }}</label>
                                                        <input type="number" class="form-control"
                                                               id="shop-number" name="by_amount" placeholder="1" min="1" value="1">
                                                    </div>
                                                    @if(isset($open_coupon))
                                                        <div class="col-12 col-md-6">
                                                            <label for="coupon" class="form-label">{{ __('dujiaoka.coupon_code') }}</label>
                                                            <input type="text"
                                                                   class="form-control"
                                                                   id="coupon" name="coupon_code" placeholder="{{ __('dujiaoka.coupon_code') }}" value="" >
                                                        </div>
                                                    @endif
                                                    @if(dujiaoka_config_get('is_open_search_pwd') == \App\Models\Goods::STATUS_OPEN)
                                                        <div class="col-12 col-md-6">
                                                            <label for="search_pwd" class="form-label">{{ __('dujiaoka.search_password') }}</label>
                                                            <input type="text"
                                                                   class="form-control"
                                                                   id="search_pwd" name="search_pwd" required placeholder="{{ __('dujiaoka.search_password') }}" value="" >
                                                        </div>
                                                    @endif

                                                    @if(dujiaoka_config_get('is_open_img_code') == \App\Models\Goods::STATUS_OPEN)
                                                        <div class="col-12 col-md-6">
                                                            <label for="verifyCode" class="form-label">{{ __('dujiaoka.img_verify_code') }}</label>
                                                            <div class="verify-code-wrapper">
                                                                <input type="text" name="img_verify_code" class="form-control"
                                                                       id="verifyCode" required placeholder="{{ __('dujiaoka.img_verify_code') }}">
                                                                <img class="verify-code-img" src="{{ captcha_src('buy') . time() }}"
                                                                     alt="{{ __('dujiaoka.img_verify_code') }}" onclick="refresh()" id="imageCode">
                                                                <script>
                                                                    function refresh(){
                                                                        $('#imageCode').attr('src','{{ captcha_src('buy') }}'+Math.random());
                                                                    }
                                                                </script>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if($type == \App\Models\Goods::MANUAL_PROCESSING && is_array($other_ipu))
                                                        @foreach($other_ipu as $ipu)
                                                            <div class="col-12 col-md-6">
                                                                <label for="{{ $ipu['field'] }}" class="form-label">{{ $ipu['desc'] }}</label>
                                                                <input type="text"
                                                                       class="form-control"
                                                                       id="{{ $ipu['field'] }}" name="{{ $ipu['field'] }}" @if($ipu['rule'] !== false) required @endif placeholder="{{ $ipu['placeholder'] }}">
                                                            </div>
                                                        @endforeach
                                                    @endif

                                                    <div class="col-12">
                                                        <label class="form-label">{{ __('dujiaoka.payment_method') }}</label>
                                                        <div class="payment-methods">
                                                            @foreach($payways as $index => $way)
                                                                <div class="payment-method-item">
                                                                    <input type="radio" class="btn-check" id="payway-{{ $way['id'] }}"
                                                                           name="payway" value="{{ $way['id'] }}" @if($index == 0) checked="checked" @endif>
                                                                    <label class="btn btn-outline-secondary" for="payway-{{ $way['id'] }}">
                                                                        {{ $way['pay_name'] }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>

                                                    <div class="col-12 mt-3">
                                                        <button type="submit" id="submit" class="btn btn-primary btn-lg w-100">
                                                            <i class="ali-icon">&#xe7d8;</i> {{ __('dujiaoka.order_now') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 商品描述 -->
                <div class="card mt-3 mb-3 description-card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="ali-icon">&#xe667;</i> {{ __('goods.fields.description') }}
                                </h5>
                            </div>
                            <div class="card-body">
                                {!! $description !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
    <!-- main end -->
    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">{{ __('goods.fields.buy_prompt') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {!! $buy_prompt !!}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('dujiaoka.close') }}</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal end -->
@stop
@section('js')
<script src="/assets/edaoren/js/bootstrap-input-spinner.js"></script>
<script>
            @if(!empty($buy_prompt))
            var myModal = new bootstrap.Modal(document.getElementById('staticBackdrop'))
            $(function(){
                myModal.show()
            });
            @endif
            $("input[type='number']").inputSpinner();
            $('#submit').click(function(){
                if($("input[name='by_amount']").val() > {{ $in_stock }}){
                    {{-- 数量不允许大于库存 --}}
                    $(".modal-body").html("{{ __('dujiaoka.prompt.inventory_shortage') }}")
                    myModal.show()
                    return false;
                }
                @if($buy_limit_num > 0)
                if($("input[name='by_amount']").val() > {{ $buy_limit_num }}){
                    {{-- 已超过限购数量 --}}
                    $(".modal-body").html("{{ __('dujiaoka.prompt.purchase_limit_exceeded') }}")
                    myModal.show()
                    return false;
                }
                @endif
            });
</script>

@stop
