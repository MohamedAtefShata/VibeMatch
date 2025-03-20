<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import './Personalized.css';

// Define props coming from the server
const props = defineProps({
    profile: Object,
    recommendations: Object,
    recommendationHistory: Array,
    success: Boolean,
    error: String
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Personalized",
        href: '/personalized',
    },
];

// User's listening history and preferences
const userProfile = ref({
    favoriteGenres: props.profile?.favoriteGenres || [],
    recentPlays: props.profile?.recentPlays || [],
    topArtists: props.profile?.topArtists || [],
    loading: false
});

// Personalized recommendations
const recommendations = ref({
    forYou: props.recommendations?.forYou || [],
    basedOnGenre: props.recommendations?.basedOnGenre || [],
    newReleases: props.recommendations?.newReleases || [],
    loading: false
});

// Recommendation history
const recommendationHistory = ref({
    items: props.recommendationHistory || [],
    loading: false
});

// Show error message if exists
const errorMessage = ref(props.error || null);

// Form for rating recommendations
const rateForm = useForm({
    rating: null
});

// Rate a recommendation
const rateRecommendation = (id: number, rating: number) => {
    rateForm.rating = rating;

    // Using Inertia put method
    router.put(`/api/recommendations/${id}/rate`, {
        rating: rating
    }, {
        preserveState: true,
        onSuccess: (page) => {
            // Check if the response indicates success
            if (page.props.success) {
                // Update the rating in our local state
                const recommendation = recommendationHistory.value.items.find(item => item.id === id);
                if (recommendation) {
                    recommendation.liked = rating;
                }

                // Refresh personalized recommendations after rating
                router.reload({ only: ['recommendations'] });
            }
        }
    });
};

// Watch for changes in the props
watch(() => props.profile, (newValue) => {
    if (newValue) {
        userProfile.value = {
            favoriteGenres: newValue.favoriteGenres || [],
            recentPlays: newValue.recentPlays || [],
            topArtists: newValue.topArtists || [],
            loading: false
        };
    }
}, { immediate: true });

watch(() => props.recommendations, (newValue) => {
    if (newValue) {
        recommendations.value = {
            forYou: newValue.forYou || [],
            basedOnGenre: newValue.basedOnGenre || [],
            newReleases: newValue.newReleases || [],
            loading: false
        };
    }
}, { immediate: true });

watch(() => props.recommendationHistory, (newValue) => {
    if (newValue) {
        recommendationHistory.value = {
            items: newValue || [],
            loading: false
        };
    }
}, { immediate: true });

// Watch for error props
watch(() => props.error, (newValue) => {
    errorMessage.value = newValue || null;
}, { immediate: true });
</script>

