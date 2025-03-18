<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

// Define user type
interface User {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    created_at: string;
    updated_at: string;
    last_login?: string;
    status: 'active' | 'inactive' | 'suspended';
}

// Define pagination interface
interface Pagination {
    current_page: number;
    data: User[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: { url: string | null; label: string; active: boolean }[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

interface Props {
    users?: Pagination | null;
    isAdmin?: boolean;
}

// Define props with defaults
const props = withDefaults(defineProps<Props>(), {
    isAdmin: true
});

// Default empty pagination when users is not provided
const emptyPagination: Pagination = {
    current_page: 1,
    data: [],
    first_page_url: '',
    from: 1,
    last_page: 1,
    last_page_url: '',
    links: [],
    next_page_url: null,
    path: '',
    per_page: 15,
    prev_page_url: null,
    to: 0,
    total: 0
};

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Dashboard",
        href: '/dashboard',
    },
    {
        title: "User Management",
        href: '/admin/users',
    }
];

// UI state
const editMode = ref(false);
const currentUserId = ref<number | null>(null);
const processing = ref(false);
const searchQuery = ref('');
const statusFilter = ref('all');
const notification = ref({
    show: false,
    type: 'success' as 'success' | 'error',
    message: ''
});
const confirmModal = ref({
    show: false,
    user: null as User | null,
    action: '' as 'delete' | 'suspend' | 'activate' | 'promote' | 'demote'
});

// User form
const form = ref({
    name: '',
    email: '',
    is_admin: false,
    status: 'active' as 'active' | 'inactive' | 'suspended',
    password: '',
    password_confirmation: ''
});

// Get users data safely
const usersData = computed(() => {
    return props.users?.data || emptyPagination.data;
});

// Total users count
const totalUsers = computed(() => {
    return props.users?.total || 0;
});

// Pagination links
const paginationLinks = computed(() => {
    return props.users?.links || emptyPagination.links;
});

// Filtered users based on search query and status filter
const filteredUsers = computed(() => {
    const users = usersData.value || [];

    if (!searchQuery.value && statusFilter.value === 'all') return users;

    return users.filter(user => {
        // Filter by status if not 'all'
        const statusMatch = statusFilter.value === 'all' ||
            user.status === statusFilter.value;

        // Filter by search query if present
        const query = searchQuery.value.toLowerCase();
        const searchMatch = !searchQuery.value ||
            user.name.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query);

        return statusMatch && searchMatch;
    });
});

// Methods
const resetForm = () => {
    form.value = {
        name: '',
        email: '',
        is_admin: false,
        status: 'active',
        password: '',
        password_confirmation: ''
    };
};

const showNotification = (type: 'success' | 'error', message: string) => {
    notification.value = {
        show: true,
        type,
        message
    };

    // Auto-hide after 5 seconds
    setTimeout(() => {
        notification.value.show = false;
    }, 5000);
};

const addUser = async () => {
    processing.value = true;

    try {
        await axios.post('/api/users', form.value);

        // Refresh the users list
        window.location.reload();

        resetForm();
        showNotification('success', 'User added successfully!');
    } catch (error: any) {
        console.error('Failed to add user:', error);
        showNotification('error', error.response?.data?.message || 'Failed to add user');
    } finally {
        processing.value = false;
    }
};

