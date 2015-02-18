<?php

namespace Sender;

/**
 *
 * @author seyfer
 */
interface SenderInterface
{

    public function setAcceptType($acceptType);

    public function setContentType($contentType);

    public function setAcceptJson();

    public function setContentTypeForm();

    public function setContentTypeJson();

    public function sendPost($url, $post = []);

    public function sendGet($url, $query = []);

    public function sendJson($url, $data);
}