<template>
    <Head title="Personalized" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="personalized-container">
            <h1 class="page-title">Your Personalized Experience</h1>

            <!-- Error alert if there's an error -->
            <div v-if="errorMessage" class="error-alert">
                {{ errorMessage }}
            </div>

            <!-- Recommendations Section -->
            <section class="recommendations-section">
                <h2 class="section-title">Personalized Recommendations</h2>
                <div v-if="recommendations.loading" class="loading-indicator">
                    Finding the perfect songs for you...
                </div>
                <div v-else class="recommendations-content">
                    <!-- For You -->
                    <div class="recommendation-category">
                        <h3>Made For You</h3>
                        <div class="songs-grid">
                            <div v-for="song in recommendations.forYou" :key="song.id" class="song-card">
                                <img :src="song.image_url" alt="Album Cover" class="song-cover" />
                                <div class="song-info">
                                    <div class="song-title">{{ song.title }}</div>
                                    <div class="song-artist">{{ song.artist }}</div>
                                </div>
                                <button class="play-button">Play</button>
                            </div>
                            <div v-if="!recommendations.forYou || recommendations.forYou.length === 0" class="empty-state">
                                Keep listening to get personalized recommendations!
                            </div>
                        </div>
                    </div>

                    <!-- Based on Genres -->
                    <div class="recommendation-category">
                        <h3>Based on Your Favorite Genres</h3>
                        <div class="songs-grid">
                            <div v-for="song in recommendations.basedOnGenre" :key="song.id" class="song-card">
                                <img :src="song.image_url" alt="Album Cover" class="song-cover" />
                                <div class="song-info">
                                    <div class="song-title">{{ song.title }}</div>
                                    <div class="song-artist">{{ song.artist }}</div>
                                </div>
                                <button class="play-button">Play</button>
                            </div>
                            <div v-if="!recommendations.basedOnGenre || recommendations.basedOnGenre.length === 0" class="empty-state">
                                Explore more genres to get recommendations!
                            </div>
                        </div>
                    </div>

                    <!-- New Releases -->
                    <div class="recommendation-category">
                        <h3>New Releases For You</h3>
                        <div class="songs-grid">
                            <div v-for="song in recommendations.newReleases" :key="song.id" class="song-card">
                                <img :src="song.image_url" alt="Album Cover" class="song-cover" />
                                <div class="song-info">
                                    <div class="song-title">{{ song.title }}</div>
                                    <div class="song-artist">{{ song.artist }}</div>
                                </div>
                                <button class="play-button">Play</button>
                            </div>
                            <div v-if="!recommendations.newReleases || recommendations.newReleases.length === 0" class="empty-state">
                                No new releases matching your taste yet. Check back soon!
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Recommendation History Section -->
            <section class="history-section">
                <h2 class="section-title">Your Recommendation History</h2>
                <div v-if="recommendationHistory.loading" class="loading-indicator">
                    Loading your recommendation history...
                </div>
                <div v-else class="history-content">
                    <div v-if="recommendationHistory.items && recommendationHistory.items.length > 0" class="history-items">
                        <div v-for="item in recommendationHistory.items" :key="item.id" class="history-item">
                            <div class="history-item-content">
                                <div class="history-left">
                                    <img :src="item.recommendedSong.image_url" alt="Album Cover" class="history-thumbnail" />
                                </div>
                                <div class="history-details">
                                    <h4 class="history-title">{{ item.recommendedSong.title }}</h4>
                                    <p class="history-artist">{{ item.recommendedSong.artist }}</p>
                                    <div class="history-based-on">
                                        <p class="based-on-text">Based on: </p>
                                        <div class="based-on-pills">
                                            <span
                                                v-for="(song, index) in item.basedOn.slice(0, 3)"
                                                :key="index"
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
                                <div class="history-actions">
                                    <button
                                        @click="rateRecommendation(item.id, 1)"
                                        class="rating-button like"
                                        :class="{ 'active': item.liked === 1 }"
                                    >
                                        👍
                                    </button>
                                    <button
                                        @click="rateRecommendation(item.id, -1)"
                                        class="rating-button dislike"
                                        :class="{ 'active': item.liked === -1 }"
                                    >
                                        👎
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="empty-state">
                        No recommendation history yet. Start exploring songs!
                    </div>
                </div>
            </section>

            <!-- User Profile Section -->
            <section class="profile-section">
                <h2 class="section-title">Your Music Profile</h2>
                <div v-if="userProfile.loading" class="loading-indicator">
                    Loading your profile...
                </div>
                <div v-else class="profile-content">
                    <!-- Favorite Genres -->
                    <div class="profile-card">
                        <h3>Your Favorite Genres</h3>
                        <div class="genres-list">
                            <span v-for="genre in userProfile.favoriteGenres" :key="genre.name" class="genre-tag">
                                {{ genre.name }}
                            </span>
                            <span v-if="!userProfile.favoriteGenres || userProfile.favoriteGenres.length === 0" class="empty-state">
                                No favorite genres yet. Start listening to more music!
                            </span>
                        </div>
                    </div>

                    <!-- Recently Played -->
                    <div class="profile-card">
                        <h3>Recently Played</h3>
                        <div class="songs-list">
                            <div v-for="song in userProfile.recentPlays" :key="song.id" class="song-item">
                                <img :src="song.image_url" alt="Album Cover" class="song-thumbnail" />
                                <div class="song-details">
                                    <div class="song-title">{{ song.title }}</div>
                                    <div class="song-artist">{{ song.artist }}</div>
                                </div>
                            </div>
                            <div v-if="!userProfile.recentPlays || userProfile.recentPlays.length === 0" class="empty-state">
                                No recently played songs. Start listening!
                            </div>
                        </div>
                    </div>

                    <!-- Top Artists -->
                    <div class="profile-card">
                        <h3>Your Top Artists</h3>
                        <div class="artists-list">
                            <div v-for="artist in userProfile.topArtists" :key="artist.id" class="artist-item">
                                <img :src="artist.image_url" alt="Artist" class="artist-thumbnail" />
                                <div class="artist-name">{{ artist.name }}</div>
                            </div>
                            <div v-if="!userProfile.topArtists || userProfile.topArtists.length === 0" class="empty-state">
                                No top artists yet. Keep exploring music!
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
