# ProjectHub - Project Management Tool

A complete, professional, responsive Project Management and Collaboration Tool similar in concept to Trello or Asana. Built with PHP, MySQL, HTML5, CSS3, and Vanilla JavaScript.

## Features

- **User Authentication**: Registration, login, logout with secure session management
- **User Dashboard**: Statistics, recent activity, quick actions (secured with prepared statements)
- **Project Management**: Create, edit, and safely delete projects with status tracking
- **Team Collaboration**: Add/remove team members with role-based permissions (Owner, Admin, Member)
- **Kanban Board**: Drag-and-drop task management with Todo, In Progress, Review, and Done columns + visible due dates
- **My Tasks View**: Dedicated dashboard listing all user-assigned tasks across projects with status/priority filtering
- **Task Management**: Create, edit, delete, assign tasks with priority levels and due dates
- **Task Comments**: Real-time communication within tasks with XSS-safe rendering
- **Activity History**: Track all project and task activities
- **Notifications**: Get notified about project assignments, task updates, and comments
- **Search & Filter**: Search projects and tasks with advanced filtering
- **Project Progress**: Automatic progress calculation based on completed tasks
- **Dark Mode**: High-contrast modern dark mode with persistent user preference
- **Profile Management & Stats**: Profile picture uploads, password management, and personal activity metrics
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices

## Technology Stack

### Frontend
- HTML5
- CSS3
- Vanilla JavaScript
- Responsive design
- Fetch API/AJAX

### Backend
- PHP 8+
- MySQL
- PHP Sessions
- PDO with prepared statements

## Project Structure

```
project-management/
│
├── index.php                    # Landing page
├── login.php                    # Login page
├── register.php                 # Registration page
├── logout.php                   # Logout handler
├── dashboard.php                # User dashboard
├── projects.php                 # Project list
├── create-project.php           # Create new project
├── project.php                  # Project board (Kanban)
├── edit-project.php             # Edit project
├── profile.php                  # User profile
├── notifications.php            # Notifications page
│
├── tasks/
│   ├── create.php               # Create task
│   ├── edit.php                 # Edit task
│   ├── delete.php               # Delete task
│   └── update-status.php        # Update task status (AJAX)
│
├── comments/
│   ├── create.php               # Create comment
│   ├── edit.php                 # Edit comment
│   └── delete.php               # Delete comment
│
├── projects/
│   ├── add-member.php           # Add project member
│   ├── remove-member.php        # Remove project member
│
├── api/
│   ├── tasks.php                # Task API endpoint
│   └── notifications.php        # Notifications API endpoint
│
├── config/
│   └── database.php             # Database configuration
│
├── includes/
│   ├── auth.php                 # Authentication functions
│   ├── functions.php            # Helper functions
│   ├── header.php               # Page header
│   ├── navbar.php               # Navigation bar
│   └── footer.php               # Page footer
│
├── assets/
│   ├── css/
│   │   └── style.css            # Main stylesheet
│   ├── js/
│   │   └── app.js               # Main JavaScript file
│   └── images/                  # Image assets
│
├── uploads/
│   └── profiles/                # User profile pictures
│
├── database/
│   ├── database.sql             # Database schema
│   └── demo_data.sql            # Sample data (optional)
│
└── README.md                    # This file
```

## Installation Instructions (XAMPP)

### Prerequisites
- XAMPP installed on your Windows machine
- Basic knowledge of using phpMyAdmin

### Step 1: Install XAMPP
1. Download XAMPP from https://www.apachefriends.org/
2. Run the installer and follow the setup wizard
3. Complete the installation

### Step 2: Start Apache and MySQL
1. Open XAMPP Control Panel
2. Click "Start" button next to Apache
3. Click "Start" button next to MySQL
4. Wait for both services to show as "Running" (green indicator)

