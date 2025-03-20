<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

// Define props from the server
const props = defineProps({
    recommendations: {
        type: Object,
        default: () => ({
            forYou: [],
            basedOnGenre: [],
            newReleases: []
        })
    },
    recommendationHistory: {
        type: Array,
        default: () => []
    },
    profile: {
        type: Object,
        default: () => ({
            favoriteGenres: [],
            recentPlays: [],
            topArtists: []
        })
    },
    success: {
        type: Boolean,
        default: true
    },
    error: {
        type: String,
        default: null
    }
});

// Breadcrumbs for the layout
const breadcrumbs = ref([
    {
        title: "Home",
        href: '/'
    },
    {
        title: "Personalized",
        href: '/personalized'
    }
]);

// Local reactive state
const personalizedRecommendations = ref({
    forYou: props.recommendations.forYou || [],
    basedOnGenre: props.recommendations.basedOnGenre || [],
    newReleases: props.recommendations.newReleases || [],
    loading: false
});

const history = ref({
    items: props.recommendationHistory || [],
    loading: false
});

const userProfile = ref({
    favoriteGenres: props.profile.favoriteGenres || [],
    recentPlays: props.profile.recentPlays || [],
    topArtists: props.profile.topArtists || [],
    loading: false
});

const errorMessage = ref(props.error);
const isLoading = ref(false);

// Create form for rating
const rateForm = useForm({
    rating: null
});

// Watch for changes in props
watch(() => props.recommendations, (newValue) => {
    personalizedRecommendations.value = {
        forYou: newValue.forYou || [],
        basedOnGenre: newValue.basedOnGenre || [],
        newReleases: newValue.newReleases || [],
        loading: false
    };
}, { deep: true });

watch(() => props.recommendationHistory, (newValue) => {
    history.value = {
        items: newValue || [],
        loading: false
    };
}, { deep: true });

watch(() => props.profile, (newValue) => {
    userProfile.value = {
        favoriteGenres: newValue.favoriteGenres || [],
        recentPlays: newValue.recentPlays || [],
        topArtists: newValue.topArtists || [],
        loading: false
    };
}, { deep: true });

watch(() => props.error, (newValue) => {
    errorMessage.value = newValue;
});

// Method to rate a recommendation
const rateRecommendation = (id, rating) => {
    rateForm.rating = rating;
    isLoading.value = true;

    router.post(`/api/personalized/rate/${id}`, { rating }, {
        preserveScroll: true,
        onSuccess: () => {
            // Update local state to reflect the new rating
            const recommendation = history.value.items.find(item => item.id === id);
            if (recommendation) {
                recommendation.liked = rating;
            }

            // Refresh the recommendations to get updated personalized content
            router.reload({ only: ['recommendations'] });
            isLoading.value = false;
        },
        onError: (errors) => {
            console.error('Error rating recommendation:', errors);
            isLoading.value = false;
        }
    });
};


// Computed property for history display label
const getTimeAgo = (timestamp) => {
    const now = new Date();
    const recordedTime = new Date(timestamp);
    const diffInMinutes = Math.floor((now - recordedTime) / (1000 * 60));

    if (diffInMinutes < 1) return 'Just now';
    if (diffInMinutes < 60) return `${diffInMinutes} minute${diffInMinutes !== 1 ? 's' : ''} ago`;

    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) return `${diffInHours} hour${diffInHours !== 1 ? 's' : ''} ago`;

    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 30) return `${diffInDays} day${diffInDays !== 1 ? 's' : ''} ago`;

    const diffInMonths = Math.floor(diffInDays / 30);
    return `${diffInMonths} month${diffInMonths !== 1 ? 's' : ''} ago`;
};
</script>

