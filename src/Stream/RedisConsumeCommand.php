<?php

namespace shmurakami\ChatworkMentionWebhook\Stream;

use Redis;

class RedisConsumeCommand implements ConsumeCommand
{

    /**
     * @var string
     */
    private string $group;
    /**
     * @var string
     */
    private string $consumer;
    /**
     * @var array
     */
    private array $streams;
    /**
     * @var int
     */
    private int $count;
    /**
     * @var int
     */
    private int $pollingMilliseconds;

    public function __construct(string $group, string $consumer, array $streams, int $count = 10, int $pollingMilliseconds = 60 * 1000)
    {
        $this->group = $group;
        $this->consumer = $consumer;
        $this->streams = $streams;

        // override property with config
        $this->count = $count;
        $this->pollingMilliseconds = $pollingMilliseconds;
    }

    public function execute(Redis $redis)
    {
        return $redis->xReadGroup($this->group, $this->consumer, $this->streams, $this->count, $this->pollingMilliseconds);
    }

}
