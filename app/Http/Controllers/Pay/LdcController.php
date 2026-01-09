<?php
namespace App\Http\Controllers\Pay;

use App\Exceptions\RuleValidationException;
use App\Http\Controllers\PayController;
use Illuminate\Http\Request;

class LdcController extends PayController
{

    public function gateway(string $payway, string $orderSN)
    {
        try {
            // 加载网关
            $this->loadGateWay($orderSN, $payway);

            // 将订单号存入Session，用于同步返回时识别订单
            // 注意：LDC的return_url只参与签名，不会覆盖后台配置的URL，所以无法通过URL传递参数
            session(['ldc_latest_order' => $this->order->order_sn]);
            \Log::info('LDC支付存储订单到Session', ['订单号' => $this->order->order_sn]);

            //组装支付参数
            $parameter = [
                'pid' =>  $this->payGateway->merchant_id,
                'type' => $payway, // 使用配置的支付标识
                'out_trade_no' => $this->order->order_sn,
                'name'   => $this->order->order_sn,
                'money'  => number_format((float)$this->order->actual_price, 2, '.', ''), // 确保金额格式正确
                'notify_url' => url($this->payGateway->pay_handleroute . '/notify_url'),
                'return_url' => route('ldc-return'), // LDC会使用后台配置的URL，这里只参与签名
                'sign_type' =>'MD5'
            ];
            ksort($parameter); //重新排序$data数组
            reset($parameter); //内部指针指向数组中的第一个元素

            // 调试：输出排序后的参数
            \Log::info('LDC支付参数排序后', ['参数' => $parameter]);

            $sign = '';
            foreach ($parameter as $key => $val) {
                \Log::info('LDC处理参数', ['key' => $key, 'val' => $val, 'is_empty' => $val === '']);
                // 使用严格比较，避免数字0被误判为空
                if ($key == "sign" || $key == "sign_type" || $val === "") continue;
                if ($sign != '') {
                    $sign .= "&";
                }
                $sign .= "$key=$val"; //拼接为url参数形式
            }

            // 调试输出签名字符串
            $signString = $sign . $this->payGateway->merchant_pem;
            \Log::info('LDC支付签名调试', [
                '原始参数' => $parameter,
                '签名字符串' => $signString,
                '密钥' => $this->payGateway->merchant_pem
            ]);

            $sign = md5($signString);//密码追加进入开始MD5签名
            $parameter['sign'] = $sign;

            \Log::info('LDC支付最终签名', [
                '计算出的签名' => $sign,
                '最终参数' => $parameter
            ]);

            //待请求参数数组 - 使用LDC支付接口
            $apiUrl = rtrim($this->payGateway->merchant_key, '/') . '/pay/submit.php';
            $sHtml = "<form id='ldcsubmit' name='ldcsubmit' action='" . $apiUrl . "' method='post'>";

            foreach($parameter as $key => $val) {
                $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
            }

            //submit按钮控件请不要含有name属性
            $sHtml = $sHtml."<input type='submit' value='正在跳转到LDC积分支付...'></form>";
            $sHtml = $sHtml."<script>document.forms['ldcsubmit'].submit();</script>";
            return $sHtml;
        } catch (RuleValidationException $exception) {
            return $this->err($exception->getMessage());
        }
    }

    public function notifyUrl(Request $request)
    {
        // LDC使用GET方式发送异步通知，明确从query参数获取
        $data = $request->query();
        // 调试日志：记录收到的通知参数
        \Log::info('LDC支付异步通知', [
            '请求方法' => $request->method(),
            '通知参数' => $data,
            '原始QueryString' => $request->getQueryString()
        ]);
        if (!isset($data['out_trade_no'])) {
            \Log::error('LDC通知缺少out_trade_no参数');
            return 'fail';
        }
        $order = $this->orderService->detailOrderSN($data['out_trade_no']);
        if (!$order) {
            \Log::error('LDC通知找不到订单', ['out_trade_no' => $data['out_trade_no']]);
            return 'fail';
        }
        $payGateway = $this->payService->detail($order->pay_id);
        if (!$payGateway) {
            \Log::error('LDC通知找不到支付网关', ['pay_id' => $order->pay_id]);
            return 'fail';
        }
        if($payGateway->pay_handleroute != '/pay/ldc'){
            \Log::error('LDC通知支付路由不匹配', ['route' => $payGateway->pay_handleroute]);
            return 'fail';
        }
        ksort($data); //重新排序$data数组
        reset($data); //内部指针指向数组中的第一个元素
        $sign = '';
        foreach ($data as $key => $val) {
            // 使用严格比较，避免数字0被误判为空
            if ($key == "sign" || $key == "sign_type" || $val === "") continue;
            if ($sign != '') {
                $sign .= "&";
            }
            $sign .= "$key=$val"; //拼接为url参数形式
        }
        $calculatedSign = md5($sign . $payGateway->merchant_pem);
        \Log::info('LDC通知签名验证', [
            '原始参数' => $data,
            '排序后参数' => $data,
            '签名字符串（不含密钥）' => $sign,
            '密钥' => $payGateway->merchant_pem,
            '完整签名字符串' => $sign . $payGateway->merchant_pem,
            '计算签名' => $calculatedSign,
            '接收签名' => $data['sign'] ?? 'missing',
            '签名是否匹配' => $calculatedSign === ($data['sign'] ?? '')
        ]);

        if (!isset($data['trade_no']) || $calculatedSign != ($data['sign'] ?? '')) {
            \Log::error('LDC通知签名验证失败');
            return 'fail';  //返回失败 继续补单
        } else {
            //合法的数据
            //业务处理
            \Log::info('LDC通知验证成功，处理订单', ['out_trade_no' => $data['out_trade_no']]);
            $this->orderProcessService->completedOrder($data['out_trade_no'], $data['money'], $data['trade_no']);
            return 'success';
        }
    }

    public function returnUrl(Request $request)
    {
        // 记录同步返回信息
        \Log::info('LDC支付同步返回', [
            'GET参数' => $request->query(),
            '原始URL' => $request->fullUrl(),
            'Session中的订单号' => session('ldc_latest_order')
        ]);
        // 从Session获取最近发起的订单号
        // 因为LDC的return_url只参与签名，实际跳转到后台配置的固定URL，无法传递订单信息
        $orderSN = session('ldc_latest_order');

        if ($orderSN) {
            // 清除Session，避免下次支付时混淆
            session()->forget('ldc_latest_order');

            \Log::info('LDC支付从Session获取到订单号', ['订单号' => $orderSN]);

            // 休眠2秒等待异步通知处理完成
            sleep(2);

            // 跳转到订单详情页
            return redirect(url('detail-order-sn', ['orderSN' => $orderSN]));
        }
        // 如果Session中没有订单号（可能Session过期或用户直接访问），跳转到首页
        \Log::warning('LDC支付同步返回时Session中没有订单号');
        return redirect('/')->with('info', '支付完成，请在订单查询页面查看订单状态');
    }

}