<template>
    <Head title="Personalized Recommendations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="personalized-container">
            <h1 class="page-title">Your Personalized Music Experience</h1>

            <!-- Error Message Display -->
            <div v-if="errorMessage" class="error-alert">
                <p>{{ errorMessage }}</p>
            </div>

            <!-- Loading Indicator -->
            <div v-if="isLoading" class="loading-indicator">
                <div class="spinner"></div>
                <p>Processing your request...</p>
            </div>

            <!-- Personalized Recommendations Section -->
            <section class="recommendations-section">
                <h2 class="section-title">Personalized Recommendations</h2>

                <!-- For You Section -->
                <div class="recommendation-category">
                    <h3>Made For You</h3>
                    <div class="songs-grid">
                        <div v-for="song in personalizedRecommendations.forYou" :key="song.id" class="song-card">
                            <div class="song-info">
                                <h4 class="song-title">{{ song.title }}</h4>
                                <p class="song-artist">{{ song.artist }}</p>
                            </div>
                        </div>

                        <div v-if="personalizedRecommendations.forYou.length === 0" class="empty-state">
                            <p>Keep listening to get personalized recommendations!</p>
                        </div>
                    </div>
                </div>

                <!-- Based on Genres Section -->
                <div class="recommendation-category">
                    <h3>Based on Your Favorite Genres</h3>
                    <div class="songs-grid">
                        <div v-for="song in personalizedRecommendations.basedOnGenre" :key="song.id" class="song-card">
                            <div class="song-info">
                                <h4 class="song-title">{{ song.title }}</h4>
                                <p class="song-artist">{{ song.artist }}</p>
                            </div>
                        </div>

                        <div v-if="personalizedRecommendations.basedOnGenre.length === 0" class="empty-state">
                            <p>Explore more genres to discover new music!</p>
                        </div>
                    </div>
                </div>

                <!-- New Releases Section -->
                <div class="recommendation-category">
                    <h3>New Releases For You</h3>
                    <div class="songs-grid">
                        <div v-for="song in personalizedRecommendations.newReleases" :key="song.id" class="song-card">
                            <div class="song-info">
                                <h4 class="song-title">{{ song.title }}</h4>
                                <p class="song-artist">{{ song.artist }}</p>
                            </div>
                        </div>

                        <div v-if="personalizedRecommendations.newReleases.length === 0" class="empty-state">
                            <p>Check back soon for new releases that match your taste!</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Recommendation History Section -->
            <section class="history-section">
                <h2 class="section-title">Your Recommendation History</h2>

                <div v-if="history.items.length > 0" class="history-container">
                    <div v-for="item in history.items" :key="item.id" class="history-card">
                        <div class="history-main">

                            <div class="history-content">
                                <div class="recommendation-info">
                                    <h4 class="recommendation-title">{{ item.recommendedSong.title }}</h4>
                                    <p class="recommendation-artist">{{ item.recommendedSong.artist }}</p>
                                    <span class="recommendation-time">{{ getTimeAgo(item.timestamp) }}</span>
                                </div>

                                <div class="based-on">
                                    <p class="based-on-label">Based on:</p>
                                    <div class="source-songs">
                    <span v-for="(source, index) in item.basedOn.slice(0, 3)" :key="index" class="source-pill">
                      {{ source.title }}
                    </span>
                                        <span v-if="item.basedOn.length > 3" class="source-pill more">
                      +{{ item.basedOn.length - 3 }} more
                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="history-actions">
                            <div class="rating-buttons">
                                <button
                                    @click="rateRecommendation(item.id, 1)"
                                    :class="['like-button', { active: item.liked === 1 }]"
                                    :disabled="isLoading"
                                >
                                    👍
                                </button>
                                <button
                                    @click="rateRecommendation(item.id, -1)"
                                    :class="['dislike-button', { active: item.liked === -1 }]"
                                    :disabled="isLoading"
                                >
                                    👎
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="empty-history">
                    <p>You haven't received any recommendations yet. Start exploring music to get personalized recommendations!</p>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.personalized-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 2rem;
    color: #333;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: #444;
}

/* Error Alert */
.error-alert {
    background-color: #ffebee;
    color: #c62828;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
    border-left: 4px solid #c62828;
}

/* Loading Indicator */
.loading-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-left-color: #1976d2;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Recommendations Section */
.recommendations-section {
    margin-bottom: 3rem;
}

.recommendation-category {
    margin-bottom: 2.5rem;
}

.recommendation-category h3 {
    font-size: 1.25rem;
    margin-bottom: 1rem;
    color: #555;
}

.songs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.5rem;
}

.song-card {
    background-color: #fff;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.song-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}


.song-card:hover {
    transform: scale(1.05);
}


.song-card:hover{
    opacity: 1;
}

.song-info {
    padding: 1rem;
}

.song-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.song-artist {
    font-size: 0.875rem;
    color: #777;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.empty-state {
    grid-column: 1 / -1;
    padding: 2rem;
    text-align: center;
    background-color: #f9f9f9;
    border-radius: 0.5rem;
    border: 1px dashed #ddd;
    color: #777;
}

.history-section {
    margin-bottom: 3rem;
}

.history-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.history-card {
    display: flex;
    background-color: #fff;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    padding: 1rem;
    transition: box-shadow 0.2s ease;
}

.history-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.history-main {
    display: flex;
    flex: 1;
}

.history-image {
    width: 80px;
    height: 80px;
    flex-shrink: 0;
    margin-right: 1rem;
}

.recommendation-cover {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 0.25rem;
}

.history-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.recommendation-info {
    margin-bottom: 0.5rem;
}

.recommendation-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.recommendation-artist {
    font-size: 0.875rem;
    color: #666;
    margin-bottom: 0.25rem;
}

.recommendation-time {
    font-size: 0.75rem;
    color: #999;
}

.based-on {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

.based-on-label {
    font-size: 0.75rem;
    color: #888;
    white-space: nowrap;
}

.source-songs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
}

.source-pill {
    font-size: 0.75rem;
    background-color: #f0f0f0;
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
    color: #555;
}

.source-pill.more {
    background-color: #e0e0e0;
    color: #666;
}

.history-actions {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    margin-left: 1rem;
}

.play-recommendation {
    background-color: #1976d2;
    color: white;
    border: none;
    border-radius: 2rem;
    padding: 0.4rem 1.2rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.play-recommendation:hover {
    background-color: #1565c0;
}

.rating-buttons {
    display: flex;
    gap: 0.75rem;
}

.like-button,
.dislike-button {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.like-button:hover,
.dislike-button:hover {
    opacity: 0.9;
    transform: scale(1.1);
}


.empty-history {
    padding: 3rem 2rem;
    text-align: center;
    background-color: #f9f9f9;
    border-radius: 0.5rem;
    border: 1px dashed #ddd;
    color: #777;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .songs-grid {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    }

    .history-card {
        flex-direction: column;
    }

    .history-main {
        margin-bottom: 1rem;
    }

    .history-actions {
        flex-direction: row;
        margin-left: 0;
    }

}

@media (max-width: 480px) {
    .songs-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    }

    .based-on {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
