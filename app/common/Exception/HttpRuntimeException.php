<?php
namespace Common\Exception;

use Phalcon\Http\Response;
use RuntimeException;

class HttpRuntimeException extends RuntimeException
{
    protected $headers = array();

    public function __construct($message, $code, $headers = null)
    {
        parent::__construct($message, $code);
        $headers and $this->headers = (array)$headers;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function toResponse()
    {
        $response = new Response($this->getMessage(), $this->getCode());
        $headers = $this->getHeaders();
        foreach ($headers as $name => $value) {
            if (is_numeric($name)) {
                list($name, $value) = explode(':', $value);
            }
            $response->setHeader($name, $value);
        }
        return $response;
    }
}
