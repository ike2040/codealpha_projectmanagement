// Project Management Tool - JavaScript

// XSS escape helper
function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navbarNav = document.getElementById('navbarNav');
    
    if (mobileMenuToggle && navbarNav) {
        mobileMenuToggle.addEventListener('click', function() {
            navbarNav.classList.toggle('active');
        });
    }
    
    // Initialize drag and drop
    initDragAndDrop();
    
    // Initialize create task form
    initCreateTaskForm();
    
    // Poll for notifications
    pollNotifications();
});

// Drag and Drop functionality
function initDragAndDrop() {
    const taskCards = document.querySelectorAll('.task-card');
    const columns = document.querySelectorAll('.column-tasks');
    
    taskCards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
    });
    
    columns.forEach(column => {
        column.addEventListener('dragover', handleDragOver);
        column.addEventListener('drop', handleDrop);
        column.addEventListener('dragenter', handleDragEnter);
        column.addEventListener('dragleave', handleDragLeave);
    });
}

let draggedTask = null;

function handleDragStart(e) {
    draggedTask = this;
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', this.dataset.taskId);
}

function handleDragEnd(e) {
    this.classList.remove('dragging');
    draggedTask = null;
    
    document.querySelectorAll('.column-tasks').forEach(column => {
        column.classList.remove('drag-over');
    });
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
}

function handleDragEnter(e) {
    e.preventDefault();
    this.classList.add('drag-over');
}

function handleDragLeave(e) {
    this.classList.remove('drag-over');
}

function handleDrop(e) {
    e.preventDefault();
    this.classList.remove('drag-over');
    
    if (!draggedTask) return;
    
    const taskId = e.dataTransfer.getData('text/plain');
    const newStatus = this.closest('.kanban-column').dataset.status;
    
    // Update task status via AJAX
    updateTaskStatus(taskId, newStatus);
    
    // Move the card visually
    this.appendChild(draggedTask);
    
    // Update task count
    updateTaskCounts();
}

function updateTaskStatus(taskId, newStatus) {
    const formData = new FormData();
    formData.append('task_id', taskId);
    formData.append('status', newStatus);
    
    fetch('tasks/update-status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Failed to update task status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update task status');
    });
}

function updateTaskCounts() {
    document.querySelectorAll('.kanban-column').forEach(column => {
        const count = column.querySelectorAll('.task-card').length;
        column.querySelector('.task-count').textContent = count;
    });
}

// Task Modal
function openTaskModal(taskId) {
    const modal = document.getElementById('taskModal');
    const modalBody = document.getElementById('modalTaskBody');
    
    modalBody.innerHTML = '<p>Loading...</p>';
    modal.classList.add('active');
    
    fetch('api/tasks.php?task_id=' + taskId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTaskModal(data);
            } else {
                modalBody.innerHTML = '<p>Error loading task: ' + data.message + '</p>';
            }
        })
        .catch(error => {
            modalBody.innerHTML = '<p>Error loading task</p>';
        });
}

function closeTaskModal() {
    const modal = document.getElementById('taskModal');
    modal.classList.remove('active');
}

