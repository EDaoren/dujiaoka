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
            //组装支付参数
            $parameter = [
                'pid' =>  $this->payGateway->merchant_id,
                'type' => $payway, // 使用配置的支付标识
                'out_trade_no' => $this->order->order_sn,
                'name'   => $this->order->order_sn,
                'money'  => number_format((float)$this->order->actual_price, 2, '.', ''), // 确保金额格式正确
                'notify_url' => url($this->payGateway->pay_handleroute . '/notify_url'),
                'return_url' => route('ldc-return', ['order_id' => $this->order->order_sn]),
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
        // 明确获取订单号参数
        $oid = $request->input('order_id');

        // 记录返回日志
        \Log::info('LDC支付同步返回', [
            '订单号' => $oid,
            '所有参数' => $request->all()
        ]);

        // 验证订单号是否存在
        if (empty($oid)) {
            \Log::error('LDC返回缺少订单号参数');
            return redirect('/')->with('error', '订单号参数缺失');
        }

        // 休眠2秒等待异步通知处理完成
        // 注意：这会阻塞用户请求，但确保订单状态已更新
        sleep(2);

        // 跳转到订单详情页（与其他支付方式保持一致）
        return redirect(url('detail-order-sn', ['orderSN' => $oid]));
    }

}