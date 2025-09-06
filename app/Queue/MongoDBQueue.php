<?php

namespace App\Queue;


use Illuminate\Queue\DatabaseQueue;
use Illuminate\Database\Connection;

class MongoDBQueue extends DatabaseQueue
{
    /**
     * Create a new database queue instance.
     *
     * @param  \Illuminate\Database\Connection  $database
     * @param  string  $table
     * @param  string  $default
     * @param  int  $retryAfter
     * @param  int  $blockFor
     * @return void
     */
    public function __construct(Connection $database, $table, $default = 'default', $retryAfter = 60, $blockFor = 0)
    {
        parent::__construct($database, $table, $default, $retryAfter, $blockFor);
    }

    /**
     * Get the next available job for the queue.
     *
     * @param  string|null  $queue
     * @return \Illuminate\Queue\Jobs\DatabaseJob|null
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        $job = $this->database->collection($this->table)
            ->where('queue', $queue)
            ->where('reserved_at', null)
            ->where('available_at', '<=', $this->currentTime())
            ->orderBy('created_at', 'asc')
            ->first();

        if ($job) {
            $this->database->collection($this->table)
                ->where('_id', $job['_id'])
                ->update([
                    'reserved_at' => $this->currentTime(),
                    'attempts' => $job['attempts'] + 1
                ]);

            return new \Illuminate\Queue\Jobs\DatabaseJob($this->container, $this, $job, $this->connectionName, $queue);
        }
        return null;
    }
}
