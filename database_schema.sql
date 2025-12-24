-- Driving Experience Tracker Database Schema
-- Reference/Lookup Tables and Main Data Table

-- Weather Conditions Reference Table
CREATE TABLE IF NOT EXISTS weather_conditions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    INDEX idx_weather_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Traffic Conditions Reference Table
CREATE TABLE IF NOT EXISTS traffic_conditions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    INDEX idx_traffic_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Route Types Reference Table
CREATE TABLE IF NOT EXISTS route_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    INDEX idx_route_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Maneuver Types Reference Table
CREATE TABLE IF NOT EXISTS maneuver_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    INDEX idx_maneuver_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default reference data (IGNORE prevents duplicates on re-run)
INSERT IGNORE INTO weather_conditions (name) VALUES 
    ('Sunny'), ('Rainy'), ('Cloudy'), ('Snowy'), ('Foggy'), ('Windy');

INSERT IGNORE INTO traffic_conditions (name) VALUES 
    ('Light'), ('Moderate'), ('Heavy'), ('Very Heavy');

INSERT IGNORE INTO route_types (name) VALUES 
    ('Highway'), ('City'), ('Rural'), ('Mountain'), ('Mixed');

INSERT IGNORE INTO maneuver_types (name) VALUES 
    ('Parking'), ('Lane Change'), ('Overtaking'), ('Turning'), ('Reversing'), ('Normal Driving');

-- Drop and recreate main driving experience table
DROP TABLE IF EXISTS driving_experience;

CREATE TABLE driving_experience (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    kilometers DECIMAL(10,2) NOT NULL,
    weather_id INT,
    traffic_id INT,
    route_id INT,
    maneuver_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (date),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (weather_id) REFERENCES weather_conditions(id) ON DELETE SET NULL,
    FOREIGN KEY (traffic_id) REFERENCES traffic_conditions(id) ON DELETE SET NULL,
    FOREIGN KEY (route_id) REFERENCES route_types(id) ON DELETE SET NULL,
    FOREIGN KEY (maneuver_id) REFERENCES maneuver_types(id) ON DELETE SET NULL,
    CONSTRAINT chk_kilometers CHECK (kilometers >= 0),
    CONSTRAINT chk_times CHECK (start_time < end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;