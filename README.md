# DriveLog Tracker - Supervised Driving Experience Management System

## 📖 Project Overview

DriveLog Tracker is a comprehensive web-based application designed to help learner drivers track, analyze, and improve their supervised driving experiences. The system provides an intuitive interface for recording driving sessions and offers powerful analytics to monitor progress over time.

**Developer**: Karam Imamali  
**Course**: CS-23  
**Year**: 2025  
**Institution**: Computer Science Department

---

## 🎯 Project Purpose

This application serves as a digital logbook for supervised driving sessions, enabling users to:
- **Record detailed driving sessions** with comprehensive metadata
- **Analyze driving patterns** through interactive visualizations
- **Track progress** toward driving proficiency goals
- **Identify areas for improvement** based on historical data
- **Maintain compliance** with supervised driving hour requirements

---

## ✨ Key Features

### Session Management
- ✅ **Create Sessions**: Log new driving experiences with detailed information
- ✅ **Edit Sessions**: Update existing records as needed
- ✅ **Delete Sessions**: Remove entries with confirmation safeguards
- ✅ **View History**: Browse all recorded sessions in an organized table

### Data Tracking
- 📅 **Date & Time**: Track when and how long each session lasted
- 🚗 **Distance**: Record kilometers driven per session
- 🌤️ **Weather Conditions**: Log environmental factors (Sunny, Rainy, Cloudy, Snowy, Foggy, Windy)
- 🚦 **Traffic Levels**: Document traffic intensity (Light, Moderate, Heavy, Very Heavy)
- 🛣️ **Route Types**: Categorize driving environments (Highway, City, Rural, Mountain, Mixed)
- 🎯 **Maneuver Types**: Track specific skills practiced (Parking, Lane Change, Overtaking, Turning, Reversing, Normal Driving)

### Analytics Dashboard
- 📊 **Summary Statistics**: Total distance, session count, and averages
- 📈 **Visual Analytics**: Interactive charts showing:
  - Distribution of weather conditions encountered
  - Traffic level patterns
  - Route type preferences
  - Maneuver practice frequency
  - Distance covered by weather and route type
  - Monthly driving trends
- 🔍 **Search & Filter**: Quick search functionality across all sessions
- 📱 **Responsive Design**: Full mobile compatibility

### User Experience
- 🎨 **Modern UI**: Cyberpunk-inspired design with neon accents
- ⚡ **Real-time Validation**: Client-side form validation for immediate feedback
- 🔔 **Status Messages**: Clear success and error notifications
- 📲 **Mobile Optimized**: Fully responsive across all devices
- ✨ **Smooth Animations**: Professional transitions and effects

---

## 🏗️ System Architecture

### Technology Stack

**Frontend:**
- HTML5 - Semantic markup
- CSS3 - Modern styling with animations
- Vanilla JavaScript - Client-side logic and validation
- Chart.js - Data visualization library
- Font Awesome - Icon library
- Google Fonts - Custom typography (Outfit, Orbitron, JetBrains Mono, Rajdhani)

**Backend:**
- PHP 7.4+ - Server-side processing
- PDO (PHP Data Objects) - Database abstraction layer
- Object-Oriented Programming - Clean, maintainable code structure

**Database:**
- MySQL/MariaDB - Relational database management
- Normalized schema with foreign key constraints
- Indexed columns for optimal query performance

### Application Structure

```
drivelog-tracker/
│
├── Frontend Files
│   ├── index.html                  # Session creation form
│   ├── form_validation.js          # Client-side validation
│   └── [Embedded CSS in pages]     # Scoped styling
│
├── Backend Logic
│   ├── database_config.php         # Database connection handler
│   ├── data_models.php             # Repository pattern classes
│   ├── session_handler.php         # CRUD operations handler
│   ├── session_remover.php         # Deletion logic
│   ├── session_editor.php          # Edit interface
│   └── analytics_dashboard.php     # Analytics & reporting
│
├── Database
│   ├── database_schema.sql         # Table definitions
│   └── database_setup.php          # Installation script
│
└── README.md                       # Documentation (this file)
```

---

## 💾 Database Schema

