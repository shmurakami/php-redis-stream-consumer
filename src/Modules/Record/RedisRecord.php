<?php

namespace shmurakami\ChatworkMentionWebhook\Modules\Record;

class RedisRecord implements Record
{
    private array $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function isEmpty(): bool
    {
        return (bool)$this->values;
    }

    public function toArray(): array
    {
        return $this->values;
    }
}
