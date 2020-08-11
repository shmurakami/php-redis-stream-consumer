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
    private int $count = 10;
    /**
     * @var int
     */
    private int $pollingMilliseconds = 60000;

    public function __construct(string $group, string $consumer, array $streams)
    {
        $this->group = $group;
        $this->consumer = $consumer;
        $this->streams = $streams;

        // override property with config
    }

    public function execute(Redis $redis)
    {
        return $redis->xReadGroup($this->group, $this->consumer, $this->streams, $this->count, $this->pollingMilliseconds);
    }

}
