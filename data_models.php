<?php
require_once 'database_config.php';

/**
 * Class ReferenceDataRepository
 * Handles retrieval of reference/lookup table data
 */
class ReferenceDataRepository {
    private $connection;
    private $allowedTables = [
        'weather_conditions', 
        'traffic_conditions', 
        'route_types', 
        'maneuver_types'
    ];

    public function __construct($databaseConnection) {
        $this->connection = $databaseConnection;
    }

    /**
     * Fetch all options from a reference table
     * @param string $tableName Name of the reference table
     * @return array Array of options
     */
    public function fetchOptions($tableName) {
        if (!in_array($tableName, $this->allowedTables)) {
            return [];
        }
        
        $query = $this->connection->query("SELECT * FROM $tableName ORDER BY name ASC");
        return $query->fetchAll();
    }

    /**
     * Get weather condition options
     */
    public function getWeatherConditions() {
        return $this->fetchOptions('weather_conditions');
    }

    /**
     * Get traffic condition options
     */
    public function getTrafficConditions() {
        return $this->fetchOptions('traffic_conditions');
    }

    /**
     * Get route type options
     */
    public function getRouteTypes() {
        return $this->fetchOptions('route_types');
    }

    /**
     * Get maneuver type options
     */
    public function getManeuverTypes() {
        return $this->fetchOptions('maneuver_types');
    }
}

/**
 * Class DrivingSessionRepository
 * Manages driving session CRUD operations and statistics
 */
class DrivingSessionRepository {
    private $connection;

    public function __construct($databaseConnection) {
        $this->connection = $databaseConnection;
    }

    /**
     * Retrieve all driving sessions with joined reference data
     * @return array All sessions ordered by date descending
     */
    public function fetchAllSessions() {
        $query = "SELECT 
                    session.*, 
                    weather.name as weather, 
                    traffic.name as traffic, 
                    route.name as route, 
                    maneuver.name as maneuver 
                FROM driving_experience session
                LEFT JOIN weather_conditions weather ON session.weather_id = weather.id
                LEFT JOIN traffic_conditions traffic ON session.traffic_id = traffic.id
                LEFT JOIN route_types route ON session.route_id = route.id
                LEFT JOIN maneuver_types maneuver ON session.maneuver_id = maneuver.id
                ORDER BY session.date DESC, session.created_at DESC";
        
        return $this->connection->query($query)->fetchAll();
    }

    /**
     * Retrieve a single session by ID
     * @param int $sessionId
     * @return array|false Session data or false if not found
     */
    public function fetchSessionById($sessionId) {
        $statement = $this->connection->prepare(
            "SELECT * FROM driving_experience WHERE id = ?"
        );
        $statement->execute([$sessionId]);
        return $statement->fetch();
    }

    /**
     * Create a new driving session
     * @param array $sessionData Session information
     * @return bool Success status
     */
    public function createSession($sessionData) {
        $query = "INSERT INTO driving_experience 
                    (date, start_time, end_time, kilometers, weather_id, traffic_id, route_id, maneuver_id) 
                VALUES 
                    (:date, :start_time, :end_time, :kilometers, :weather_id, :traffic_id, :route_id, :maneuver_id)";
        
        $statement = $this->connection->prepare($query);
        return $statement->execute([
            ':date' => $sessionData['date'],
            ':start_time' => $sessionData['start_time'],
            ':end_time' => $sessionData['end_time'],
            ':kilometers' => $sessionData['kilometers'],
            ':weather_id' => $sessionData['weather_id'],
            ':traffic_id' => $sessionData['traffic_id'],
            ':route_id' => $sessionData['route_id'],
            ':maneuver_id' => $sessionData['maneuver_id']
        ]);
    }