### Step 3: Place the Project
1. Navigate to `C:\xampp\htdocs\`
2. Create a new folder named `codealpha_projectmanagement` (or your preferred name)
3. Copy all project files into this folder

### Step 4: Create the Database
1. Open your web browser
2. Go to http://localhost/phpmyadmin/
3. Click on "New" in the left sidebar
4. Enter database name: `project_management`
5. Click "Create"

### Step 5: Import Database Schema & Demo Data
1. In phpMyAdmin, select the `project_management` database
2. Click on the "Import" tab
3. Click "Choose File" and navigate to `database/combined.sql` (contains complete tables + demo test data) or `database/database.sql` for empty schema
4. Click "Go" at the bottom
5. You should see a success message

> 💡 **Demo Test Accounts** (Password for all: `password`):
> - **John Smith**: `john@example.com` or `johnsmith`
> - **Mary Johnson**: `mary@example.com` or `maryjohnson`
> - **David Wilson**: `david@example.com` or `davidwilson`

### Step 6: Configure Database Connection
1. Open `config/database.php` in a text editor
2. Verify the database credentials (default XAMPP settings):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'project_management');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
3. Save the file if you made any changes

### Step 7: Set File Permissions
1. Navigate to `uploads/profiles/` folder
2. Right-click and select "Properties"
3. Ensure the folder has write permissions (for XAMPP, this is usually already set)

### Step 8: Access the Application
1. Open your web browser
2. Go to http://localhost/codealpha_projectmanagement/
3. You should see the landing page

### Step 9: Register an Account
1. Click "Get Started" or "Register"
2. Fill in the registration form:
   - Full Name
   - Username
   - Email
   - Password
   - Confirm Password
   - Profile Picture (optional)
3. Click "Register"
4. You will be redirected to the login page

### Step 10: Login
1. Enter your email or username and password
2. Click "Login"
3. You will be redirected to the dashboard

## Usage Guide

### Creating a Project
1. After logging in, go to "Projects" from the navigation
2. Click "Create Project"
3. Fill in project details:
   - Project Name
   - Description
   - Start Date (optional)
   - Due Date (optional)
   - Status
4. Click "Create Project"

### Adding Team Members
1. Open a project
2. Click on the project settings or members section
3. Enter the username or email of the user you want to add
4. Select their role (Admin or Member)
5. Click "Add Member"

### Creating Tasks
1. Open a project board
2. Click "Add Task" button
3. Fill in task details:
   - Title
   - Description
   - Priority (Low, Medium, High, Urgent)
   - Status (Todo, In Progress, Review, Done)
   - Assign To (select a team member)
   - Due Date (optional)
4. Click "Create Task"

### Managing Tasks
- **Move Tasks**: Drag and drop task cards between columns to change status
- **Edit Tasks**: Click on a task card to open the task modal, then edit details
- **Delete Tasks**: In the task modal, click "Delete Task" (if you have permission)
- **Add Comments**: In the task modal, use the comment form to add comments

### Viewing Notifications
1. Click the bell icon in the navigation bar
2. You'll see unread notification count
3. Click on a notification to view details
4. Click "Mark as Read" to dismiss notifications

### Managing Your Profile
1. Click on your profile picture or "Profile" in the navigation
2. Update your information:
   - Full Name
   - Username
   - Email
   - Profile Picture
   - Password
3. Click "Update Profile"

## Security Features

- **Password Hashing**: All passwords are hashed using `password_hash()`
- **SQL Injection Protection**: All database queries use PDO prepared statements
- **XSS Protection**: All user input is sanitized using `htmlspecialchars()`
- **CSRF Protection**: Forms include CSRF tokens for important actions
- **Session Security**: Secure session handling with regeneration on login
- **Authorization Checks**: Server-side permission checks for all actions
- **Input Validation**: Comprehensive validation on all forms

## Database Schema

The application uses the following tables:
- `users` - User accounts
- `projects` - Project information
- `project_members` - Project team members
- `tasks` - Task details
- `comments` - Task comments
- `notifications` - User notifications
- `activity_logs` - Activity history

See `database/database.sql` for the complete schema.

## Troubleshooting

### Database Connection Error
- Ensure MySQL is running in XAMPP Control Panel
- Verify database credentials in `config/database.php`
- Check that the database `project_management` exists

### File Upload Error
- Ensure `uploads/profiles/` folder exists and has write permissions
- Check PHP upload_max_filesize and post_max_size settings in php.ini

### Session Issues
- Clear browser cookies and cache
- Ensure session.save_path is writable in php.ini

### Blank Pages
- Enable error reporting in PHP for debugging
- Check Apache error logs in XAMPP

## Browser Compatibility

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## License

This project is created for educational purposes.

## Support

For issues or questions, please refer to the code comments or contact the development team.

## Credits

Built as a CodeAlpha project management tool assignment.
