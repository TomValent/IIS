<?php
/* modified, original from https://code.tutsplus.com/tutorials/how-to-build-a-simple-rest-api-in-php--cms-37000 */

class MethodException extends Exception
{
	private string $status;

	public function __construct($message, $status = "HTTP/1.1 400 Bad Request", $code = 0, Throwable $previous = null)
	{
		$this->status = $status;
		parent::__construct($message, $code, $previous);
	}

	public function getStatus(): string
	{
		return $this->status;
	}
}

class BaseController
{
    /**
     * __call magic method.
     */
    public function __call($name, $arguments): void
    {
        $this->sendOutput('', array('HTTP/1.1 404 Not Found'));
    }

    /**
     * Get URI elements.
     *
     * @return array
     */
    protected function getUriSegments(): array
	{
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		return explode( '/', $uri );
    }

    /**
     * Get querystring params.
     *
     * @return array
     */
    protected function getQueryStringParams(): array
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
	 * @param mixed $data
	 * @param array $httpHeaders
	 */
    private function sendOutput($data, $httpHeaders=array()): void
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

	private function sendError($message, $status): void
	{
		$this->sendOutput(array('error' => $message), array($status));
	}

	public function invoke($method): void
	{
		try {
			$data = $this->{$method}();
			$this->sendOutput($data, array('HTTP/1.1 200 OK'));
		}
		catch (MethodException $e) {
			error_log("error: ". $e->getMessage());
			$this->sendError($e->getMessage(), $e->getStatus());
		}
		catch(PDOException $e){
			error_log("db error: ". $e->getMessage());
			$this->sendError('Database error', 'HTTP/1.1 500 Internal Server Error');
		}
		catch (Throwable $e) {
			 error_log("error: ". $e->getMessage());
			 $this->sendError('Internal server error', 'HTTP/1.1 500 Internal Server Error');
		}
	}

	/**
	 * @throws MethodException
	 */
	protected function checkRequestMethod($method): void
	{
		$requestMethod = $_SERVER["REQUEST_METHOD"];
		if (strtoupper($requestMethod) != $method) {
			throw new MethodException('Method not supported', 'HTTP/1.1 422 Unprocessable Entity');
		}
	}

	/**
	 * @throws MethodException
	 */
	protected function get($from, $field)
	{
		if (!isset($from[$field])) {
			throw new MethodException("Missing GET/POST data");
		}
		return $from[$field];
	}

	/**
	 * @throws MethodException
	 */
	protected function checkLoggedIn(): void
	{
		$user_id = getUserID();
		if ($user_id === NULL) {
			throw new MethodException("Missing user info");
		}
	}

	/**
	 * @throws MethodException
	 */
	protected function getLoggedUserID()
	{
		$this->checkLoggedIn();
		return getUserID();
	}

	/**
	 * @throws MethodException
	 */
	protected function checkLoggedInAdmin(): void
	{
		if (!isset($_SESSION["isAdmin"]) || !$_SESSION["isAdmin"]) {
			throw new MethodException("Must be admin");
		}
	}

	/**
	 * @throws MethodException
	 */
	protected function getMatch($id, $tournament_id)
	{
		$match = Database::getInstance()->getMatchByID($id, $tournament_id);
		if (!$match) {
			throw new MethodException("Match does not exist");
		}
		return $match;
	}

}