    /**
     * Update an existing driving session
     * @param int $sessionId
     * @param array $sessionData Updated session information
     * @return bool Success status
     */
    public function updateSession($sessionId, $sessionData) {
        $query = "UPDATE driving_experience 
                SET date = :date, 
                    start_time = :start_time, 
                    end_time = :end_time, 
                    kilometers = :kilometers, 
                    weather_id = :weather_id, 
                    traffic_id = :traffic_id, 
                    route_id = :route_id, 
                    maneuver_id = :maneuver_id 
                WHERE id = :id";
        
        $statement = $this->connection->prepare($query);
        return $statement->execute([
            ':id' => $sessionId,
            ':date' => $sessionData['date'],
            ':start_time' => $sessionData['start_time'],
            ':end_time' => $sessionData['end_time'],
            ':kilometers' => $sessionData['kilometers'],
            ':weather_id' => $sessionData['weather_id'],
            ':traffic_id' => $sessionData['traffic_id'],
            ':route_id' => $sessionData['route_id'],
            ':maneuver_id' => $sessionData['maneuver_id']
        ]);
    }

    /**
     * Delete a driving session
     * @param int $sessionId
     * @return bool Success status
     */
    public function deleteSession($sessionId) {
        $statement = $this->connection->prepare(
            "DELETE FROM driving_experience WHERE id = ?"
        );
        return $statement->execute([$sessionId]);
    }

    /**
     * Get aggregated statistics for a specific category
     * @param string $category Category type (weather, traffic, route, maneuver)
     * @return array Statistics with labels and counts
     */
    public function fetchCategoryStatistics($category) {
        $tableMapping = [
            'weather' => 'weather_conditions',
            'traffic' => 'traffic_conditions',
            'route' => 'route_types',
            'maneuver' => 'maneuver_types'
        ];

        if (!array_key_exists($category, $tableMapping)) {
            return [];
        }

        $referenceTable = $tableMapping[$category];
        $foreignKey = $category . '_id';

        $query = "SELECT 
                    ref.name as label, 
                    COUNT(session.id) as count, 
                    SUM(session.kilometers) as total_km
                FROM driving_experience session
                JOIN $referenceTable ref ON session.$foreignKey = ref.id
                GROUP BY ref.name
                ORDER BY count DESC";
        
        return $this->connection->query($query)->fetchAll();
    }
    
    /**
     * Calculate total kilometers driven
     * @return float Total kilometers
     */
    public function calculateTotalDistance() {
        $result = $this->connection->query(
            "SELECT SUM(kilometers) as total FROM driving_experience"
        )->fetch();
        
        return $result['total'] ?? 0;
    }

    /**
     * Get kilometers grouped by month
     * @param int $monthLimit Number of recent months to retrieve
     * @return array Monthly kilometer data
     */
    public function fetchMonthlyKilometers($monthLimit = 6) {
        $query = "SELECT 
                    DATE_FORMAT(date, '%Y-%m') as month, 
                    SUM(kilometers) as total 
                FROM driving_experience 
                GROUP BY month 
                ORDER BY month DESC 
                LIMIT " . intval($monthLimit);
        
        return $this->connection->query($query)->fetchAll();
    }

    /**
     * Get kilometers grouped by weather condition
     * @return array Weather-based kilometer data
     */
    public function fetchKilometersByWeather() {
        $query = "SELECT 
                    weather.name as weather, 
                    SUM(session.kilometers) as total 
                FROM driving_experience session 
                JOIN weather_conditions weather ON session.weather_id = weather.id 
                GROUP BY weather.name 
                ORDER BY total DESC";
        
        return $this->connection->query($query)->fetchAll();
    }

    /**
     * Get kilometers grouped by route type
     * @return array Route-based kilometer data
     */
    public function fetchKilometersByRoute() {
        $query = "SELECT 
                    route.name as route, 
                    SUM(session.kilometers) as total 
                FROM driving_experience session 
                JOIN route_types route ON session.route_id = route.id 
                GROUP BY route.name 
                ORDER BY total DESC";
        
        return $this->connection->query($query)->fetchAll();
    }
}
?>