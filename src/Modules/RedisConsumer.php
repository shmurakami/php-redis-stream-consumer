<?php
declare(strict_types=1);

namespace shmurakami\ChatworkMentionWebhook\Modules;

use Redis;
use RuntimeException;
use shmurakami\ChatworkMentionWebhook\Modules\Record\RedisRecord;
use shmurakami\ChatworkMentionWebhook\Repository\RedisStreamRepository;
use shmurakami\ChatworkMentionWebhook\Stream\RedisConsumeCommand;

class RedisConsumer
{
    /**
     * @var array|false|string
     */
    private $group;
    /**
     * @var array|false|string
     */
    private $consumer;
    /**
     * @var array|false|string
     */
    private $topic;
    /**
     * @var RedisStreamRepository
     */
    private RedisStreamRepository $repository;

    public function __construct()
    {
        $redis = new Redis();
        // TODO replace with config class
        $redisHost = getenv('REDIS_HOST');
        $redisPort = getenv('REDIS_PORT');
        $connected = $redis->connect($redisHost, $redisPort);
        if (!$connected) {
            throw new RuntimeException('failed to connect to redis');
        }

        $this->group = getenv('REDIS_STREAM_GROUP');
        $this->consumer = getenv('REDIS_STREAM_CONSUMER');
        $this->topic = getenv('REDIS_STREAM_KEY');

        $this->repository = new RedisStreamRepository($redis);

        $this->ensureTopic($this->topic, $this->group);
    }

    private function ensureTopic(string $topic, string $group)
    {
        $this->repository->prepareTopic($topic, $group);
    }

    public function consume(): RedisRecord
    {
        $consumeCommand = new RedisConsumeCommand($this->group, $this->consumer, [$this->topic => '>']);
        return $this->repository->consume($consumeCommand, $this->topic);
    }
}
