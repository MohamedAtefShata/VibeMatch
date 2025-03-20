<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

// Define props for data from the server
const props = defineProps({
    recommendations: {
        type: Array,
        default: () => []
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

// Emit the recommendations data to the parent component
const emit = defineEmits(['history-loaded']);

// When the component mounts, emit the history data
if (props.success) {
    emit('history-loaded', { success: true, recommendations: props.recommendations });
} else {
    emit('history-loaded', { success: false, error: props.error });
}
</script>

<template>
    <!-- This is a silent component that just passes data to parent -->
    <Head title="Recommendation History" />
</template>
