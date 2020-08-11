<?php
declare(strict_types=1);

use shmurakami\ChatworkMentionWebhook\Modules\Consumer;
use shmurakami\ChatworkMentionWebhook\Modules\Record\Record;
use shmurakami\ChatworkMentionWebhook\Modules\Worker;
use shmurakami\ChatworkMentionWebhook\Repository\RedisStreamRepository;
use shmurakami\ChatworkMentionWebhook\Stream\RedisConsumeCommand;

require_once __DIR__ . '/../src/bootstrap.php';

$consumer = new Consumer();
$worker = new Worker();

$messageBufferLength = 1;
$consumerMultiplicity = 2;

$messageBufferChannel = new Swoole\Coroutine\Channel($messageBufferLength);

Co\run(function () use ($worker, $messageBufferChannel, $consumerMultiplicity) {
    $concurrencyCapChannel = new Co\Channel($consumerMultiplicity);

    while (true) {
        /** @var Record $values */
        $values = $messageBufferChannel->pop();

        $concurrencyCapChannel->push(true);
        go(function() use ($values, $worker, $concurrencyCapChannel) {
            $worker->execute($values);
            $concurrencyCapChannel->pop();
        });
    }
});

Co\run(function () use ($messageBufferChannel) {
    $redis = new Redis();
    // TODO replace with config class
    $redisHost = getenv('REDIS_HOST');
    $redisPort = getenv('REDIS_PORT');
    $connected = $redis->connect($redisHost, $redisPort);
    if (!$connected) {
        throw new RuntimeException('failed to connect to redis');
    }

    $group = getenv('REDIS_STREAM_GROUP');
    $consumer = getenv('REDIS_STREAM_CONSUMER');
    $topic = getenv('REDIS_STREAM_KEY');

    $repository = new RedisStreamRepository($redis);
    $consumeCommand = new RedisConsumeCommand($group, $consumer, [$topic => '>']);

    while (true) {
        $record = $repository->consume($consumeCommand, $topic);
        if ($record->isEmpty()) {
            continue;
        }
        $messageBufferChannel->push($record);
    }
});
