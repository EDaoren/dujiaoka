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
                \Log::info('LDC处理参数', ['key' => $key, 'val' => $val, 'empty' => empty($val)]);
                if ($key == "sign" || $key == "sign_type" || $val == "") continue;
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
        $data = $request->all();
        $order = $this->orderService->detailOrderSN($data['out_trade_no']);
        if (!$order) {
            return 'fail';
        }
        $payGateway = $this->payService->detail($order->pay_id);
        if (!$payGateway) {
            return 'fail';
        }
        if($payGateway->pay_handleroute != '/pay/ldc'){
            return 'fail';
        }
        ksort($data); //重新排序$data数组
        reset($data); //内部指针指向数组中的第一个元素
        $sign = '';
        foreach ($data as $key => $val) {
            if ($key == "sign" || $key == "sign_type" || $val == "") continue;
            if ($sign != '') {
                $sign .= "&";
            }
            $sign .= "$key=$val"; //拼接为url参数形式
        }
        if (!$data['trade_no'] || md5($sign . $payGateway->merchant_pem) != $data['sign']) { //不合法的数据
            return 'fail';  //返回失败 继续补单
        } else {
            //合法的数据
            //业务处理
            $this->orderProcessService->completedOrder($data['out_trade_no'], $data['money'], $data['trade_no']);
            return 'success';
        }
    }

    public function returnUrl(Request $request)
    {
        $oid = $request->get('order_id');
        // 休眠2秒等待异步通知处理完成
        sleep(2);
        return redirect(url('detail-order-sn', ['orderSN' => $oid]));
    }

}