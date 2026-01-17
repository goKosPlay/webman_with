<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\attribute\routing\Route;
use app\attribute\dependency\Autowired;
use app\example\ExampleAppConfig;
use support\Db;
use support\Request;

class IndexController
{
    #[Autowired]
    private ExampleAppConfig $config;
    
    #[Route('GET', '/admin', 'admin.index')]
    public function index(Request $request)
    {
//        $k = 'db'
//                |> strtolower(...)
//                |> (fn($str) => str_replace(' ', '-', $str))
//                |> strtoupper(...);

        $data = Db::table("sa_article")->paginate();
//        return json(['msg' => $data, ]);
//        Coroutine::create(function(){
//            Timer::sleep(1.5);
//            echo "hello coroutine\n";
//        });
//        return response('hello webman');
        return json(['code' => 0, 'msg' => 'ok', 'data'=>$data, 'c'=>$this->config->getDebug()]);
    }

    #[Route('GET', '/admin/view/{name}', 'admin.view')]
    public function view(Request $request)
    {
        return view('index/view', ['name' => 'webman']);
    }

    #[Route(['GET', 'POST'], '/admin/api/data', 'admin.json')]
    public function json(Request $request)
    {
        return json(['code' => 0, 'msg' => 'ok']);
    }

}
