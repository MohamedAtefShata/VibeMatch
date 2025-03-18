<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import './AdminDashboard.css';

// Define prop types with defaults
interface Song {
    id: number;
    title: string;
    artist: string;
    album?: string;
    genre?: string;
    year?: number;
    duration?: number;
    cover_image?: string;
    image_url?: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Pagination {
    current_page: number;
    data: Song[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

interface Props {
    songs?: Pagination | null;
    isAdmin?: boolean;
}

// Define props with defaults to prevent undefined errors
const props = defineProps<Props>();

// Default empty data when songs is not provided
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
        title: "Admin Dashboard",
        href: '/admin/songs',
    }
];

// Form state
const form = ref({
    title: '',
    artist: '',
    album: '',
    genre: '',
    year: null as number | null,
    duration: null as number | null,
    cover_image: ''
});

// UI state
const editMode = ref(false);
const currentSongId = ref<number | null>(null);
const processing = ref(false);
const searchQuery = ref('');
const notification = ref({
    show: false,
    type: 'success' as 'success' | 'error',
    message: ''
});
const deleteModal = ref({
    show: false,
    song: null as Song | null
});

// Get songs data safely
const songsData = computed(() => {
    return props.songs?.data || emptyPagination.data;
});

// Total songs count safely
const totalSongs = computed(() => {
    return props.songs?.total || 0;
});

// Links safely
const paginationLinks = computed(() => {
    return props.songs?.links || emptyPagination.links;
});

// Computed
const filteredSongs = computed(() => {
    const songs = songsData.value || [];

    if (!searchQuery.value) return songs;

    const query = searchQuery.value.toLowerCase();
    return songs.filter(song =>
        song.title?.toLowerCase().includes(query) ||
        song.artist?.toLowerCase().includes(query) ||
        song.album?.toLowerCase().includes(query) ||
        song.genre?.toLowerCase().includes(query)
    );
});

// Methods
const resetForm = () => {
    form.value = {
        title: '',
        artist: '',
        album: '',
        genre: '',
        year: null,
        duration: null,
        cover_image: ''
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

const addSong = async () => {
    processing.value = true;

    try {
        await axios.post('/api/songs', form.value);

        // Refresh the songs list
        window.location.reload();

        resetForm();
        showNotification('success', 'Song added successfully!');
    } catch (error: any) {
        console.error('Failed to add song:', error);
        showNotification('error', error.response?.data?.error || 'Failed to add song');
    } finally {
        processing.value = false;
    }
};

const editSong = (song: Song) => {
    editMode.value = true;
    currentSongId.value = song.id;

    // Populate form with song data
    form.value = {
        title: song.title || '',
        artist: song.artist || '',
        album: song.album || '',
        genre: song.genre || '',
        year: song.year || null,
        duration: song.duration || null,
        cover_image: song.cover_image || song.image_url || ''
    };

    // Scroll to form
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const updateSong = async () => {
    if (!currentSongId.value) return;

    processing.value = true;

    try {
        await axios.put(`/api/songs/${currentSongId.value}`, form.value);

        // Refresh the songs list
        window.location.reload();

        cancelEdit();
        showNotification('success', 'Song updated successfully!');
    } catch (error: any) {
        console.error('Failed to update song:', error);
        showNotification('error', error.response?.data?.error || 'Failed to update song');
    } finally {
        processing.value = false;
    }
};

const cancelEdit = () => {
    editMode.value = false;
    currentSongId.value = null;
    resetForm();
};

const confirmDelete = (song: Song) => {
    deleteModal.value = {
        show: true,
        song
    };
};

const deleteSong = async () => {
    if (!deleteModal.value.song) return;

    processing.value = true;

    try {
        await axios.delete(`/api/songs/${deleteModal.value.song.id}`);

        // Refresh the songs list
        window.location.reload();

        deleteModal.value.show = false;
        showNotification('success', 'Song deleted successfully!');
    } catch (error: any) {
        console.error('Failed to delete song:', error);
        showNotification('error', error.response?.data?.error || 'Failed to delete song');
    } finally {
        processing.value = false;
    }
};

const goToPage = (url: string | null) => {
    if (!url) return;

    window.location.href = url;
};
</script>

<template>
    <Head title="Manage Songs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="admin-container">
            <!-- Success/Error Messages -->
            <div v-if="notification.show" :class="['notification', notification.type]">
                {{ notification.message }}
                <button @click="notification.show = false" class="close-btn">&times;</button>
            </div>

            <!-- Add/Edit Song Form -->
            <div class="form-container">
                <h2 class="section-header">{{ editMode ? 'Edit Song' : 'Add New Song' }}</h2>
                <form @submit.prevent="editMode ? updateSong() : addSong()">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input
                            id="title"
                            type="text"
                            v-model="form.title"
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label for="artist">Artist</label>
                        <input
                            id="artist"
                            type="text"
                            v-model="form.artist"
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label for="album">Album</label>
                        <input
                            id="album"
                            type="text"
                            v-model="form.album"
                        />
                    </div>

                    <div class="form-group">
                        <label for="genre">Genre</label>
                        <input
                            id="genre"
                            type="text"
                            v-model="form.genre"
                        />
                    </div>

                    <div class="form-group">
                        <label for="year">Year</label>
                        <input
                            id="year"
                            type="number"
                            v-model="form.year"
                        />
                    </div>

                    <div class="form-group">
                        <label for="duration">Duration (seconds)</label>
                        <input
                            id="duration"
                            type="number"
                            v-model="form.duration"
                        />
                    </div>

                    <div class="form-group">
                        <label for="cover_image">Cover Image URL</label>
                        <input
                            id="cover_image"
                            type="text"
                            v-model="form.cover_image"
                        />
                    </div>

                    <div class="form-buttons">
                        <button
                            type="submit"
                            class="btn btn-primary"
                            :disabled="processing"
                        >
                            {{ editMode ? 'Update Song' : 'Add Song' }}
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

            <!-- Song List -->
            <div class="songs-container">
                <h2 class="section-header">Manage Songs</h2>

                <!-- Search Box -->
                <div class="search-box">
                    <input
                        type="text"
                        placeholder="Search songs..."
                        v-model="searchQuery"
                    />
                </div>

                <!-- Songs Table -->
                <div class="table-container">
                    <table class="songs-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Artist</th>
                            <th>Album</th>
                            <th>Genre</th>
                            <th>Year</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="song in filteredSongs" :key="song.id">
                            <td>{{ song.id }}</td>
                            <td>{{ song.title }}</td>
                            <td>{{ song.artist }}</td>
                            <td>{{ song.album }}</td>
                            <td>{{ song.genre }}</td>
                            <td>{{ song.year }}</td>
                            <td class="actions">
                                <button
                                    @click="editSong(song)"
                                    class="btn btn-edit"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="confirmDelete(song)"
                                    class="btn btn-delete"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <tr v-if="filteredSongs.length === 0">
                            <td colspan="7" class="no-records">No songs found</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination with enhanced safeguards -->
                <div class="pagination">
                    <div class="page-info">
                        Showing {{ songsData.length }} of {{ totalSongs }} songs
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

            <!-- Delete Confirmation Modal -->
            <div v-if="deleteModal.show" class="modal-overlay">
                <div class="modal-container">
                    <h3 class="modal-header">Confirm Delete</h3>
                    <p>Are you sure you want to delete the song "{{ deleteModal.song?.title }}" by {{ deleteModal.song?.artist }}?</p>
                    <p class="warning-text">This action cannot be undone.</p>
                    <div class="modal-buttons">
                        <button
                            @click="deleteModal.show = false"
                            class="btn btn-secondary"
                        >
                            Cancel
                        </button>
                        <button
                            @click="deleteSong()"
                            class="btn btn-delete"
                            :disabled="processing"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
