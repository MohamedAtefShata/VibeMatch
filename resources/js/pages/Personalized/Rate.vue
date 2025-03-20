<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

// Define props for data from the server
const props = defineProps({
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

// Emit the rating result to the parent component
const emit = defineEmits(['rated']);

// When the component mounts, emit the rating result
if (props.success) {
    emit('rated', { success: true, message: props.message });
} else {
    emit('rated', { success: false, error: props.error });
}
</script>

<template>
    <!-- This is a silent component that just passes data to parent -->
    <Head title="Rate Recommendation" />
</template>
