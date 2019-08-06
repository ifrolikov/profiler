<?php
declare(strict_types=1);

namespace Profiler;

use Psr\Log\LoggerInterface;

class Profiler
{
    private static $instance;
    
    private $tree = [];
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    public static function instance(LoggerInterface $logger = null): Profiler
    {
        if (empty(static::$instance)) {
            static::$instance = new Profiler($logger);
        }
        
        return static::$instance;
    }
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function add(string $key): void
    {
        $start = microtime(true);
        $fullKey = [];
        foreach ($this->tree as $element) {
            if (empty($element['stop'])) {
                $fullKey[] = $element['key'];
            }
        }
        $fullKey[] = $key;
        $this->tree[] = [
            'key'     => $key,
            'fullKey' => implode('.', $fullKey),
            'start'   => $start
        ];
    }
    
    public function stop(): string
    {
        $time = microtime(true);
        $index = count($this->tree) - 1;
        $this->tree[$index]['stop'] = $time;
        return $this->tree[$index]['fullKey'];
    }
    
    public function log()
    {
        $log = [];
        $stopTime = microtime(true);
        foreach ($this->tree as $element) {
            $elementStopTime = $element['stop'] ?? $stopTime;
            $log[] = [
                'key'      => $element['fullKey'],
                'duration' => round(100 * ($elementStopTime - $element['start']), 3)
            ];
        }
        $this->logger->debug(json_encode($log, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
        $this->tree = [];
    }
    
    public function __destruct()
    {
        $this->log();
    }
}
