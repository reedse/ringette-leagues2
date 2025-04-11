<?php

namespace App\Constants;

/**
 * Filter constants for games and other resources
 * These should match the frontend constants (filters.js)
 */
class Filters
{
    // Filter keys
    const LEAGUE = 'league';
    const SEASON = 'season';
    const TEAM = 'team';
    const STATUS = 'status';
    
    // Game statuses
    const STATUS_SCHEDULED = 'Scheduled';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_COMPLETED = 'Completed';
    
    /**
     * Get all available game statuses
     * 
     * @return array
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_SCHEDULED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED
        ];
    }
    
    /**
     * Get all filter keys as an array
     * 
     * @return array
     */
    public static function getFilterKeys(): array
    {
        return [
            self::LEAGUE,
            self::SEASON,
            self::TEAM,
            self::STATUS
        ];
    }
} 