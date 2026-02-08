<?php

/**
 * USSD Logger
 * 
 * Structured logging for USSD operations
 */
class UssdLogger
{
    private string $logFile;
    private string $sessionId;
    
    public function __construct(string $sessionId = '')
    {
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $this->logFile = $logDir . '/ussd_' . date('Y-m-d') . '.log';
        $this->sessionId = $sessionId;
    }
    
    /**
     * Set session ID for logging context
     */
    public function setSessionId(string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }
    
    /**
     * Log an info message
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }
    
    /**
     * Log an error message
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }
    
    /**
     * Log a warning message
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }
    
    /**
     * Log a debug message
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }
    
    /**
     * Log incoming USSD request
     */
    public function logRequest(array $data): void
    {
        $this->info('USSD Request received', [
            'sessionID' => $data['sessionID'] ?? 'unknown',
            'msisdn' => $this->maskPhone($data['msisdn'] ?? ''),
            'userData' => $data['userData'] ?? '',
            'network' => $data['network'] ?? '',
            'newSession' => $data['newSession'] ?? false,
        ]);
    }
    
    /**
     * Log outgoing USSD response
     */
    public function logResponse(array $response): void
    {
        $this->info('USSD Response sent', [
            'continueSession' => $response['continueSession'] ?? false,
            'messagePreview' => substr($response['message'] ?? '', 0, 50) . '...',
        ]);
    }
    
    /**
     * Core logging function
     */
    private function log(string $level, string $message, array $context = []): void
    {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'session_id' => $this->sessionId,
            'message' => $message,
        ];
        
        if (!empty($context)) {
            $entry['context'] = $context;
        }
        
        $line = json_encode($entry) . PHP_EOL;
        file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Mask phone number for privacy
     */
    private function maskPhone(string $phone): string
    {
        if (strlen($phone) < 6) {
            return '***';
        }
        return substr($phone, 0, 3) . '****' . substr($phone, -3);
    }
}