### Core Tables

**driving_experience** (Main Data Table)
- `id` - Primary key (Auto-increment)
- `date` - Session date
- `start_time` - Session start time
- `end_time` - Session end time
- `kilometers` - Distance driven (Decimal 10,2)
- `weather_id` - Foreign key to weather_conditions
- `traffic_id` - Foreign key to traffic_conditions
- `route_id` - Foreign key to route_types
- `maneuver_id` - Foreign key to maneuver_types
- `created_at` - Record creation timestamp
- `updated_at` - Last modification timestamp

**Reference Tables** (Lookup Data)
- `weather_conditions` - Weather types
- `traffic_conditions` - Traffic levels
- `route_types` - Route categories
- `maneuver_types` - Maneuver categories

### Data Integrity
- ✅ Foreign key constraints ensure referential integrity
- ✅ Check constraints validate data ranges (e.g., kilometers ≥ 0)
- ✅ Unique constraints prevent duplicate reference data
- ✅ Indexed columns optimize query performance
- ✅ Timestamps track data lifecycle

---

## 🚀 Installation Guide

### Prerequisites
- Web server (Apache/Nginx)
- PHP 7.4 or higher with PDO extension
- MySQL 5.7+ or MariaDB 10.2+
- Modern web browser

### Step-by-Step Installation

1. **Upload Files**
   ```
   Upload all project files to your web server directory
   ```

2. **Configure Database**
   - Ensure your database credentials are set in `database_config.php`
   - Default configuration uses existing database connection

3. **Initialize Database**
   ```
   Navigate to: http://your-domain.com/database_setup.php
   ```
   This will:
   - Create all required tables
   - Set up foreign key relationships
   - Insert default reference data
   - Validate the setup

4. **Access Application**
   - Main Form: `http://your-domain.com/index.html`
   - Dashboard: `http://your-domain.com/analytics_dashboard.php`

5. **Verify Installation**
   - Create a test session
   - Check that it appears in the dashboard
   - Verify charts are rendering correctly

---

## 📖 User Guide

### Recording a Driving Session

1. **Navigate to Entry Form** (`index.html`)
2. **Fill in Required Information:**
   - Session Date (defaults to today)
   - Start Time and End Time
   - Distance driven in kilometers
   - Weather condition
   - Traffic level
   - Route type
   - Maneuver type practiced
3. **Submit**: Click "Save Session" button
4. **Confirmation**: You'll be redirected to the dashboard with a success message

### Viewing Your Progress

1. **Open Dashboard** (`analytics_dashboard.php`)
2. **View Summary Cards:**
   - Total Distance: Cumulative kilometers driven
   - Total Sessions: Number of recorded sessions
   - Average Distance: Average km per session
3. **Analyze Charts:**
   - Distribution charts show patterns in conditions
   - Trend charts reveal progress over time
4. **Browse Session Table:**
   - See all sessions chronologically
   - Use search to find specific entries
   - Click column headers to sort

### Editing a Session

1. **From Dashboard**: Click "Edit" button on any session
2. **Modify Fields**: Update any information as needed
3. **Save Changes**: Click "Update Session"
4. **Verify**: Changes appear immediately in dashboard

### Deleting a Session

1. **From Dashboard**: Click "Delete" button on any session
2. **Confirm**: Accept the confirmation dialog
3. **Removed**: Session is permanently deleted

---

## 🔧 Technical Documentation

### Data Models

**DrivingSessionRepository Class**
```php
// Primary methods:
fetchAllSessions()           // Retrieve all sessions with joined data
fetchSessionById($id)        // Get specific session
createSession($data)         // Insert new session
updateSession($id, $data)    // Update existing session
deleteSession($id)           // Remove session
calculateTotalDistance()     // Sum all kilometers
fetchCategoryStatistics()    // Get aggregated stats
fetchMonthlyKilometers()     // Monthly trend data
```

**ReferenceDataRepository Class**
```php
// Reference data access:
getWeatherConditions()       // Fetch weather options
getTrafficConditions()       // Fetch traffic options
getRouteTypes()             // Fetch route options
getManeuverTypes()          // Fetch maneuver options
```

