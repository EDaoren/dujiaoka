@extends('edaoren.layouts.default')
@section('content')
    <!-- main start -->
    <section class="main-container">
        <div class="container">
            <div class="good-card">
                <div class="row justify-content-center">
                    <div class="card my-3 border-0 shadow-sm">
                            <div class="card-body p-4 text-center">
                                <h3 class="card-title fw-bold mb-4" style="color: var(--primary-color);">
                                    <i class="ali-icon me-2">&#xe832;</i>{{ __('dujiaoka.confirm_order') }}
                                </h3>
                            </div>
                            <div class="card-body px-4 pb-5 pt-0">
                                <div class="mx-auto" style="max-width: 400px;">
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                        <span class="text-muted">{{ __('order.fields.order_sn') }}</span>
                                        <span class="fw-bold">{{ $order_sn }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">{{ __('order.fields.title') }}</span>
                                        <span class="text-end text-truncate" style="max-width: 200px;">{{ $title }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">{{ __('order.fields.goods_price') }}</span>
                                        <span>{{ $goods_price }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">{{ __('order.fields.buy_amount') }}</span>
                                        <span>x {{ $buy_amount }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">{{ __('order.fields.email') }}</span>
                                        <span>{{ $email }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">{{ __('order.fields.type') }}</span>
                                        <div>
                                            @if($type == \App\Models\Order::AUTOMATIC_DELIVERY)
                                                <span class="badge bg-success">{{ __('goods.fields.automatic_delivery') }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ __('goods.fields.manual_processing') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if(!empty($coupon))
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">{{ __('order.fields.coupon_id') }}</span>
                                            <span>{{ $coupon['coupon'] }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">{{ __('order.fields.coupon_discount_price') }}</span>
                                            <span class="text-danger">-{{ __('dujiaoka.money_symbol') }}{{ $coupon_discount_price }}</span>
                                        </div>
                                    @endif
                                    @if($wholesale_discount_price > 0 )
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">{{ __('order.fields.wholesale_discount_price') }}</span>
                                            <span class="text-success">-{{ __('dujiaoka.money_symbol') }}{{ $wholesale_discount_price }}</span>
                                        </div>
                                    @endif
                                    @if(!empty($info))
                                        <div class="mb-2 mt-3 p-3 bg-light rounded">
                                            <label class="text-muted mb-1 d-block">{{ __('dujiaoka.order_information') }}</label>
                                            <p class="mb-0 text-break">{{ $info }}</p>
                                        </div>
                                    @endif

                                    <hr class="my-4">

                                    <div class="d-flex justify-content-between mb-3 align-items-center">
                                        <span class="text-muted">{{ __('order.fields.actual_price') }}</span>
                                        <span class="fs-5 fw-bold text-danger">{{ __('dujiaoka.money_symbol') }}{{ $actual_price }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between mb-4 align-items-center">
                                        <span class="text-muted">{{ __('dujiaoka.payment_method') }}</span>
                                        <span class="fw-bold text-dark">{{ $pay['pay_name'] }}</span>
                                    </div>

                                    <div class="text-muted small text-center mb-4">
                                        {{ __('order.fields.order_created') }}：{{ $created_at }}
                                    </div>

                                    <div class="pay-now text-center">
                                        <a href="{{ url('pay-gateway', ['handle' => urlencode($pay['pay_handleroute']),'payway' => $pay['pay_check'], 'orderSN' => $order_sn]) }}" type="button" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                                            <i class="ali-icon me-1">&#xe673;</i> {{ __('dujiaoka.pay_immediately') }}
                                        </a>
                                    </div>

                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- main end -->
@stop
@section('js')
@stop
