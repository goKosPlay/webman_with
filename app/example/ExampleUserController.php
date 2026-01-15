<?php

namespace app\example;

use app\attribute\dependency\{RestController, Autowired, Lazy};
use app\attribute\routing\{GetMapping, PostMapping, PutMapping, DeleteMapping};
use app\attribute\cache\{Cacheable, CacheEvict};
use app\attribute\web\{Validated, Middleware};

#[RestController(prefix: '/api/users', middleware: ['auth'])]
class ExampleUserController
{
    #[Autowired]
    private ExampleUserService $userService;
    
    #[Lazy]
    private ExampleCacheService $cache;
    
    #[GetMapping(path: '', name: 'users.index')]
    #[Cacheable(key: 'users:list:{page}', ttl: 600)]
    public function index($page = 1)
    {
        return $this->userService->paginate($page);
    }
    
    #[GetMapping(path: '/{id}', name: 'users.show')]
    #[Cacheable(key: 'user:{id}', ttl: 3600)]
    public function show($id)
    {
        return $this->userService->findById($id);
    }
    
    #[PostMapping(path: '', name: 'users.store')]
    #[Validated(groups: ['create'])]
    #[Middleware(middleware: 'throttle:10,1')]
    #[CacheEvict(allEntries: true)]
    public function store($request)
    {
        return $this->userService->create($request->all());
    }
    
    #[PutMapping(path: '/{id}', name: 'users.update')]
    #[CacheEvict(key: 'user:{id}')]
    public function update($id, $request)
    {
        return $this->userService->update($id, $request->all());
    }
    
    #[DeleteMapping(path: '/{id}', name: 'users.destroy')]
    #[CacheEvict(key: 'user:{id}')]
    public function destroy($id)
    {
        return $this->userService->delete($id);
    }
}
