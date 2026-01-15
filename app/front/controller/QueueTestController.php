<?php

namespace app\front\controller;

use app\attribute\dependency\Autowired;
use app\attribute\routing\Route;
use app\job\{ExportDataJob, GenerateReportJob, ProcessImageJob, SendEmailJob};
use app\support\Queue;
use support\Request;

class QueueTestController
{
    #[Autowired]
    private Queue $queue;
    
    #[Route('POST', '/queue/email', 'queue.email')]
    public function dispatchEmail(Request $request)
    {
        $jobId = $this->queue->push(SendEmailJob::class, [
            'to' => $request->input('to', 'user@example.com'),
            'subject' => $request->input('subject', 'Test Email'),
            'body' => $request->input('body', 'This is a test email from queue system')
        ]);
        
        return json([
            'message' => 'Email job dispatched to queue',
            'job_id' => $jobId
        ]);
    }
    
    #[Route('POST', '/queue/image', 'queue.image')]
    public function processImage(Request $request)
    {
        $jobId = $this->queue->push(ProcessImageJob::class, [
            'path' => $request->input('path', '/uploads/test.jpg'),
            'operations' => ['resize', 'compress', 'watermark']
        ], 'images');
        
        return json([
            'message' => 'Image processing job dispatched',
            'job_id' => $jobId
        ]);
    }
    
    #[Route('POST', '/queue/report', 'queue.report')]
    public function generateReport(Request $request)
    {
        $delay = $request->input('delay', 0);
        
        $jobId = $this->queue->push(
            GenerateReportJob::class,
            [
                'user_id' => $request->input('user_id', 1),
                'type' => $request->input('type', 'monthly'),
                'start_date' => date('Y-m-01'),
                'end_date' => date('Y-m-t')
            ],
            'reports',
            $delay
        );
        
        return json([
            'message' => $delay > 0 ? "Report generation scheduled in {$delay} seconds" : 'Report generation dispatched',
            'job_id' => $jobId
        ]);
    }
    
    #[Route('POST', '/queue/export', 'queue.export')]
    public function exportData(Request $request)
    {
        $jobId = $this->queue->push(ExportDataJob::class, [
            'user_id' => $request->input('user_id', 1),
            'format' => $request->input('format', 'csv'),
            'filters' => $request->input('filters', [])
        ], 'exports');
        
        return json([
            'message' => 'Data export job dispatched',
            'job_id' => $jobId
        ]);
    }
    
    #[Route('GET', '/queue/job/{id}', 'queue.job.status')]
    public function jobStatus(Request $request, $id)
    {
        $job = $this->queue->getJob($id);
        
        if (!$job) {
            return json(['error' => 'Job not found'], 404);
        }
        
        return json([
            'job' => $job
        ]);
    }
    
    #[Route('GET', '/queue/{name}/stats', 'queue.stats')]
    public function queueStats(Request $request, $name)
    {
        $stats = $this->queue->stats($name);
        
        return json([
            'queue' => $name,
            'stats' => $stats
        ]);
    }
    
    #[Route('POST', '/queue/{name}/flush', 'queue.flush')]
    public function flushQueue(Request $request, $name)
    {
        $this->queue->flush($name);
        
        return json([
            'message' => "Queue '{$name}' flushed successfully"
        ]);
    }
    
    #[Route('POST', '/queue/batch', 'queue.batch')]
    public function batchJobs(Request $request)
    {
        $jobIds = [];
        
        // 批量分发多个任务
        for ($i = 1; $i <= 5; $i++) {
            $jobIds[] = $this->queue->push(SendEmailJob::class, [
                'to' => "user{$i}@example.com",
                'subject' => "Batch Email #{$i}",
                'body' => "This is batch email number {$i}"
            ]);
        }
        
        return json([
            'message' => 'Batch jobs dispatched',
            'job_ids' => $jobIds,
            'count' => count($jobIds)
        ]);
    }
}
