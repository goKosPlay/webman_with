<?php

namespace app\example;

use app\attribute\dependency\{Service, Autowired, Lazy};
use app\attribute\schedule\Scheduled;
use app\attribute\transaction\Transactional;
use app\attribute\async\Async;
use app\attribute\event\EventListener;
use app\attribute\cache\{Cacheable, CacheEvict};

#[Service(singleton: true)]
class ExampleUserService
{
    #[Autowired]
    private ExampleUserRepository $repository;
    
    #[Lazy]
    private ExampleEmailService $emailService;
    
    #[Lazy]
    private ExampleNotificationService $notificationService;
    
    #[Transactional]
    public function create($data)
    {
        $user = $this->repository->create($data);
        $this->sendWelcomeEmail($user);
        $this->notifyAdmins($user);
        return $user;
    }
    
    #[Cacheable(key: 'user:{id}', ttl: 3600)]
    public function findById($id)
    {
        return $this->repository->findById($id);
    }
    
    #[Transactional]
    #[CacheEvict(key: 'user:{id}')]
    public function update($id, $data)
    {
        return $this->repository->update($id, $data);
    }
    
    #[Transactional]
    #[CacheEvict(key: 'user:{id}')]
    public function delete($id)
    {
        return $this->repository->delete($id);
    }
    
    public function paginate($page = 1, $perPage = 20)
    {
        return $this->repository->paginate($page, $perPage);
    }
    
    #[Async]
    private function sendWelcomeEmail($user)
    {
        $this->emailService->send($user->email, 'Welcome!', [
            'name' => $user->name
        ]);
    }
    
    #[Async]
    private function notifyAdmins($user)
    {
        $this->notificationService->notifyAdmins('New user registered: ' . $user->email);
    }
    
    #[Scheduled(cron: '0 2 * * *', timeZone: 'Asia/Shanghai')]
    public function cleanupInactiveUsers()
    {
        $this->repository->deleteInactive(30);
    }
    
    #[Scheduled(fixedRate: 3600000)]
    public function syncUserStats()
    {
        $this->repository->updateStatistics();
    }
    
    #[EventListener(events: 'user.login', priority: 10)]
    public function onUserLogin($event)
    {
        $this->repository->updateLastLogin($event->userId);
    }
    
    #[EventListener(events: ['user.updated', 'user.deleted'])]
    public function onUserChanged($event)
    {
        $this->clearUserCache($event->userId);
    }
    
    #[CacheEvict(key: 'user:{userId}')]
    private function clearUserCache($userId)
    {
    }
}
