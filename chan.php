<?php

$messageBufferChannel = new Swoole\Coroutine\Channel(3);

Co\run(function () use ($messageBufferChannel) {
    $concurrencyCapChannel = new Co\Channel(5);

    while (true) {
        $values = $messageBufferChannel->pop();
        if ($values) {
            $concurrencyCapChannel->push(true);
            go(function() use ($values, $concurrencyCapChannel) {
                echo "got values " . count($values['mystream']) . "\n";
                co::sleep(3);
                $concurrencyCapChannel->pop();
            });
        }
    }
});

Co\run(function () use ($messageBufferChannel) {
    $redis = new Redis();
    $redis->connect('redis-server', 6379);

    while (true) {
        $values = $redis->xReadGroup('group1', 'consumer10', ['mystream' => '>'], $count = 10, $block = 2000);
        if ($values) {
            $messageBufferChannel->push($values);
        }
    }
});
