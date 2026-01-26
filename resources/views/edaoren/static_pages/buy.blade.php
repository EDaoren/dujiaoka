@extends('edaoren.layouts.seo')
@section('content')
    <!-- main start -->
    <section class="main-container">
        <div class="container">
            <div class="good-card">
                        <div class="card mt-3 buy-detail-card">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <div class="product-image-wrapper p-3">
                                        <img src="{{ picture_ulr($picture) }}"
                                             class="product-image rounded" alt="{{ $gd_name }}">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body p-3">
                                        <h4 class="product-title mb-2 fw-bold">{{ $gd_name }}</h4>

                                        <!-- 价格区域 -->
                                        <div class="price-section mb-3 p-2 rounded" style="background-color: #fff9e6;">
                                            <div class="price-label text-muted small">{{ __('dujiaoka.price') }}</div>
                                            <div class="price-value">
                                                <span class="currency fs-5 text-danger fw-bold">{{ __('dujiaoka.money_symbol') }}</span>
                                                <span class="amount fs-3 text-danger fw-bold">{{ $actual_price }}</span>
                                            </div>
                                        </div>

                                        <!-- 商品信息 -->
                                        <div class="product-info mb-3 small">
                                            <div class="info-item">
                                                <span class="info-label text-muted">{{__('goods.fields.in_stock')}}：</span>
                                                <span class="info-value text-dark fw-bold">{{ $in_stock }}</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label text-muted">已售：</span>
                                                <span class="info-value text-dark fw-bold">{{ $sales_volume }}</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label text-muted">类型：</span>
                                                @if($type == \App\Models\Goods::AUTOMATIC_DELIVERY)
                                                    <span class="badge bg-success small"><i class="ali-icon">&#xe7db;</i> {{ __('goods.fields.automatic_delivery') }}</span>
                                                @else
                                                    <span class="badge bg-warning small"><i class="ali-icon">&#xe74b;</i> {{ __('goods.fields.manual_processing') }}</span>
                                                @endif
                                            </div>
                                            @if($buy_limit_num > 0)
                                                <div class="info-item">
                                                    <span class="badge bg-danger small">
                                                        🛒 {{__('dujiaoka.purchase_limit')}}：{{ $buy_limit_num }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- 批发价格 -->
                                        @if(!empty($wholesale_price_cnf) && is_array($wholesale_price_cnf))
                                            <div class="wholesale-section mb-3 p-2 bg-light rounded small">
                                                <div class="wholesale-title text-muted mb-1">
                                                    <i class="ali-icon">&#xe77d;</i> 批发优惠
                                                </div>
                                                <div class="wholesale-list d-flex flex-wrap gap-2">
                                                    @foreach($wholesale_price_cnf as $ws)
                                                        <div class="wholesale-item badge bg-white text-dark border">
                                                            <span class="wholesale-qty">{{ $ws['number'] }}{{ __('dujiaoka.or_the_above') }}</span>
                                                            <span class="wholesale-price text-danger ms-1">{{ __('dujiaoka.money_symbol') }}{{ $ws['price'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <div class="buy-form mt-3">
                                            <form  action="{{ url('create-order') }}" method="post">
                                                {{ csrf_field() }}
                                                <div class="form-group row g-2">
                                                    <div class="col-12 col-md-6">
                                                        <input type="hidden" name="gid" value="{{ $id }}">
                                                        <label for="email" class="form-label small text-muted mb-1">{{ __('dujiaoka.email') }}</label>
                                                        <input type="email" class="form-control form-control-sm"
                                                               name="email" id="email" required placeholder="{{ __('dujiaoka.email') }}">
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <label for="shop-number" class="form-label small text-muted mb-1">{{ __('dujiaoka.by_amount') }}</label>
                                                        <div class="input-group input-group-sm">
                                                            <button class="btn btn-outline-secondary" type="button" onclick="if(this.nextElementSibling.value>1)this.nextElementSibling.value--">-</button>
                                                            <input type="number" class="form-control text-center"
                                                                   id="shop-number" name="by_amount" placeholder="1" min="1" value="1">
                                                            <button class="btn btn-outline-secondary" type="button" onclick="this.previousElementSibling.value++">+</button>
                                                        </div>
                                                    </div>
                                                    @if(isset($open_coupon))
                                                        <div class="col-12 col-md-6">
                                                            <label for="coupon" class="form-label small text-muted mb-1">{{ __('dujiaoka.coupon_code') }}</label>
                                                            <input type="text"
                                                                   class="form-control form-control-sm"
                                                                   id="coupon" name="coupon_code" placeholder="{{ __('dujiaoka.coupon_code') }}" value="" >
                                                        </div>
                                                    @endif
                                                    @if(dujiaoka_config_get('is_open_search_pwd') == \App\Models\Goods::STATUS_OPEN)
                                                        <div class="col-12 col-md-6">
                                                            <label for="search_pwd" class="form-label small text-muted mb-1">{{ __('dujiaoka.search_password') }}</label>
                                                            <input type="text"
                                                                   class="form-control form-control-sm"
                                                                   id="search_pwd" name="search_pwd" required placeholder="{{ __('dujiaoka.search_password') }}" value="" >
                                                        </div>
                                                    @endif

                                                    @if(dujiaoka_config_get('is_open_img_code') == \App\Models\Goods::STATUS_OPEN)
                                                        <div class="col-12 col-md-6">
                                                            <label for="verifyCode" class="form-label small text-muted mb-1">{{ __('dujiaoka.img_verify_code') }}</label>
                                                            <div class="input-group input-group-sm">
                                                                <input type="text" name="img_verify_code" class="form-control"
                                                                       id="verifyCode" required placeholder="{{ __('dujiaoka.img_verify_code') }}">
                                                                <img class="verify-code-img border rounded-end" style="height: 31px; cursor: pointer;" src="{{ captcha_src('buy') . time() }}"
                                                                     alt="{{ __('dujiaoka.img_verify_code') }}" onclick="refresh()" id="imageCode">
                                                            </div>
                                                            <script>
                                                                function refresh(){
                                                                    $('#imageCode').attr('src','{{ captcha_src('buy') }}'+Math.random());
                                                                }
                                                            </script>
                                                        </div>
                                                    @endif

                                                    @if($type == \App\Models\Goods::MANUAL_PROCESSING && is_array($other_ipu))
                                                        @foreach($other_ipu as $ipu)
                                                            <div class="col-12 col-md-6">
                                                                <label for="{{ $ipu['field'] }}" class="form-label small text-muted mb-1">{{ $ipu['desc'] }}</label>
                                                                <input type="text"
                                                                       class="form-control form-control-sm"
                                                                       id="{{ $ipu['field'] }}" name="{{ $ipu['field'] }}" @if($ipu['rule'] !== false) required @endif placeholder="{{ $ipu['placeholder'] }}">
                                                            </div>
                                                        @endforeach
                                                    @endif

                                                    <div class="col-12">
                                                        <label class="form-label small text-muted mb-1">{{ __('dujiaoka.payment_method') }}</label>
                                                        <div class="payment-methods d-flex flex-wrap gap-2">
                                                            @foreach($payways as $index => $way)
                                                                <div class="payment-method-item">
                                                                    <input type="radio" class="btn-check" id="payway-{{ $way['id'] }}"
                                                                           name="payway" value="{{ $way['id'] }}" @if($index == 0) checked="checked" @endif>
                                                                    <label class="btn btn-sm btn-outline-secondary rounded-pill px-3" for="payway-{{ $way['id'] }}">
                                                                        {{ $way['pay_name'] }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>

                                                    <div class="col-12 mt-3">
                                                        <button type="submit" id="submit" class="btn btn-primary w-100 rounded-pill shadow-sm">
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
