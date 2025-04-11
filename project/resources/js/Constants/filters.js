/**
 * Filter constants for games and other resources
 * These should match the backend constants (GameController.php etc.)
 */

export const FILTER_LEAGUE = 'league';
export const FILTER_SEASON = 'season';
export const FILTER_TEAM = 'team';
export const FILTER_STATUS = 'status';

// Game statuses
export const STATUS_SCHEDULED = 'Scheduled';
export const STATUS_IN_PROGRESS = 'In Progress';
export const STATUS_COMPLETED = 'Completed';

export const STATUS_OPTIONS = [
    STATUS_SCHEDULED,
    STATUS_IN_PROGRESS,
    STATUS_COMPLETED
];

// Utility function to generate empty filters object
export const createEmptyFilters = () => ({
    [FILTER_LEAGUE]: null,
    [FILTER_SEASON]: null,
    [FILTER_TEAM]: null,
    [FILTER_STATUS]: null
});

// Utility function to apply filters to a route
export const applyFilters = (filters) => {
    // Remove null/undefined values 
    return Object.entries(filters).reduce((result, [key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            result[key] = value;
        }
        return result;
    }, {});
};

export default {
    FILTER_LEAGUE,
    FILTER_SEASON,
    FILTER_TEAM,
    FILTER_STATUS,
    STATUS_SCHEDULED,
    STATUS_IN_PROGRESS,
    STATUS_COMPLETED,
    STATUS_OPTIONS,
    createEmptyFilters,
    applyFilters
}; 