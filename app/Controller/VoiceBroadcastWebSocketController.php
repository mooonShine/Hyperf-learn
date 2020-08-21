<?php
declare(strict_types=1);
namespace App\Controller\WebSocket;
use App\Service\SendWebSocketQueueService;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Validation\ValidationException;
use Swoole\Exception;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;
use App\Exception\WebSocketException;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Di\Annotation\Inject;
class VoiceBroadcastWebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    protected $redis;
    /**
     * @Inject
     * @var SendWebSocketQueueService
     */
    protected $service;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
// 初始化
    public function __construct(LoggerFactory $loggerFactory)
    {
        $container = ApplicationContext::getContainer();
        $this->redis = $container->get(Redis::class);
        $this->logger = $loggerFactory->get('log','default');
    }
// onmessage 方法接收 客户端或者服务端消息
    public function onMessage(WebSocketServer $server, Frame $frame): void
    {
        $recvData = json_decode($frame->data);
        if(!is_object($recvData)) {
            $this->checkData($frame->data,$frame->fd);
        } else {
            $this->sendData($server,$frame->data,$frame->fd);
        }
    }
    /**
     * 校验数据
     * @param $string
     * @param $fd
     * @return string
     */
    function checkData ($string, $fd) {
        if (!is_string($string)) {
            $this->logger->error('字符串类型错误');
            return '字符串类型错误';
        }
        $strArray = explode('_',$string);
        $shopIds = json_decode($string[1],true);
        if(!is_array($shopIds) || empty($shopIds)) {
            $this->logger->error('参数错误');
            return '参数有误';
        }
        echo "全部映射成功"
}
    /**
     * 发送消息到 PC 端
     */
    public function sendData($server,$sendData,$fd) {
        $recvData = json_encode($sendData,true);
        $uid = $recvData['uid'];
        $data = $recvData['data'];
        $fdsArr = $this->redis->sMembers('jiayouwa:websocket:voiceSet_'.$uid);
        echo 'voiceSet_'.$uid;
        $data = [
            'result' => true,
            'code'=>0,
            'msg'=>'操作成功',
            'data'=>$data,
        ];
        if(count($fdsArr)) {
            foreach ($fdsArr as $key=>$value) {
                try {
                    $server->push(intval($value),json_encode($data));
                    echo "线程：$fd 向线程 $value 发送信息\n";
                } catch (\Throwable $e) {
// 增加 重试次数
                    $this->service->push($recvData,1);
// 把数据删除
                    $this->redis->sRem('jiayouwa:websocket:voiceSet_'.$uid,$value);
                    continue;
                }
            }}
    }
    public function onClose(Server $server, int $fd, int $reactorId): void
    {
        echo "$fd-closed\n";
    }
    public function onOpen(WebSocketServer $server, Request $request): void
    {
        echo "线程：$request->fd-打开\n";
    }
}