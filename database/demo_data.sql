-- Demo Data for Project Management Tool
-- This file contains sample data for testing purposes
-- Import this AFTER importing database.sql

USE project_management;

-- Sample Users
INSERT INTO users (full_name, username, email, password, created_at) VALUES
('John Smith', 'johnsmith', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW()),
('Mary Johnson', 'maryjohnson', 'mary@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW()),
('David Wilson', 'davidwilson', 'david@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW()),
('Sarah Brown', 'sarahbrown', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW()),
('Michael Davis', 'michaeldavis', 'michael@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW());

-- Note: All passwords are 'password' (hashed)

-- Sample Projects
INSERT INTO projects (owner_id, name, description, start_date, due_date, status, created_at) VALUES
(1, 'Website Redesign', 'Complete redesign of company website with modern UI/UX', '2024-01-01', '2024-03-31', 'In Progress', NOW()),
(1, 'Mobile App Development', 'Develop a cross-platform mobile application', '2024-02-01', '2024-06-30', 'Planning', NOW()),
(2, 'Marketing Campaign', 'Q1 marketing campaign for new product launch', '2024-01-15', '2024-04-15', 'In Progress', NOW()),
(3, 'Database Migration', 'Migrate legacy database to new cloud infrastructure', '2024-03-01', '2024-05-31', 'Planning', NOW());

-- Project Members
INSERT INTO project_members (project_id, user_id, role, joined_at) VALUES
-- Project 1 (Website Redesign)
(1, 1, 'Owner', NOW()),
(1, 2, 'Admin', NOW()),
(1, 3, 'Member', NOW()),
(1, 4, 'Member', NOW()),
-- Project 2 (Mobile App Development)
(2, 1, 'Owner', NOW()),
(2, 3, 'Admin', NOW()),
(2, 5, 'Member', NOW()),
-- Project 3 (Marketing Campaign)
(3, 2, 'Owner', NOW()),
(3, 4, 'Admin', NOW()),
(3, 5, 'Member', NOW()),
-- Project 4 (Database Migration)
(4, 3, 'Owner', NOW()),
(4, 1, 'Member', NOW()),
(4, 5, 'Member', NOW());

-- Sample Tasks
INSERT INTO tasks (project_id, created_by, assigned_to, title, description, priority, status, due_date, created_at) VALUES
-- Project 1 Tasks
(1, 1, 2, 'Design Homepage Mockup', 'Create initial design mockups for the homepage', 'High', 'Done', '2024-01-15', NOW()),
(1, 1, 3, 'Setup Development Environment', 'Configure local development environment', 'Medium', 'Done', '2024-01-10', NOW()),
(1, 2, 3, 'Implement Homepage', 'Code the homepage based on approved mockups', 'High', 'In Progress', '2024-02-15', NOW()),
(1, 1, 4, 'Write Content', 'Create content for all pages', 'Medium', 'Todo', '2024-02-28', NOW()),
(1, 2, 4, 'SEO Optimization', 'Implement SEO best practices', 'Low', 'Todo', '2024-03-15', NOW()),
(1, 1, 2, 'User Testing', 'Conduct user testing sessions', 'High', 'Review', '2024-03-20', NOW()),
-- Project 2 Tasks
(2, 1, 3, 'Requirements Gathering', 'Gather and document app requirements', 'High', 'Done', '2024-02-15', NOW()),
(2, 1, 5, 'UI/UX Design', 'Design app interface and user flows', 'High', 'In Progress', '2024-03-15', NOW()),
(2, 3, 5, 'Backend API Development', 'Develop REST API endpoints', 'High', 'Todo', '2024-04-30', NOW()),
(2, 1, 3, 'Frontend Development', 'Build mobile app frontend', 'High', 'Todo', '2024-05-15', NOW()),
-- Project 3 Tasks
(3, 2, 4, 'Market Research', 'Conduct market research for target audience', 'Medium', 'Done', '2024-01-30', NOW()),
(3, 2, 5, 'Content Creation', 'Create marketing content and assets', 'High', 'In Progress', '2024-03-01', NOW()),
(3, 4, 5, 'Social Media Setup', 'Setup social media accounts and profiles', 'Medium', 'Todo', '2024-03-15', NOW()),
(3, 2, 4, 'Email Campaign', 'Design and launch email marketing campaign', 'High', 'Todo', '2024-04-01', NOW()),
-- Project 4 Tasks
(4, 3, 1, 'Database Analysis', 'Analyze current database structure', 'High', 'Done', '2024-03-15', NOW()),
(4, 3, 5, 'Cloud Provider Selection', 'Select and configure cloud provider', 'High', 'In Progress', '2024-04-01', NOW()),
(4, 1, 5, 'Data Migration Script', 'Write scripts for data migration', 'High', 'Todo', '2024-05-15', NOW()),
(4, 3, 1, 'Testing and Validation', 'Test migrated data for accuracy', 'High', 'Todo', '2024-05-25', NOW());

-- Sample Comments
INSERT INTO comments (task_id, user_id, comment, created_at) VALUES
(1, 2, 'Mockups look great! Approved for development.', NOW()),
(1, 1, 'Thanks! Moving to implementation phase.', NOW()),
(3, 3, 'Working on the homepage implementation. Should be done by Friday.', NOW()),
(3, 1, 'Great progress! Let me know if you need any help.', NOW()),
(6, 2, 'Please schedule user testing sessions for next week.', NOW()),
(6, 4, 'I can help coordinate the testing sessions.', NOW()),
(7, 3, 'Requirements document is complete and approved.', NOW()),
(8, 5, 'Started working on UI designs. Will share mockups soon.', NOW()),
(11, 4, 'Market research complete. Target audience identified.', NOW()),
(12, 5, 'Creating social media graphics and ad copy.', NOW()),
(15, 1, 'Database analysis complete. Found some optimization opportunities.', NOW()),
(16, 5, 'Evaluating AWS vs Azure for cloud migration.', NOW());

-- Sample Activity Logs
INSERT INTO activity_logs (project_id, task_id, user_id, action, description, created_at) VALUES
(1, 1, 1, 'created_task', 'created task: Design Homepage Mockup', NOW()),
(1, 1, 2, 'added_comment', 'added a comment', NOW()),
(1, 2, 1, 'created_task', 'created task: Setup Development Environment', NOW()),
(1, 3, 1, 'created_task', 'created task: Implement Homepage', NOW()),
(1, 3, 3, 'changed_status', 'changed status from Todo to In Progress', NOW()),
(1, NULL, 1, 'created', 'created this project', NOW()),
(1, NULL, 2, 'added_member', 'added Mary Johnson to the project', NOW()),
(2, 7, 1, 'created_task', 'created task: Requirements Gathering', NOW()),
(2, 8, 1, 'created_task', 'created task: UI/UX Design', NOW()),
(3, 11, 2, 'created_task', 'created task: Market Research', NOW()),
(4, 15, 3, 'created_task', 'created task: Database Analysis', NOW());

-- Sample Notifications
INSERT INTO notifications (user_id, type, message, related_project_id, related_task_id, is_read, created_at) VALUES
(2, 'task_assigned', 'You have been assigned to task: Design Homepage Mockup', 1, 1, FALSE, NOW()),
(3, 'task_assigned', 'You have been assigned to task: Setup Development Environment', 1, 2, FALSE, NOW()),
(3, 'task_assigned', 'You have been assigned to task: Implement Homepage', 1, 3, FALSE, NOW()),
(4, 'task_assigned', 'You have been assigned to task: Write Content', 1, 4, TRUE, NOW()),
(2, 'comment_added', 'New comment on task: Design Homepage Mockup', 1, 1, FALSE, NOW()),
(3, 'project_added', 'You have been added to the project: Website Redesign', 1, NULL, TRUE, NOW()),
(5, 'task_assigned', 'You have been assigned to task: UI/UX Design', 2, 8, FALSE, NOW()),
(4, 'task_assigned', 'You have been assigned to task: Market Research', 3, 11, TRUE, NOW()),
(5, 'task_assigned', 'You have been assigned to task: Content Creation', 3, 12, FALSE, NOW()),
(1, 'task_completed', 'Task completed: Design Homepage Mockup', 1, 1, TRUE, NOW());

-- Demo data insertion complete
-- Default login credentials:
-- Username: johnsmith, Password: password
-- Username: maryjohnson, Password: password
-- Username: davidwilson, Password: password
-- Username: sarahbrown, Password: password
-- Username: michaeldavis, Password: password
