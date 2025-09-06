<?php

namespace App\Traits;

trait InsertOrIgnoreMongoTrait
{
    /**
     * Insert documents while ignoring duplicates (based on a unique key).
     *
     * @param array $records
     * @param string $uniqueKey
     * @return void
     */
    public static function insertOrIgnore(array $records, string $uniqueKey)
    {
        foreach ($records as $record) {
            if (! static::where($uniqueKey, $record[$uniqueKey])->exists()) {
                static::create($record);
            }
        }
    }
}
