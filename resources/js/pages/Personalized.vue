<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// Define props coming from the server
const props = defineProps({
    profile: Object,
    recommendations: Object,
    recommendationHistory: Array
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

// Form for rating recommendations
const rateForm = useForm({
    rating: null
});

// Rate a recommendation
const rateRecommendation = (id: number, rating: number) => {
    rateForm.rating = rating;

    rateForm.put(route('api.recommendations.rate', { id }), {
        preserveState: true,
        onSuccess: () => {
            // Update the rating in our local state
            const recommendation = recommendationHistory.value.items.find(item => item.id === id);
            if (recommendation) {
                recommendation.liked = rating;
            }

            // Refresh personalized recommendations after rating
            router.reload({ only: ['recommendations'] });
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
</script>

<template>
    <Head title="Personalized" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="personalized-container">
            <h1 class="page-title">Your Personalized Experience</h1>

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

<style scoped>
/* Basic layout styling */
.personalized-container {
    padding: 1.5rem;
    max-width: 1200px;
    margin: 0 auto;
}

.page-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 0.5rem;
}

.loading-indicator {
    padding: 2rem;
    text-align: center;
    color: #6b7280;
    background-color: #f9fafb;
    border-radius: 0.5rem;
}

.empty-state {
    color: #6b7280;
    font-style: italic;
    text-align: center;
    padding: 1rem;
}

/* Profile section styling */
.profile-section {
    margin-bottom: 2rem;
}

.profile-content {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.profile-card {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 1rem;
}

.profile-card h3 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.genres-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.genre-tag {
    background-color: #f3f4f6;
    border-radius: 9999px;
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
}

.songs-list, .artists-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.song-item, .artist-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.song-thumbnail, .artist-thumbnail {
    width: 3rem;
    height: 3rem;
    border-radius: 0.25rem;
    object-fit: cover;
}

.artist-thumbnail {
    border-radius: 50%;
}

/* Recommendations section styling */
.recommendations-section {
    margin-bottom: 2rem;
}

.recommendations-content {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.recommendation-category h3 {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.songs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1rem;
}

.song-card {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.2s;
}

.song-card:hover {
    transform: translateY(-5px);
}

.song-cover {
    width: 100%;
    aspect-ratio: 1 / 1;
    object-fit: cover;
}

.song-info {
    padding: 0.75rem;
}

.song-title {
    font-weight: 600;
    font-size: 0.875rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.song-artist {
    font-size: 0.75rem;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.play-button {
    width: 100%;
    padding: 0.5rem;
    background-color: #4f46e5;
    color: white;
    border: none;
    font-weight: 500;
    cursor: pointer;
}

.play-button:hover {
    background-color: #4338ca;
}

/* History section styling */
.history-section {
    margin-bottom: 2rem;
}

.history-content {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.history-items {
    display: flex;
    flex-direction: column;
}

.history-item {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.history-item:last-child {
    border-bottom: none;
}

.history-item-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.history-thumbnail {
    width: 4rem;
    height: 4rem;
    border-radius: 0.25rem;
    object-fit: cover;
}

.history-details {
    flex: 1;
}

.history-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.history-artist {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.history-based-on {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.based-on-text {
    font-size: 0.75rem;
    color: #6b7280;
}

.based-on-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
}

.based-on-pill {
    background-color: #f3f4f6;
    border-radius: 9999px;
    padding: 0.125rem 0.5rem;
    font-size: 0.75rem;
}

.based-on-pill.more {
    background-color: #e5e7eb;
    color: #6b7280;
}

.history-actions {
    display: flex;
    gap: 0.5rem;
}

.rating-button {
    padding: 0.5rem;
    border: 1px solid #e5e7eb;
    background-color: white;
    border-radius: 9999px;
    cursor: pointer;
    transition: all 0.2s;
}

.rating-button:hover {
    background-color: #f3f4f6;
}

.rating-button.active.like {
    color: #16a34a;
    border-color: #16a34a;
    background-color: #f0fdf4;
}

.rating-button.active.dislike {
    color: #dc2626;
    border-color: #dc2626;
    background-color: #fef2f2;
}

@media (max-width: 768px) {
    .profile-content {
        grid-template-columns: 1fr;
    }

    .songs-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    }

    .history-item-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .history-actions {
        align-self: flex-end;
    }
}
</style>
