<?php
namespace Common;
use Exception;
use Common\Exception\NotFoundException;
use Phalcon\Di;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File;
use Phalcon\Logger\Formatter\Line;

class Log extends Logger
{
    /**
     * @var \Phalcon\Logger\Adapter|File
     */
    protected static $logger;
    protected static $hostname;

    public static function debug($message = null, array $context = null)
    {
        static::log(static::DEBUG, $message, $context);
    }

    public static function error($message = null, array $context = null)
    {
        static::log(static::ERROR, $message, $context);
    }

    public static function info($message = null, array $context = null)
    {
        static::log(static::INFO, $message, $context);
    }

    public static function log($type, $message = null, array $context = null)
    {
        static::$logger or static::$logger = Di::getDefault()->getShared('log');
        $context['host'] = static::$hostname;
        $context['request'] = fnGet($_SERVER, 'REQUEST_METHOD') . ' ' . fnGet($_SERVER, 'REQUEST_URI');
        static::$logger->log($type, $message, $context);
    }

    public static function exception(Exception $e)
    {
        static::error($e instanceof NotFoundException ? ' 404 Not Found' : "\n" . $e->__toString());
    }

    public static function register(Di $di)
    {
        static::$hostname = gethostname();
        $di->setShared('log', function () {
            $filePath = storagePath('logs');
            if (!is_dir($filePath)) {
                mkdir($filePath, 0777, true);
            }
            $filePath .= '/' . Config::get('app.log.file');
            $logger = new File($filePath);
            $formatter = $logger->getFormatter();
            if ($formatter instanceof Line) {
                $formatter->setDateFormat('Y-m-d H:i:s');
                $formatter->setFormat('[%date%]{host}[%type%] {request} %message%');
            }
            return $logger;
        });
    }
}
