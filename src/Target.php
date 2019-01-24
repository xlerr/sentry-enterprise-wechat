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
use yii\helpers\VarDumper;

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
     * @var int error msg max length, 0 does not process
     */
    public $msgMaxLength = 0;

    /**
     * @var mixed
     */
    public $exceptMatchMsg;

    /**
     * @var string
     */
    public static $exceptMsgPattern;

    /**
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\UserException
     */
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

        if (!empty($this->exceptMatchMsg)) {
            self::$exceptMsgPattern = sprintf('/%s/', implode('|', array_map('preg_quote', (array)$this->exceptMatchMsg)));
        }

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

    public function export()
    {
        $message = implode("\n", array_map([$this, 'formatMessage'], $this->messages));

        if (($msgMaxLength = (int)$this->msgMaxLength) > 0) {
            $message = substr($message, 0, $msgMaxLength);
        }

        $this->httpClient->post('WeWork/groupChat', [
            RequestOptions::FORM_PARAMS => [
                'chatId'   => $this->chatId,
                'message'  => $message,
                'ident'    => md5($message),
                'unitTime' => 30,
            ],
        ]);
    }

    /**
     * @param array $messages
     * @param int   $levels
     * @param array $categories
     * @param array $except
     *
     * @return array
     */
    public static function filterMessages($messages, $levels = 0, $categories = [], $except = [])
    {
        $messages = parent::filterMessages($messages, $levels, $categories, $except);

        if (empty(self::$exceptMsgPattern)) {
            return $messages;
        }

        $pattern = self::$exceptMsgPattern;

        return array_filter($messages, function ($message) use ($pattern) {
            list($text) = $message;
            if ($text instanceof \Throwable || $text instanceof \Exception) {
                $text = (string)$text;
            } else {
                $text = VarDumper::export($text);
            }

            return !preg_match($pattern, $text);
        });
    }
}
