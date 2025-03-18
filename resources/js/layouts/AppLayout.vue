<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    isAdmin?: boolean;
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    isAdmin: false
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" :isAdmin="isAdmin">
        <slot />
    </AppLayout>
</template>
