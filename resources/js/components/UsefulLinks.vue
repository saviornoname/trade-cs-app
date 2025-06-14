<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';

interface LinkItem {
    label: string;
    href: string;
}

const links: LinkItem[] = [
    { label: 'Watchlist', href: route('watchlist.view') },
    { label: 'Імпортувати Watchlist', href: route('watchlist.import.view') },
    { label: 'API Credentials', href: route('api-credentials.edit') },
    { label: 'Api test (Dmarket)', href: route('dmarket.index') },
    { label: 'Api test (Dmarket, my orders)', href: route('dmarket.json') },
];
</script>

<template>
    <div>
        <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">Корисні посилання:</p>
        <ul class="space-y-2 text-sm">
            <li v-for="link in links" :key="link.href">
                <TextLink :href="link.href">➤ {{ link.label }}</TextLink>
            </li>
        </ul>
    </div>
</template>
