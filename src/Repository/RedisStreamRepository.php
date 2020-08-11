<?php
declare(strict_types=1);

namespace shmurakami\ChatworkMentionWebhook\Repository;

use Redis;
use shmurakami\ChatworkMentionWebhook\Modules\Record\RedisRecord;
use shmurakami\ChatworkMentionWebhook\Stream\RedisConsumeCommand;

class RedisStreamRepository implements StreamRepository
{
    /**
     * @var Redis
     */
    private Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function prepareTopic(string $topic, string $group): bool
    {
        $this->redis->xGroup('CREATE', $topic, $group, '>', $mkStream = true);
        return true;
    }

    /**
     * @return RedisRecord
     */
    public function consume(RedisConsumeCommand $command, string $topic): RedisRecord
    {
        $results = $command->execute($this->redis)[$topic] ?? [];
        return new RedisRecord($results);
    }
}
