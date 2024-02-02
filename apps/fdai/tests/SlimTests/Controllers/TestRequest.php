<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Controllers;

use OAuth2\RequestInterface;


class TestRequest implements RequestInterface
{
    public $query, $request, $server, $headers;

    public function __construct()
    {
        $this->query = $_GET;
        $this->request = $_POST;
        $this->server  = $_SERVER;
    }
    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function query($name, $default = null)
    {
        return isset($this->query[$name]) ? $this->query[$name] : $default;
    }
    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function request($name, $default = null)
    {
        return isset($this->request[$name]) ? $this->request[$name] : $default;
    }
    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function server($name, $default = null)
    {
        return isset($this->server[$name]) ? $this->server[$name] : $default;
    }
    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function headers($name, $default = null)
    {
        return isset($this->headers[$name]) ? $this->headers[$name] : $default;
    }
    /**
     * @return mixed
     */
    public function getAllQueryParameters()
    {
        return $this->query;
    }
    /**
     * @param array $query
     */
    public function setQuery(array $query)
    {
        $this->query = $query;
    }
    /**
     * @param array $params
     */
    public function setPost(array $params)
    {
        $this->server['REQUEST_METHOD'] = 'POST';
        $this->request = $params;
    }
    /**
     * @param array $params
     * @return TestRequest
     */
    public static function createPost(array $params = [])
    {
        $request = new self();
        $request->setPost($params);

        return $request;
    }
}
