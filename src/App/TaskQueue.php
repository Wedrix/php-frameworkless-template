<?php

declare(strict_types=1);

namespace App;

use Laravel\SerializableClosure\SerializableClosure;

interface TaskQueue
{
    public function addTask(
        \Closure $task
    ): void;

    public function processIndefinitely(): void;
}

function TaskQueue(): TaskQueue
{
    static $taskQueue;
    
    $taskQueue ??= new class() implements TaskQueue {
        private readonly string $name;

        private readonly \AMQPExchange $exchange;

        private readonly \AMQPQueue $queue;
    
        public function __construct()
        {
            $connection = new \AMQPConnection([
                'host' => Config()->rabbitMQHost(),
                'port' => Config()->rabbitMQPort(), 
                'login' => Config()->rabbitMQUser(), 
                'password' => Config()->rabbitMQPassword()
            ]);

            \register_shutdown_function(static function() use($connection) {
                $connection->pdisconnect();
            });

            $connection->pconnect();

            $channel = new \AMQPChannel($connection);

            $name = 'Tasks';

            $queue = new \AMQPQueue($channel);
            $queue->setName($name);
            $queue->declareQueue();

            $exchange = new \AMQPExchange($channel);

            $this->name = $name;

            $this->queue = $queue;

            $this->exchange = $exchange;
        }
    
        public function addTask(
            \Closure $task
        ): void
        {
            $this->exchange
                ->publish(
                    message: \serialize(new SerializableClosure($task)),
                    routingKey: $this->name
                );
        }
    
        public function processIndefinitely(): void
        {
            $this->queue
                ->consume(
                    callback: static function (\AMQPEnvelope $message, \AMQPQueue $queue) {
                        $task = \unserialize($message->getBody())->getClosure();
            
                        $task();

                        $queue->ack(
                            deliveryTag: $message->getDeliveryTag()
                        );
                    }
                );
        }
    };

    return $taskQueue;
}