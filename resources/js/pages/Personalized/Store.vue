<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

// Define props for data from the server
const props = defineProps({
    recommendation: {
        type: Object,
        default: null
    },
    success: {
        type: Boolean,
        default: true
    },
    message: {
        type: String,
        default: null
    },
    error: {
        type: String,
        default: null
    }
});

// Emit the stored recommendation to the parent component
const emit = defineEmits(['stored']);

// When the component mounts, emit the stored recommendation if successful
if (props.success && props.recommendation) {
    emit('stored', { success: true, recommendation: props.recommendation, message: props.message });
} else {
    emit('stored', { success: false, error: props.error });
}
</script>

<template>
    <!-- This is a silent component that just passes data to parent -->
    <Head title="Store Recommendation" />
</template>
