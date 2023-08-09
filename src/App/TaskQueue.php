<?php

declare(strict_types=1);

namespace App;

use Laravel\SerializableClosure\SerializableClosure;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPIOException;
use PhpAmqpLib\Message\AMQPMessage;

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

        private readonly AMQPChannel $channel;
    
        public function __construct()
        {
            $this->name = 'Task';
            
            $this->channel = (function (): AMQPChannel {
                static $createConnection;
                
                $createConnection ??= static function() use (&$createConnection): AMQPStreamConnection {
                    static $tries = 0;
    
                    try {
                        $connection = new AMQPStreamConnection(
                            host: RabbitMQConfig()->host(), 
                            port: RabbitMQConfig()->port(), 
                            user: RabbitMQConfig()->user(), 
                            password: RabbitMQConfig()->password(),
                            keepalive: true
                        );
    
                        \register_shutdown_function(static function() use($connection) {
                            $connection->close();
                        });
    
                        return $connection;
                    }
                    catch (AMQPIOException $e) {
                        if (++$tries > 30) {
                            throw $e;
                        }
    
                        \usleep(1000000); // Wait 1 second before retry
    
                        return $createConnection();
                    }
    
                };

                $channel = $createConnection()->channel();
    
                /**
                 * RabbitMQ doesn't support prefetch-size
                 * https://github.com/php-amqplib/php-amqplib/issues/97#issuecomment-23236292
                 */
                $channel->basic_qos(0, 50, false);
                
                $channel->queue_declare(
                    queue: $this->name,
                    durable: true
                );
    
                return $channel;
            })();
        }
    
        public function addTask(
            \Closure $task
        ): void
        {
            $this->channel
                ->basic_publish(
                    msg: new AMQPMessage(
                        body: \serialize(new SerializableClosure($task)),
                        properties: [
                            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
                        ]
                    ),
                    routing_key: $this->name
                );
        }
    
        public function processIndefinitely(): void
        {
            $this->channel
                ->basic_consume(
                    queue: $this->name, 
                    callback: static function ($msg) {
                        $task = \unserialize($msg->getBody())->getClosure();
            
                        $task();
                        
                        DataMapper()->clear();

                        $msg->ack();
                    },
                    no_ack: false
                );
            
            while ($this->channel->is_open()) {
                $this->channel->wait();
            }
        }
    };

    return $taskQueue;
}