function renderTaskModal(data) {
    const task = data.task;
    const comments = data.comments;
    const activity = data.activity;
    const members = data.members;
    
    const modalBody = document.getElementById('modalTaskBody');
    const modalTitle = document.getElementById('modalTaskTitle');
    
    modalTitle.textContent = task.title;
    
    let html = `
        <div class="task-detail-section">
            <h3>Task Details</h3>
            <div class="task-detail-row">
                <span class="task-detail-label">Description:</span>
                <span class="task-detail-value">${esc(task.description) || 'No description'}</span>
            </div>
            <div class="task-detail-row">
                <span class="task-detail-label">Priority:</span>
                <span class="task-detail-value">${task.priority}</span>
            </div>
            <div class="task-detail-row">
                <span class="task-detail-label">Status:</span>
                <span class="task-detail-value">${task.status}</span>
            </div>
            <div class="task-detail-row">
                <span class="task-detail-label">Assigned To:</span>
                <span class="task-detail-value">${task.assigned_to_name || 'Unassigned'}</span>
            </div>
            <div class="task-detail-row">
                <span class="task-detail-label">Due Date:</span>
                <span class="task-detail-value">${task.due_date || 'No due date'}</span>
            </div>
            <div class="task-detail-row">
                <span class="task-detail-label">Created By:</span>
                <span class="task-detail-value">${task.created_by_name}</span>
            </div>
            <div class="task-detail-row">
                <span class="task-detail-label">Created:</span>
                <span class="task-detail-value">${new Date(task.created_at).toLocaleString()}</span>
            </div>
            <div class="task-detail-row">
                <span class="task-detail-label">Last Updated:</span>
                <span class="task-detail-value">${new Date(task.updated_at).toLocaleString()}</span>
            </div>
        </div>
        
        ${data.can_edit ? `
        <div class="task-detail-section">
            <h3>Edit Task</h3>
            <form id="editTaskForm" class="task-form">
                <input type="hidden" name="task_id" value="${task.id}">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" value="${task.title}" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3">${esc(task.description) || ''}</textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority">
                            <option value="Low" ${task.priority === 'Low' ? 'selected' : ''}>Low</option>
                            <option value="Medium" ${task.priority === 'Medium' ? 'selected' : ''}>Medium</option>
                            <option value="High" ${task.priority === 'High' ? 'selected' : ''}>High</option>
                            <option value="Urgent" ${task.priority === 'Urgent' ? 'selected' : ''}>Urgent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="Todo" ${task.status === 'Todo' ? 'selected' : ''}>Todo</option>
                            <option value="In Progress" ${task.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                            <option value="Review" ${task.status === 'Review' ? 'selected' : ''}>Review</option>
                            <option value="Done" ${task.status === 'Done' ? 'selected' : ''}>Done</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Assign To</label>
                        <select name="assigned_to">
                            <option value="">Unassigned</option>
                            ${members.map(m => `<option value="${m.id}" ${task.assigned_to == m.id ? 'selected' : ''}>${m.full_name}</option>`).join('')}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Due Date</label>
                        <input type="date" name="due_date" value="${task.due_date || ''}">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Task</button>
                </div>
            </form>
        </div>
        ` : ''}
        
        ${data.can_delete ? `
        <div class="task-detail-section">
            <h3>Danger Zone</h3>
            <button class="btn btn-danger" onclick="deleteTask(${task.id})">Delete Task</button>
        </div>
        ` : ''}
        
        <div class="comments-section">
            <h3>Comments</h3>
            <form class="comment-form" onsubmit="addComment(event, ${task.id})">
                <textarea id="commentText" placeholder="Add a comment..." required></textarea>
                <button type="submit" class="btn btn-primary">Post Comment</button>
            </form>
            <div class="comments-list">
                ${comments.map(comment => `
                    <div class="comment-item" data-comment-id="${comment.id}">
                        <div class="comment-avatar">
                            <img src="${comment.profile_picture ? 'uploads/profiles/' + comment.profile_picture : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(comment.full_name) + '&background=random&size=32'}" alt="${comment.full_name}">
                        </div>
                        <div class="comment-content">
                            <div class="comment-header">
                                <span class="comment-author">${comment.full_name}</span>
                                <span class="comment-time">${new Date(comment.created_at).toLocaleString()}</span>
                            </div>
                            <p class="comment-text">${esc(comment.comment)}</p>
                            <div class="comment-actions">
                                <button onclick="editComment(${comment.id})">Edit</button>
                                <button onclick="deleteComment(${comment.id})">Delete</button>
                            </div>
                        </div>
                    </div>
                `).join('')}
                ${comments.length === 0 ? '<p>No comments yet</p>' : ''}
            </div>
        </div>
        
        <div class="task-detail-section">
            <h3>Activity History</h3>
            <div class="activity-timeline">
                ${activity.map(act => `
                    <div class="activity-timeline-item">
                        <div class="activity-timeline-icon">📝</div>
                        <div class="activity-timeline-content">
                            <p><strong>${act.full_name}</strong> ${act.description}</p>
                            <p class="activity-timeline-time">${new Date(act.created_at).toLocaleString()}</p>
                        </div>
                    </div>
                `).join('')}
                ${activity.length === 0 ? '<p>No activity yet</p>' : ''}
            </div>
        </div>
    `;
    
    modalBody.innerHTML = html;
    
    // Initialize edit form
    const editForm = document.getElementById('editTaskForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            updateTask(this);
        });
    }
}

