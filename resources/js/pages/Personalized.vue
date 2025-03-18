<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Personalized",
        href: '/personalized',
    },
];

// User's listening history and preferences
const userProfile = ref({
    favoriteGenres: [],
    recentPlays: [],
    topArtists: [],
    loading: true
});

// Personalized recommendations
const recommendations = ref({
    forYou: [],
    basedOnGenre: [],
    newReleases: [],
    loading: true
});

// Fetch user profile and recommendations on component mount
onMounted(async () => {
    try {
        // Fetch user profile data
        const profileResponse = await axios.get('/api/user/profile');
        userProfile.value = {
            ...profileResponse.data,
            loading: false
        };

        // Fetch personalized recommendations
        const recommendationsResponse = await axios.get('/api/songs/personalized');
        recommendations.value = {
            ...recommendationsResponse.data,
            loading: false
        };
    } catch (error) {
        console.error('Error fetching personalized data:', error);
        userProfile.value.loading = false;
        recommendations.value.loading = false;
    }
});
</script>

<template>
    <Head title="Personalized" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="personalized-container">
            <h1 class="page-title">Your Personalized Experience</h1>

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
                            <span v-if="userProfile.favoriteGenres.length === 0" class="empty-state">
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
                            <div v-if="userProfile.recentPlays.length === 0" class="empty-state">
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
                            <div v-if="userProfile.topArtists.length === 0" class="empty-state">
                                No top artists yet. Keep exploring music!
                            </div>
                        </div>
                    </div>
                </div>
            </section>

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
                            <div v-if="recommendations.forYou.length === 0" class="empty-state">
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
                            <div v-if="recommendations.basedOnGenre.length === 0" class="empty-state">
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
                            <div v-if="recommendations.newReleases.length === 0" class="empty-state">
                                No new releases matching your taste yet. Check back soon!
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

@media (max-width: 768px) {
    .profile-content {
        grid-template-columns: 1fr;
    }

    .songs-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    }
}
</style>