### API Endpoints (Form Handlers)

**session_handler.php**
- Method: POST
- Purpose: Create or update sessions
- Validation: Server-side input sanitization and validation
- Response: Redirect to dashboard with status message

**session_remover.php**
- Method: POST
- Purpose: Delete specific session
- Validation: ID verification
- Response: Redirect to dashboard with confirmation

### Security Features

- ✅ **SQL Injection Prevention**: Prepared statements with PDO
- ✅ **XSS Protection**: htmlspecialchars() on all output
- ✅ **CSRF Protection**: Session-based authentication ready
- ✅ **Input Validation**: Client and server-side validation
- ✅ **Error Handling**: Graceful error messages without exposing system details

---

## 📊 Analytics & Reporting

### Available Visualizations

1. **Weather Distribution** (Pie Chart)
   - Shows percentage of sessions in each weather condition
   - Helps identify experience gaps

2. **Traffic Patterns** (Doughnut Chart)
   - Displays traffic level distribution
   - Tracks exposure to various traffic conditions

3. **Route Types** (Bar Chart)
   - Compares session counts by route type
   - Identifies preferred or avoided environments

4. **Maneuver Practice** (Horizontal Bar Chart)
   - Shows which skills are practiced most/least
   - Guides future practice focus

5. **Distance by Weather** (Pie Chart)
   - Kilometers driven in each weather condition
   - Different perspective from session count

6. **Distance by Route** (Bar Chart)
   - Kilometers covered on each route type
   - Shows where most driving occurs

7. **Monthly Trends** (Line Chart)
   - Distance driven over time
   - Visualizes progression and consistency

### Statistical Insights

The dashboard automatically calculates:
- **Total Distance**: Sum of all kilometers driven
- **Session Count**: Total number of recorded sessions
- **Average Distance**: Mean kilometers per session
- **Category Distributions**: Breakdown by all dimensions

---

## 🎨 Design Philosophy

### Color Palette
- **Background**: Deep space dark (`#0A0E27`, `#0F1629`)
- **Surfaces**: Elevated dark layers (`#1E293B`, `#334155`)
- **Accents**: 
  - Cyan (`#00F5FF`) - Primary actions, highlights
  - Pink (`#FF007A`) - Secondary emphasis
  - Purple (`#A855F7`) - Tertiary accents
- **Text**: 
  - Primary: White (`#FFFFFF`)
  - Secondary: Light blue-gray (`#8B9DBA`)
  - Muted: Dark gray (`#4A5568`)

### Typography
- **Outfit**: Clean, modern body text
- **Orbitron**: Bold, futuristic headers
- **Rajdhani**: Technical, data-focused dashboard
- **JetBrains Mono**: Monospace for time inputs

### Visual Effects
- Animated gradient backgrounds
- Neon glow on interactive elements
- Smooth transitions (0.3s cubic-bezier)
- Hover state transformations
- Loading states for form submissions

---

## 📱 Responsive Design

### Breakpoints
- **Desktop**: 1600px+ (Full layout with multi-column grids)
- **Tablet**: 768px - 1599px (Adjusted grid columns)
- **Mobile**: < 768px (Single column, card-based table view)

### Mobile Optimizations
- Touch-friendly button sizes (min 44x44px)
- Readable font sizes (minimum 16px for inputs)
- Horizontal scroll prevention
- Simplified table view with data labels
- Optimized chart sizes
- Stacked form layouts

---

## 🔄 Data Flow

### Session Creation Flow
```
User Input → Client Validation → Form Submit → 
session_handler.php → Server Validation → 
Database Insert → Success/Error Message → 
Redirect to Dashboard → Display Confirmation
```

### Dashboard Loading Flow
```
Page Load → Fetch All Sessions → 
Calculate Statistics → Prepare Chart Data → 
Render Components → Enable Interactions
```

---

## 🛡️ Error Handling

### Client-Side
- Required field validation
- Date format validation
- Numeric range validation (distance > 0)
- Time range validation (end > start)
- Future date prevention
- Real-time error highlighting

