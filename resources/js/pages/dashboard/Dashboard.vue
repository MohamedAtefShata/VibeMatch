<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import './Dashboard.css';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Recommend Songs",
        href: '/dashboard',
    },
];

const searchQuery = ref('');
const searchResults = ref([]);
const selectedSongs = ref([]);
const recommendation = ref(null);
const isLoading = ref(false);
const previousRecommendations = ref([]);

// Fetch previous recommendations on component mount
onMounted(async () => {
    try {
        const response = await axios.get('/api/songs/previous-recommendations');
        previousRecommendations.value = response.data;
    } catch (error) {
        console.error('Error fetching previous recommendations:', error);
    }
});

const searchSongs = async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }

    try {
        const response = await axios.get(`/api/songs/search?query=${searchQuery.value}`);
        searchResults.value = response.data;
    } catch (error) {
        console.error('Error searching songs:', error);
    }
};

const addToSelection = (song) => {
    // Check if song is already selected
    if (!selectedSongs.value.some(s => s.id === song.id)) {
        selectedSongs.value.push(song);
        searchQuery.value = '';
        searchResults.value = [];
    }
};

const removeFromSelection = (song) => {
    selectedSongs.value = selectedSongs.value.filter(s => s.id !== song.id);
};

const getRecommendation = async () => {
    isLoading.value = true;
    try {
        const songIds = selectedSongs.value.map(song => song.id);
        const response = await axios.post('/api/songs/recommend', { songIds });
        recommendation.value = response.data;

        // Add to previous recommendations
        const newRecommendation = {
            id: Date.now(), // Temporary ID until backend provides one
            recommendedSong: response.data[0],
            basedOn: [...selectedSongs.value],
            timestamp: new Date().toISOString(),
            liked: null // null = not rated, true = liked, false = disliked
        };

        previousRecommendations.value.unshift(newRecommendation);
    } catch (error) {
        console.error('Error getting recommendation:', error);
    } finally {
        isLoading.value = false;
    }
};

</script>

<template>
    <Head title="Recommend Songs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container">
            <!-- Central Square Container -->
            <div class="central-square">
                <!-- Recommendation Result (shows at top when available) -->
                <div v-if="recommendation" class="recommendation-section">
                    <h2 class="section-title">Recommended Song</h2>
                    <div class="recommendation-content">
                        <img :src="recommendation[0].image_url" alt="Album Cover" class="album-cover" />
                        <div class="song-details">
                            <h3 class="song-title">{{ recommendation[0].title }}</h3>
                            <p class="artist-name">{{ recommendation[0].artist }}</p>
                            <div class="button-group">
                                <button class="btn btn-primary">Play Now</button>
                                <button class="btn btn-secondary">Add to Playlist</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="main-content">
                    <!-- Search Bar -->
                    <div class="search-section">
                        <h2 class="section-title">Search Songs</h2>
                        <div class="search-container">
                            <input
                                type="text"
                                v-model="searchQuery"
                                @input="searchSongs"
                                placeholder="Search for songs..."
                                class="search-input"
                            />

                            <!-- Search Results Dropdown -->
                            <div v-if="searchResults.length > 0" class="search-results">
                                <div
                                    v-for="song in searchResults"
                                    :key="song.id"
                                    class="search-result-item"
                                    @click="addToSelection(song)"
                                >
                                    <div class="result-text">{{ song.title }} - {{ song.artist }}</div>
                                    <button class="add-button">Add</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Songs -->
                    <div class="selected-section">
                        <h2 class="section-title">Selected Songs</h2>
                        <div class="selected-container">
                            <div v-if="selectedSongs.length > 0">
                                <div
                                    v-for="song in selectedSongs"
                                    :key="song.id"
                                    class="selected-item"
                                >
                                    <div class="song-text">{{ song.title }} - {{ song.artist }}</div>
                                    <button @click="removeFromSelection(song)" class="remove-button">
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <div v-else class="empty-selection">
                                No songs selected yet
                            </div>
                        </div>
                    </div>

                    <!-- Recommendation Button -->
                    <div class="button-section">
                        <button
                            @click="getRecommendation"
                            class="recommend-button"
                            :disabled="selectedSongs.length < 1 || isLoading"
                            :class="{ 'disabled': selectedSongs.length < 1 || isLoading }"
                        >
                            <span v-if="isLoading">Finding recommendation...</span>
                            <span v-else>Get Recommendation</span>
                        </button>

                    </div>

                    <!-- Previous Recommendations -->
                    <div class="previous-recommendations-section">
                        <h2 class="section-title">Previous Recommendations</h2>
                        <div class="previous-recommendations-container">
                            <div v-if="previousRecommendations.length > 0">
                                <div
                                    v-for="item in previousRecommendations"
                                    :key="item.id"
                                    class="recommendation-item"
                                >
                                    <div class="recommendation-item-content">
                                        <div class="recommendation-left">
                                            <img
                                                :src="item.recommendedSong.image_url"
                                                alt="Album Cover"
                                                class="recommendation-thumbnail"
                                            />
                                        </div>
                                        <div class="recommendation-details">
                                            <h4 class="recommendation-title">{{ item.recommendedSong.title }}</h4>
                                            <p class="recommendation-artist">{{ item.recommendedSong.artist }}</p>
                                            <div class="recommendation-based-on">
                                                <p class="based-on-text">Based on: </p>
                                                <div class="based-on-pills">
                                                    <span
                                                        v-for="(song) in item.basedOn.slice(0, 3)"
                                                        :key="song.id"
                                                        class="based-on-pill"
                                                    >
                                                        {{ song.title }}
                                                    </span>
                                                    <span v-if="item.basedOn.length > 3" class="based-on-pill more">
                                                        +{{ item.basedOn.length - 3 }} more
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="recommendation-actions">
                                            <button
                                                class="rating-button like"
                                                :class="{ 'active': item.liked === true }"
                                            >
                                                👍
                                            </button>
                                            <button
                                                class="rating-button dislike"
                                                :class="{ 'active': item.liked === false }"
                                            >
                                                👎
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="empty-recommendations">
                                No previous recommendations yet
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
