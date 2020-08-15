<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

/**
* @AutoController()
*/

class IndexController extends AbstractController
{
    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }

    public function multi(RequestInterface $request)
    {
	    $user = $this->request->input('user', 'zh');
	    $id = $this->request->input('id', '暂无');
	$method = $this->request->getMethod();
	return [
	   '你访问的方法' => $method,
	   '信息' => "你好 {$user}.你的id是.{$id}",
	 ];
    }
}