### Server-Side
- PDO exception catching
- Input sanitization
- Data type validation
- Database constraint validation
- Graceful error messages
- Session-based error persistence

---

## 🚦 Testing Checklist

### Functional Testing
- [ ] Create new session successfully
- [ ] Edit existing session
- [ ] Delete session with confirmation
- [ ] View all sessions in table
- [ ] Search functionality works
- [ ] Sort columns ascending/descending
- [ ] Charts render correctly
- [ ] Statistics calculate accurately
- [ ] Mobile layout displays properly
- [ ] Form validation catches errors

### Data Integrity
- [ ] Foreign keys prevent orphaned records
- [ ] Check constraints enforce valid ranges
- [ ] Timestamps record correctly
- [ ] Updates preserve referential integrity

### Security Testing
- [ ] SQL injection attempts fail
- [ ] XSS attempts are sanitized
- [ ] Direct file access is prevented
- [ ] Invalid input is rejected

---

## 🔮 Future Enhancements

### Potential Features
- 🔐 User authentication and multi-user support
- 📧 Email reports and reminders
- 🎯 Goal setting and achievement tracking
- 📤 Export to PDF/Excel
- 📊 Advanced analytics (weather vs. distance correlation)
- 🗺️ GPS integration for route mapping
- 📸 Photo attachment for sessions
- 👥 Instructor feedback system
- 📅 Appointment scheduling
- 🏆 Gamification elements

### Technical Improvements
- REST API for mobile app integration
- Real-time notifications
- Automated backup system
- Advanced caching mechanisms
- Progressive Web App (PWA) capabilities

---

## 📄 License & Usage

This project is developed for educational purposes as part of the CS-23 curriculum.

**Usage Terms:**
- Free to use for personal driving practice tracking
- Modification allowed for learning purposes
- Attribution required if redistributed
- Not for commercial use without permission

---

## 🤝 Support & Maintenance

### Troubleshooting

**Database Connection Errors:**
- Verify credentials in `database_config.php`
- Check database server is running
- Confirm user has proper permissions

**Charts Not Displaying:**
- Ensure internet connection for CDN resources
- Check browser console for JavaScript errors
- Verify Chart.js library loads successfully

**Form Validation Issues:**
- Clear browser cache
- Check JavaScript is enabled
- Verify form_validation.js is loaded

**Mobile Display Problems:**
- Clear mobile browser cache
- Check viewport meta tag is present
- Test on multiple devices

### Contact

For questions, issues, or contributions:
- **Developer**: Karam Imamali
- **Course**: CS-23
- **Year**: 2025

---

## 📚 References & Credits

### Technologies Used
- **Chart.js**: Data visualization library
- **Font Awesome**: Icon toolkit
- **Google Fonts**: Typography
- **PHP**: Server-side scripting
- **MySQL**: Database management

### Learning Resources
- PHP Documentation: https://www.php.net/docs.php
- MySQL Reference: https://dev.mysql.com/doc/
- Chart.js Guide: https://www.chartjs.org/docs/
- MDN Web Docs: https://developer.mozilla.org/

---

## 🎓 Academic Context

This project demonstrates proficiency in:
- Full-stack web development
- Database design and normalization
- Object-oriented programming
- User interface design
- Data visualization
- Security best practices
- Responsive web design
- Version control and documentation

**Learning Outcomes Achieved:**
✅ Design and implement a complete web application  
✅ Create normalized database schemas  
✅ Develop secure CRUD operations  
✅ Build responsive user interfaces  
✅ Implement data analytics and visualization  
✅ Apply software engineering best practices  
✅ Document technical projects professionally  

---

## 📝 Conclusion

DriveLog Tracker represents a comprehensive solution for supervised driving management, combining intuitive design with powerful analytics. The application successfully addresses the need for organized driving practice tracking while providing valuable insights through data visualization.

The project demonstrates modern web development practices, including responsive design, secure data handling, and user-centered interface design. Its modular architecture ensures maintainability and allows for future expansion.

---

**Version**: 1.0  
**Last Updated**: 2025  
**Status**: Production Ready ✅

---

*Developed with precision and care by Karam Imamali as part of the CS-23 program.*