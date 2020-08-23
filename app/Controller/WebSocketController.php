<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    public function onMessage($server, Frame $frame): void
    {
      //  $server->push($frame->fd, 'Recv: ' . $frame->data);
        $recvData = json_decode($frame->data);
        if(!is_object($recvData)) {
            $this->checkData($frame->data,$frame->fd);
        } else {
            $this->sendData($server,$frame->data,$frame->fd);
        }
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
        var_dump('closed');
    }

    public function onOpen($server, Request $request): void
    {
        echo "线程：$request->fd-打开";
        //$server->push($request->fd, 'Opened');
    }

    /**
     * 校验数据
     * @param $string
     * @param $fd
     * @return string
     */
    function checkData ($string, $fd) {
        if (!is_string($string)) {
          //  $this->logger->error('字符串类型错误');
            return '字符串类型错误';
        }

        $strArray = explode('_',$string);
        $shopIds = json_decode($string[1],true);

        if(!is_array($shopIds) || empty($shopIds)) {
        //    $this->logger->error('参数错误');
            return '参数有误';
        }

        echo "全部映射成功";

   }


    /**
     * 发送消息到 PC 端
     */
    public function sendData($server,$sendData,$fd) {
        $recvData = json_encode($sendData,true);
        $uid = $recvData['uid'];
        $data = $recvData['data'];

       // $fdsArr = $this->redis->sMembers('jiayouwa:websocket:voiceSet_'.$uid);

     //   echo 'voiceSet_'.$uid;

        $data = [
            'result' => true,
            'code'=>0,
            'msg'=>'操作成功',
            'data'=>$data,
        ];
        $server->push(intval($fd),json_encode($data));


//        if(count($fdsArr)) {
//            foreach ($fdsArr as $key=>$value) {
//                try {
//                    $server->push(intval($value),json_encode($data));
//                    echo "线程：$fd 向线程 $value 发送信息\\n";
//                } catch (\Throwable $e) {
//                    // 增加 重试次数
//                    $this->service->push($recvData,1);
//                    // 把数据删除
//                  //  $this->redis->sRem('jiayouwa:websocket:voiceSet_'.$uid,$value);
//                    continue;
//                }
//            }
//        }
    }
}