const editUser = (user: User) => {
    editMode.value = true;
    currentUserId.value = user.id;

    // Populate form with user data
    form.value = {
        name: user.name,
        email: user.email,
        is_admin: user.is_admin,
        status: user.status,
        password: '',
        password_confirmation: ''
    };

    // Scroll to form
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const updateUser = async () => {
    if (!currentUserId.value) return;

    processing.value = true;

    try {
        await axios.put(`/api/users/${currentUserId.value}`, form.value);

        // Refresh the users list
        window.location.reload();

        cancelEdit();
        showNotification('success', 'User updated successfully!');
    } catch (error: any) {
        console.error('Failed to update user:', error);
        showNotification('error', error.response?.data?.message || 'Failed to update user');
    } finally {
        processing.value = false;
    }
};

const cancelEdit = () => {
    editMode.value = false;
    currentUserId.value = null;
    resetForm();
};

const confirmAction = (user: User, action: 'delete' | 'suspend' | 'activate' | 'promote' | 'demote') => {
    confirmModal.value = {
        show: true,
        user,
        action
    };
};

const executeAction = async () => {
    if (!confirmModal.value.user) return;

    const { user, action } = confirmModal.value;
    processing.value = true;

    try {
        switch (action) {
            case 'delete':
                await axios.delete(`/api/users/${user.id}`);
                showNotification('success', `User ${user.name} deleted successfully!`);
                break;

            case 'suspend':
                await axios.put(`/api/users/${user.id}/status`, { status: 'suspended' });
                showNotification('success', `User ${user.name} has been suspended!`);
                break;

            case 'activate':
                await axios.put(`/api/users/${user.id}/status`, { status: 'active' });
                showNotification('success', `User ${user.name} has been activated!`);
                break;

            case 'promote':
                await axios.put(`/api/users/${user.id}/role`, { is_admin: true });
                showNotification('success', `${user.name} has been promoted to admin!`);
                break;

            case 'demote':
                await axios.put(`/api/users/${user.id}/role`, { is_admin: false });
                showNotification('success', `${user.name} has been demoted from admin!`);
                break;
        }

        // Refresh the users list
        window.location.reload();
    } catch (error: any) {
        console.error(`Failed to ${action} user:`, error);
        showNotification('error', error.response?.data?.message || `Failed to ${action} user`);
    } finally {
        processing.value = false;
        confirmModal.value.show = false;
    }
};

const goToPage = (url: string | null) => {
    if (!url) return;
    window.location.href = url;
};

// Get status color class
const getStatusClass = (status: string) => {
    switch (status) {
        case 'active': return 'status-active';
        case 'inactive': return 'status-inactive';
        case 'suspended': return 'status-suspended';
        default: return '';
    }
};
</script>

<template>
    <Head title="User Management" />

    <AppLayout :breadcrumbs="breadcrumbs" :isAdmin="isAdmin">
        <div class="admin-container">
            <!-- Success/Error Messages -->
            <div v-if="notification.show" :class="['notification', notification.type]">
                {{ notification.message }}
                <button @click="notification.show = false" class="close-btn">&times;</button>
            </div>

            <!-- Add/Edit User Form -->
            <div class="form-container">
                <h2 class="section-header">{{ editMode ? 'Edit User' : 'Add New User' }}</h2>
                <form @submit.prevent="editMode ? updateUser() : addUser()">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" v-model="form.status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="is_admin">Admin Privileges</label>
                        <div class="checkbox-wrapper">
                            <input
                                id="is_admin"
                                type="checkbox"
                                v-model="form.is_admin"
                            />
                            <span>Grant admin privileges</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">
                            {{ editMode ? 'New Password (leave blank to keep current)' : 'Password' }}
                        </label>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            :required="!editMode"
                        />
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            v-model="form.password_confirmation"
                            :required="!editMode"
                        />
                    </div>

                    <div class="form-buttons">
                        <button
                            type="submit"
                            class="btn btn-primary"
                            :disabled="processing"
                        >
                            {{ editMode ? 'Update User' : 'Add User' }}
                        </button>
                        <button
                            v-if="editMode"
                            type="button"
                            class="btn btn-secondary"
                            @click="cancelEdit"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- User List -->
            <div class="users-container">
                <h2 class="section-header">Manage Users</h2>

                <!-- Search and Filter Box -->
                <div class="filter-container">
                    <div class="search-box">
                        <input
                            type="text"
                            placeholder="Search users..."
                            v-model="searchQuery"
                        />
                    </div>
                    <div class="status-filter">
                        <label for="status-filter">Filter by status:</label>
                        <select id="status-filter" v-model="statusFilter">
                            <option value="all">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="table-container">
                    <table class="users-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="user in filteredUsers" :key="user.id">
                            <td>{{ user.id }}</td>
                            <td>{{ user.name }}</td>
                            <td>{{ user.email }}</td>
                            <td>
                                    <span :class="['role-badge', user.is_admin ? 'admin' : 'user']">
                                        {{ user.is_admin ? 'Admin' : 'User' }}
                                    </span>
                            </td>
                            <td>
                                    <span :class="['status-badge', getStatusClass(user.status)]">
                                        {{ user.status }}
                                    </span>
                            </td>
                            <td>{{ new Date(user.created_at).toLocaleDateString() }}</td>
                            <td class="actions">
                                <button @click="editUser(user)" class="btn btn-edit">
                                    Edit
                                </button>

                                <!-- Role toggle -->
                                <button
                                    v-if="!user.is_admin"
                                    @click="confirmAction(user, 'promote')"
                                    class="btn btn-promote"
                                >
                                    Make Admin
                                </button>
                                <button
                                    v-else
                                    @click="confirmAction(user, 'demote')"
                                    class="btn btn-demote"
                                >
                                    Remove Admin
                                </button>

                                <!-- Status toggle -->
                                <button
                                    v-if="user.status !== 'suspended'"
                                    @click="confirmAction(user, 'suspend')"
                                    class="btn btn-suspend"
                                >
                                    Suspend
                                </button>
                                <button
                                    v-else
                                    @click="confirmAction(user, 'activate')"
                                    class="btn btn-activate"
                                >
                                    Activate
                                </button>

                                <!-- Delete button -->
                                <button @click="confirmAction(user, 'delete')" class="btn btn-delete">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <tr v-if="filteredUsers.length === 0">
                            <td colspan="7" class="no-records">No users found</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <div class="page-info">
                        Showing {{ usersData.length }} of {{ totalUsers }} users
                    </div>
                    <div class="page-links">
                        <button
                            v-for="link in paginationLinks"
                            :key="link.url || link.label"
                            @click="goToPage(link.url)"
                            :disabled="!link.url || link.active"
                            :class="['page-link', { active: link.active, disabled: !link.url }]"
                            v-html="link.label"
                        ></button>
                    </div>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <div v-if="confirmModal.show" class="modal-overlay">
                <div class="modal-container">
                    <h3 class="modal-header">Confirm Action</h3>

                    <!-- Content for delete action -->
                    <div v-if="confirmModal.action === 'delete'">
                        <p>Are you sure you want to delete the user <strong>{{ confirmModal.user?.name }}</strong>?</p>
                        <p class="warning-text">This action cannot be undone and will remove all data associated with this user.</p>
                    </div>

                    <!-- Content for suspend action -->
                    <div v-else-if="confirmModal.action === 'suspend'">
                        <p>Are you sure you want to suspend <strong>{{ confirmModal.user?.name }}</strong>?</p>
                        <p>Suspended users cannot log in or use the platform until they are reactivated.</p>
                    </div>

                    <!-- Content for activate action -->
                    <div v-else-if="confirmModal.action === 'activate'">
                        <p>Are you sure you want to activate <strong>{{ confirmModal.user?.name }}</strong>?</p>
                        <p>This will restore full access to the platform for this user.</p>
                    </div>

                    <!-- Content for promote action -->
                    <div v-else-if="confirmModal.action === 'promote'">
                        <p>Are you sure you want to promote <strong>{{ confirmModal.user?.name }}</strong> to admin?</p>
                        <p class="warning-text">Admins have full access to manage all users, songs, and system settings.</p>
                    </div>

                    <!-- Content for demote action -->
                    <div v-else-if="confirmModal.action === 'demote'">
                        <p>Are you sure you want to remove admin privileges from <strong>{{ confirmModal.user?.name }}</strong>?</p>
                        <p>They will no longer have access to admin features and dashboards.</p>
                    </div>

                    <div class="modal-buttons">
                        <button
                            @click="confirmModal.show = false"
                            class="btn btn-secondary"
                        >
                            Cancel
                        </button>
                        <button
                            @click="executeAction()"
                            :class="['btn', `btn-${confirmModal.action}`]"
                            :disabled="processing"
                        >
                            {{ processing ? 'Processing...' : 'Confirm' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Basic container styling */
.admin-container {
    padding: 1.5rem;
    max-width: 1200px;
    margin: 0 auto;
}

/* Section headers */
.section-header {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 0.5rem;
}

/* Notifications */
.notification {
    padding: 1rem;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    padding: 0 0.5rem;
}

/* Form styling */
.form-container {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.375rem;
    font-weight: 500;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"],
.form-group select {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
}

.checkbox-wrapper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-buttons {
    display: flex;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

/* Button styles */
.btn {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: background-color 0.2s;
}

.btn-primary {
    background-color: #4f46e5;
    color: white;
}

.btn-primary:hover {
    background-color: #4338ca;
}

.btn-secondary {
    background-color: #e5e7eb;
    color: #374151;
}

.btn-secondary:hover {
    background-color: #d1d5db;
}

.btn-edit {
    background-color: #3b82f6;
    color: white;
}

.btn-edit:hover {
    background-color: #2563eb;
}

.btn-delete, .btn-suspend {
    background-color: #ef4444;
    color: white;
}

.btn-delete:hover, .btn-suspend:hover {
    background-color: #dc2626;
}

.btn-promote {
    background-color: #8b5cf6;
    color: white;
}

.btn-promote:hover {
    background-color: #7c3aed;
}

.btn-demote {
    background-color: #6b7280;
    color: white;
}

.btn-demote:hover {
    background-color: #4b5563;
}

.btn-activate {
    background-color: #10b981;
    color: white;
}

.btn-activate:hover {
    background-color: #059669;
}

/* Users container */
.users-container {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    margin-bottom: 2rem;
}

/* Filter and search */
.filter-container {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.search-box input {
    width: 300px;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
}

.status-filter {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-filter select {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
}

/* Table styling */
.table-container {
    overflow-x: auto;
    margin-bottom: 1.5rem;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
}

.users-table th,
.users-table td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.users-table th {
    background-color: #f9fafb;
    font-weight: 600;
}

.users-table tbody tr:hover {
    background-color: #f9fafb;
}

.actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.actions .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.no-records {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
    font-style: italic;
}

/* Badges */
.role-badge, .status-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-info {
    color: #6b7280;
    font-size: 0.875rem;
}

.page-links {
    display: flex;
    gap: 0.25rem;
}

.page-link {
    padding: 0.375rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background-color: white;
    color: #374151;
    cursor: pointer;
    font-size: 0.875rem;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 50;
}

.modal-container {
    background-color: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.modal-header {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.warning-text {
    color: #991b1b;
    font-weight: 500;
    margin: 0.75rem 0;
}

.modal-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .filter-container {
        flex-direction: column;
        align-items: flex-start;
    }

    .search-box input {
        width: 100%;
    }

    .actions {
        flex-direction: column;
    }

    .pagination {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