function updateTask(form) {
    const formData = new FormData(form);
    
    fetch('tasks/edit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Task updated successfully');
            location.reload();
        } else {
            alert('Failed to update task: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update task');
    });
}

function deleteTask(taskId) {
    if (!confirm('Are you sure you want to delete this task?')) return;
    
    const formData = new FormData();
    formData.append('task_id', taskId);
    
    fetch('tasks/delete.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Task deleted successfully');
            closeTaskModal();
            location.reload();
        } else {
            alert('Failed to delete task: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete task');
    });
}

// Comments
function addComment(event, taskId) {
    event.preventDefault();
    
    const commentText = document.getElementById('commentText').value;
    const formData = new FormData();
    formData.append('task_id', taskId);
    formData.append('comment', commentText);
    
    fetch('comments/create.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('commentText').value = '';
            openTaskModal(taskId); // Reload modal to show new comment
        } else {
            alert('Failed to add comment: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add comment');
    });
}

function editComment(commentId) {
    const newComment = prompt('Enter new comment:');
    if (!newComment) return;
    
    const formData = new FormData();
    formData.append('comment_id', commentId);
    formData.append('comment', newComment);
    
    fetch('comments/edit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Comment updated successfully');
            location.reload();
        } else {
            alert('Failed to update comment: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update comment');
    });
}

function deleteComment(commentId) {
    if (!confirm('Are you sure you want to delete this comment?')) return;
    
    const formData = new FormData();
    formData.append('comment_id', commentId);
    
    fetch('comments/delete.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Comment deleted successfully');
            location.reload();
        } else {
            alert('Failed to delete comment: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete comment');
    });
}

// Create Task Modal
function openCreateTaskModal() {
    const modal = document.getElementById('createTaskModal');
    modal.classList.add('active');
}

function closeCreateTaskModal() {
    const modal = document.getElementById('createTaskModal');
    modal.classList.remove('active');
    document.getElementById('createTaskForm').reset();
}

function initCreateTaskForm() {
    const form = document.getElementById('createTaskForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            
            fetch('tasks/create.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Task created successfully');
                    closeCreateTaskModal();
                    location.reload();
                } else {
                    alert('Failed to create task: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to create task');
            });
        });
    }
}

// Notifications
function pollNotifications() {
    setInterval(() => {
        fetch('api/notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotificationBadge(data.unread_count);
                }
            })
            .catch(error => {
                console.error('Error polling notifications:', error);
            });
    }, 30000); // Poll every 30 seconds
}

function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-badge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline';
        } else {
            badge.style.display = 'none';
        }
    }
}

// Close modals on outside click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
});

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.active').forEach(modal => {
            modal.classList.remove('active');
        });
    }
});

// Dark Mode
(function() {
    const saved = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);

    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('themeToggle');
        if (!btn) return;

        function updateIcon() {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            btn.textContent = isDark ? '☀️' : '🌙';
            btn.title = isDark ? 'Switch to light mode' : 'Switch to dark mode';
        }

        updateIcon();

        btn.addEventListener('click', function() {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            updateIcon();
        });
    });
})();
