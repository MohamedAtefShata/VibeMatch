<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type User } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import './AdminUserManagement.css';

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
    success?: string;
    error?: string;
}

const props = defineProps<Props>();

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
const searchQuery = ref('');
const confirmModal = ref({
    show: false,
    user: null as User | null,
    action: '' as 'delete' | 'promote' | 'demote'
});

// Flash message display
const notificationMessage = computed(() => props.success || props.error || '');
const notificationType = computed(() => props.success ? 'success' : 'error');
const showNotification = computed(() => !!notificationMessage.value);

// User form using Inertia useForm
const form = useForm({
    name: '',
    email: '',
    is_admin: false,
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

// Filtered users based on search query
const filteredUsers = computed(() => {
    const users = usersData.value || [];

    if (!searchQuery.value) return users;

    return users.filter(user => {
        // Filter by search query if present
        const query = searchQuery.value.toLowerCase();
        return !searchQuery.value ||
            user.name.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query);
    });
});

// Methods
const resetForm = () => {
    form.reset();
};

const addUser = () => {
    form.post('/admin/users', {
        onSuccess: () => {
            resetForm();
        }
    });
};

const editUser = (user: User) => {
    editMode.value = true;
    currentUserId.value = user.id;

    // Populate form with user data
    form.name = user.name;
    form.email = user.email;
    form.is_admin = user.is_admin;
    form.password = '';
    form.password_confirmation = '';

    // Scroll to form
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const updateUser = () => {
    if (!currentUserId.value) return;

    form.put(`/admin/users/${currentUserId.value}`, {
        onSuccess: () => {
            cancelEdit();
        }
    });
};

const cancelEdit = () => {
    editMode.value = false;
    currentUserId.value = null;
    resetForm();
};

const confirmAction = (user: User, action: 'delete' | 'promote' | 'demote') => {
    confirmModal.value = {
        show: true,
        user,
        action
    };
};

const executeAction = () => {
    if (!confirmModal.value.user) return;

    const { user, action } = confirmModal.value;

    switch (action) {
        case 'delete':
            useForm({}).delete(`/admin/users/${user.id}`);
            break;

        case 'promote':
            useForm({ is_admin: true }).put(`/admin/users/${user.id}/role`);
            break;

        case 'demote':
            useForm({ is_admin: false }).put(`/admin/users/${user.id}/role`);
            break;
    }

    confirmModal.value.show = false;
};
</script>

<template>
    <Head title="User Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="admin-container">
            <!-- Success/Error Messages -->
            <div v-if="showNotification" :class="['notification', notificationType]">
                {{ notificationMessage }}
                <button class="close-btn">&times;</button>
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
                        <div v-if="form.errors.name" class="form-error">{{ form.errors.name }}</div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                        />
                        <div v-if="form.errors.email" class="form-error">{{ form.errors.email }}</div>
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
                        <div v-if="form.errors.is_admin" class="form-error">{{ form.errors.is_admin }}</div>
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
                        <div v-if="form.errors.password" class="form-error">{{ form.errors.password }}</div>
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
                            :disabled="form.processing"
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

                <!-- Search Box -->
                <div class="filter-container">
                    <div class="search-box">
                        <input
                            type="text"
                            placeholder="Search users..."
                            v-model="searchQuery"
                        />
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

                                <!-- Delete button -->
                                <button @click="confirmAction(user, 'delete')" class="btn btn-delete">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <tr v-if="filteredUsers.length === 0">
                            <td colspan="6" class="no-records">No users found</td>
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
                        <Link
                            v-for="link in paginationLinks"
                            :key="link.url || link.label"
                            :href="link.url || '#'"
                            :class="['page-link', { active: link.active, disabled: !link.url }]"
                            v-html="link.label"
                            :disabled="!link.url || link.active"
                        ></Link>
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
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Processing...' : 'Confirm' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
