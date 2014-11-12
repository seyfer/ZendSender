<?php

namespace Sender;

use Zend\Stdlib\Parameters;
use Zend\Http\Request,
    Zend\Http\Client;

/**
 * Description of Sender
 *
 * @author seyfer
 */
class Sender implements SenderInterface
{

    /**
     *
     * @var Client
     */
    private $client;
    private $url;
    private $acceptType;
    private $contentType = 'application/x-www-form-urlencoded; charset=UTF-8';

    public function setAcceptType($acceptType)
    {
        $this->acceptType = $acceptType;
    }

    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAdapter('Zend\Http\Client\Adapter\Curl');
    }

    public function setAcceptJson()
    {
        $this->setAcceptType("application/json");
        return $this;
    }

    public function setContentTypeJson()
    {
        $this->setContentType('application/json');
        return $this;
    }

    public function setContentTypeForm()
    {
        $this->setContentType('application/x-www-form-urlencoded; charset=UTF-8');
        return $this;
    }

    /**
     *
     * @param string $url
     * @param array|string $data
     * @return string
     * @throws \Exception
     */
    public function sendJson($url, $data)
    {
        $this->url = $url;
        $json      = json_encode($data);

        if ($this->contentType != 'application/json') {
            $this->setContentTypeJson();
        }

        $postRequest = $this->prepareJsonRequest($json);

        try {
            $response = $this->client->send($postRequest);
            $result   = $response->getBody();

            return $result;
        } catch (\Exception $exc) {
            throw $exc;
        }
    }

    /**
     *
     * @param string $json
     * @return \Zend\Http\Request
     */
    protected function prepareJsonRequest($json)
    {
        $jsonRequest = new Request();
        $jsonRequest->setMethod(Request::METHOD_POST);
        $jsonRequest->setContent($json);
        $jsonRequest->setUri($this->url);
        $jsonRequest->getHeaders()->addHeaders([
            'Content-Type' => $this->contentType,
            'Accept'       => $this->contentType,
        ]);

        return $jsonRequest;
    }

    /**
     *
     * @param \Zend\Stdlib\Parameters|array $post
     * @return string
     * @throws \Auth\Model\Exception
     */
    public function sendPost($url, $post = array())
    {
        $this->url = $url;

        $post = $this->prepareParameters($post);

        $postRequest = $this->preparePostRequest($post);

        try {
            $response = $this->client->send($postRequest);
            $result   = $response->getBody();

            return $result;
        } catch (\Exception $exc) {
            throw $exc;
        }
    }

    /**
     *
     * @param array|\Zend\Stdlib\Parameters $post
     * @return \Zend\Stdlib\Parameters
     */
    private function prepareParameters($post)
    {
        if (!$post instanceof Parameters) {
            if (is_array($post)) {
                $post = new Parameters($post);
            } else {
                $post = new Parameters((array) $post);
            }
        }

        return $post;
    }

    /**
     *
     * @param \Zend\Stdlib\Parameters $post
     * @return \Zend\Http\Request
     */
    private function preparePostRequest(Parameters $post)
    {
        $postRequest = new Request();
        $postRequest->setMethod(Request::METHOD_POST);
        $postRequest->setPost($post);
        $postRequest->setUri($this->url);
        $postRequest->getHeaders()->addHeaders([
            'Content-Type' => $this->contentType
        ]);

        return $postRequest;
    }

    /**
     *
     * @param string $url
     * @param array $query
     * @return string
     * @throws Exception
     * @throws \InfoClient\Exception
     */
    public function sendGet($url, $query = array())
    {
        $this->url = $url;

        $query = $this->prepareParameters($query);

        $getRequest = $this->prepareGetRequest($query);

        try {
            $response = $this->client->send($getRequest);
            $result   = $response->getBody();

            return $result;
        } catch (\Exception $exc) {
            throw $exc;
        }
    }

    /**
     *
     * @param \Zend\Stdlib\Parameters $query
     * @return \Zend\Http\Request
     */
    private function prepareGetRequest(Parameters $query)
    {
        $getRequest = new Request();
        $getRequest->setMethod(Request::METHOD_GET);
        $getRequest->setUri($this->url);

        $getRequest->setQuery($query);

        if ($this->acceptType) {
            $getRequest->getHeaders()->addHeaders(array(
                "Accept" => $this->acceptType
            ));
        }

        return $getRequest;
    }

    private function setQuery($query, $getRequest)
    {
        foreach ($query->toArray() as $key => $value) {
            $getRequest->getQuery()->set($key, $value);
        }

        return $getRequest;
    }

    private function debugUri($getRequest)
    {
        $uri = $getRequest->getUri();
        \Zend\Debug\Debug::dump($uri);
    }

}
