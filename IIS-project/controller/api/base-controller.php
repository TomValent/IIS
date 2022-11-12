<?php
/* modified, original from https://code.tutsplus.com/tutorials/how-to-build-a-simple-rest-api-in-php--cms-37000 */

class MethodException extends Exception
{
	private $status;

	public function __construct($message, $status = "HTTP/1.1 400 Bad Request", $code = 0, Throwable $previous = null)
	{
		$this->status = $status;
		parent::__construct($message, $code, $previous);
	}

	public function getStatus()
	{
		return $this->status;
	}
}

class BaseController
{
    /**
     * __call magic method.
     */
    public function __call($name, $arguments)
    {
        $this->sendOutput('', array('HTTP/1.1 404 Not Found'));
    }

    /**
     * Get URI elements.
     *
     * @return array
     */
    protected function getUriSegments()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode( '/', $uri );

        return $uri;
    }

    /**
     * Get querystring params.
     *
     * @return array
     */
    protected function getQueryStringParams()
    {
        if (array_key_exists('QUERY_STRING', $_SERVER)) {
            parse_str($_SERVER['QUERY_STRING'], $query);
            return $query;
        }
        return array();
    }

    /**
     * Send API output.
     *
     * @param mixed  $data
     * @param string $httpHeader
     */
    private function sendOutput($data, $httpHeaders=array())
    {
        header_remove('Set-Cookie');
		header('Content-Type: application/json');
        if (is_array($httpHeaders) && count($httpHeaders)) {
            foreach ($httpHeaders as $httpHeader) {
                header($httpHeader);
            }
        }
		echo json_encode($data);
        exit;
    }

	private function sendError($message, $status) {
		$this->sendOutput(array('error' => $message), array($status));
	}

	public function invoke($method)
	{
		try {
			$data = $this->{$method}();

			$this->sendOutput($data, array('HTTP/1.1 200 OK'));
		}
		catch (MethodException $e) {
			$this->sendError($e->getMessage(), $e->getStatus());
		}
		catch (Error $e) {
			// $msg = $e->getMessage(); optional info
			$this->sendError('Something went wrong! Please contact support.', 'HTTP/1.1 500 Internal Server Error');
		}
	}

	protected function checkRequestMethod($method)
	{
		$requestMethod = $_SERVER["REQUEST_METHOD"];
		if (strtoupper($requestMethod) != $method) {
			throw new MethodException('Method not supported', 'HTTP/1.1 422 Unprocessable Entity');
		}
	}
}
