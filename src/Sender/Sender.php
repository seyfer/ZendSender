<?php

namespace Sender;

use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Stdlib\Parameters;

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

    /**
     * url for send
     *
     * @var string
     */
    private $url;

    /**
     *
     * @var string
     */
    private $acceptType;

    /**
     * for default it's form
     *
     * @var string
     */
    private $contentType = EnumContentType::FORM;

    /**
     * you can set this before request
     *
     * @var array
     */
    private $extraHeaders = [];

    /**
     * @param array $extraHeaders
     * @return $this
     */
    public function setExtraHeaders($extraHeaders)
    {
        $this->extraHeaders = $extraHeaders;

        return $this;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    public function setAcceptType($acceptType)
    {
        $this->acceptType = $acceptType;

        return $this;
    }

    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAdapter('Zend\Http\Client\Adapter\Curl');
    }

    /**
     * set accept application/json
     *
     * @return \Sender\Sender
     */
    public function setAcceptJson()
    {
        $this->setAcceptType(EnumContentType::JSON);

        return $this;
    }

    /**
     * set content type application/json
     *
     * @return \Sender\Sender
     */
    public function setContentTypeJson()
    {
        $this->setContentType(EnumContentType::JSON);

        return $this;
    }

    /**
     * set form content type
     *
     * @return \Sender\Sender
     */
    public function setContentTypeForm()
    {
        $this->setContentType(EnumContentType::FORM);

        return $this;
    }

    /**
     *
     * @param string       $url
     * @param array|string $data
     * @return string
     * @throws \Exception
     */
    public function sendJson($url, $data)
    {
        $this->url = $url;
        $json      = json_encode($data);

        if ($this->contentType != EnumContentType::JSON) {
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
        $jsonRequest = $this->initRequest();
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
     * @param                               $url
     * @param \Zend\Stdlib\Parameters|array $post
     * @return string
     * @throws \Exception
     */
    public function sendPost($url, $post = [])
    {
        $this->url = $url;
        $post      = $this->prepareParameters($post);

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
                $post = new Parameters((array)$post);
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
        $postRequest = $this->initRequest();
        $postRequest->setMethod(Request::METHOD_POST);
        $postRequest->setPost($post);
        $postRequest->setUri($this->url);
        $postRequest->getHeaders()->addHeaders([
                                                   'Content-Type' => $this->contentType
                                               ]);

        return $postRequest;
    }

    /**
     * @return Request
     */
    private function initRequest()
    {
        $request = new Request();

        if ($this->extraHeaders) {
            $request->getHeaders()->addHeaders($this->extraHeaders);
        }

        return $request;
    }

    /**
     *
     * @param string $url
     * @param array  $query
     * @return string
     * @throws Exception
     * @throws \InfoClient\Exception
     */
    public function sendGet($url, $query = [])
    {
        $this->url = $url;
        $query     = $this->prepareParameters($query);

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
        $getRequest = $this->initRequest();
        $getRequest->setMethod(Request::METHOD_GET);
        $getRequest->setUri($this->url);

        $getRequest->setQuery($query);

        if ($this->acceptType) {
            $getRequest->getHeaders()->addHeaders([
                                                      "Accept" => $this->acceptType
                                                  ]);
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
