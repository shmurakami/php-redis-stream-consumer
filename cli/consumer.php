<?php
declare(strict_types=1);

use shmurakami\ChatworkMentionWebhook\Modules\Record\Record;
use shmurakami\ChatworkMentionWebhook\Modules\RedisConsumer;
use shmurakami\ChatworkMentionWebhook\Modules\Worker;

require_once __DIR__ . '/../src/bootstrap.php';

$messageBufferLength = 1;
$consumerMultiplicity = 2;

$messageBufferChannel = new Swoole\Coroutine\Channel($messageBufferLength);

Co\run(function () use ($messageBufferChannel, $consumerMultiplicity) {
    $concurrencyCapChannel = new Co\Channel($consumerMultiplicity);

    while (true) {
        /** @var Record $values */
        $values = $messageBufferChannel->pop();

        $concurrencyCapChannel->push(true);
        go(function() use ($values, $concurrencyCapChannel) {
            $worker = new Worker();
            $worker->execute($values);
            $concurrencyCapChannel->pop();
        });
    }
});

Co\run(function () use ($messageBufferChannel) {
    $consumer = new RedisConsumer();

    while (true) {
        $record = $consumer->consume();
        if ($record->isEmpty()) {
            continue;
        }
        $messageBufferChannel->push($record);
    }
});
