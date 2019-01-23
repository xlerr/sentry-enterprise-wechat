<?php

namespace xlerr\sentry\ewechat;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\RequestOptions;
use kvmanager\models\KeyValue;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Yii;
use yii\base\InvalidConfigException;

class Target extends \yii\log\Target
{
    /**
     * @var null|string|array
     */
    public $config;

    /**
     * @var \GuzzleHttp\Client|\GuzzleHttp\ClientInterface
     */
    public $httpClient;

    /**
     * @var array
     */
    public $logVars = [];

    /**
     * @var array http client options
     */
    public $httpClientOptions = [
        RequestOptions::HTTP_ERRORS => false,
    ];

    /**
     * @var string host
     */
    public $host;

    /**
     * @var string chat ID
     */
    public $chatId;

    /**
     * @var int error msg max length
     */
    public $msgMaxLength = 500;

    public function init()
    {
        if (is_string($this->config)) {
            $this->config = KeyValue::getValueAsArray($this->config, false);
        }

        if (null === $this->config) {
            return $this->setEnabled(false);
        }

        if (!is_array($this->config)) {
            throw new InvalidConfigException(__CLASS__ . '::$config can only be an Array or String, NULL is disabled.');
        }
        Yii::configure($this, $this->config);

        if (empty($this->chatId)) {
            throw new InvalidConfigException(__CLASS__ . '::$chatId cannot be empty!');
        }

        if (null === $this->httpClient) {
            if (empty($this->host)) {
                throw new InvalidConfigException(__CLASS__ . '::$host cannot be empty!');
            }

            $options = array_merge($this->httpClientOptions, [
                'handler'  => $this->getHandlerStack(),
                'base_uri' => rtrim($this->host, '/') . '/',
            ]);

            $this->httpClient = new Client($options);
        }
    }

    /**
     * @return \GuzzleHttp\HandlerStack
     */
    protected function getHandlerStack()
    {
        $stack = HandlerStack::create();

        if (YII_DEBUG) {
            // 记录请求信息
            $stack->push(Middleware::mapRequest(function (RequestInterface $request) {
                Yii::debug([
                    'url'     => (string)$request->getUri(),
                    'method'  => $request->getMethod(),
                    'headers' => $request->getHeaders(),
                    'body'    => (string)$request->getBody(),
                ], __CLASS__ . ':requestMiddleware');

                return $request;
            }));

            // 记录响应信息
            $stack->push(Middleware::mapResponse(function (ResponseInterface $response) {
                Yii::debug([
                    'statusCode' => $response->getStatusCode(),
                    'headers'    => $response->getHeaders(),
                    'body'       => (string)$response->getBody(),
                ], __CLASS__ . ':responseMiddleware');

                return $response;
            }));
        }

        return $stack;
    }

    /**
     * 发送消息
     *
     * @param string $message 消息内容
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function send($message)
    {
        return $this->httpClient->post('WeWork/groupChat', [
            RequestOptions::FORM_PARAMS => [
                'chatId'   => $this->chatId,
                'message'  => $message,
                'ident'    => md5($message),
                'unitTime' => 30,
            ],
        ]);
    }

    public function export()
    {
        $message = implode("\n", array_map([$this, 'formatMessage'], $this->messages));
        $this->send(substr($message,0, $this->msgMaxLength));
    }
}
