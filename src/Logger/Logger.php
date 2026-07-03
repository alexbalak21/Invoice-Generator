<?php

/**
 * Logger
 *
 * Lightweight PSR-3-inspired file logger.
 * All levels write to logs/app.log.
 *
 * Usage:
 *   Logger::info('Document saved', ['id' => 42, 'type' => 'invoice']);
 *   Logger::error('Query failed', ['msg' => $e->getMessage()]);
 */
class Logger
{
    public const DEBUG   = 'DEBUG';
    public const INFO    = 'INFO';
    public const WARNING = 'WARNING';
    public const ERROR   = 'ERROR';

    /** Absolute path to the log file. Set once in bootstrap. */
    private static string $logFile = '';

    // -----------------------------------------------------------------------
    // Configuration
    // -----------------------------------------------------------------------

    public static function init(string $logFile): void
    {
        self::$logFile = $logFile;

        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    // -----------------------------------------------------------------------
    // Level helpers
    // -----------------------------------------------------------------------

    public static function debug(string $message, array $context = []): void
    {
        self::write(self::DEBUG, $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write(self::INFO, $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write(self::WARNING, $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write(self::ERROR, $message, $context);
    }

    // -----------------------------------------------------------------------
    // Core writer
    // -----------------------------------------------------------------------

    private static function write(string $level, string $message, array $context): void
    {
        if (self::$logFile === '') {
            // Fallback: not initialised yet, use PHP's built-in logger
            error_log("[{$level}] {$message}");
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $caller    = self::resolveCaller();
        $ctx       = $context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
        $line      = "[{$timestamp}] [{$level}] [{$caller}] {$message}{$ctx}" . PHP_EOL;

        file_put_contents(self::$logFile, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Walk up the call stack to find the first frame outside Logger itself.
     * Returns "ClassName::method" or "file.php" depending on context.
     */
    private static function resolveCaller(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);

        foreach ($trace as $frame) {
            $file  = $frame['file']  ?? '';
            $class = $frame['class'] ?? '';

            // Skip Logger frames
            if ($class === 'Logger' || str_ends_with($file, 'Logger.php')) {
                continue;
            }

            if ($class !== '') {
                return $class . '::' . ($frame['function'] ?? '?');
            }

            if ($file !== '') {
                return basename($file);
            }
        }

        return 'app';
    }
}
