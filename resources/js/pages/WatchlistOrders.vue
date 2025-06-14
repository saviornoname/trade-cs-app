<script setup lang="ts">
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import { route } from 'ziggy-js';

interface WatchItem {
    id: number;
    title: string;
    active: boolean;
}

const items = ref<WatchItem[]>([]);
const search = ref('');
const showItems = ref(true);
const currentPage = ref(1);
const perPage = 10;
const totalPages = ref(1);
const activeOnly = ref(true);
const comparisons = ref<any[]>([]);
const file = ref<File | null>(null);
const isSubmitting = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const loadItems = async () => {
    const res = await axios.get(route('watchlist.items'), {
        params: {
            page: currentPage.value,
            per_page: perPage,
            search: search.value,
            active: activeOnly.value ? 1 : 0,
        },
    });
    items.value = res.data.data;
    totalPages.value = res.data.last_page;
};

const toggleActive = async (item: WatchItem) => {
    await axios.patch(route('watchlist.toggle', { item: item.id }));
    item.active = !item.active;
};



watch([currentPage, search, activeOnly], ([, newSearch, newActive], [, oldSearch, oldActive]) => {
    if (newSearch !== oldSearch || newActive !== oldActive) {
        currentPage.value = 1;
    }
    loadItems();
});

const deactivateAll = async () => {
    await axios.post(route('watchlist.deactivateAll'));
    await loadItems();
};

const activateAll = async () => {
    await axios.post(route('watchlist.activateAll'));
    await loadItems();
};

const loadComparisons = async () => {
    const res = await axios.get(route('dmarket.targets-market'));
    comparisons.value = res.data;
};

onMounted(async () => {
    await loadItems();
    await loadComparisons();
});

const handleFile = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        file.value = target.files[0];
    }
};

const submit = async () => {
    isSubmitting.value = true;
    errorMessage.value = '';

    const formData = new FormData();
    if (file.value) {
        formData.append('csv_file', file.value);
    }

    try {
        await axios.post(route('watchlist.import'), formData);
        successMessage.value = 'Імпорт завершено успішно.';
        file.value = null;
        await loadItems();
    } catch (e: any) {
        errorMessage.value = e.response?.data?.message || 'Сталася помилка під час імпорту.';
        console.error(e.response?.data || e);
    } finally {
        isSubmitting.value = false;
    }
};
</script>


<template>
    <div class="p-4">
        <h1 class="mb-4 text-xl font-bold">Watchlist Items</h1>

        <div class="mb-4 flex items-center gap-2">
            <input v-model="search" placeholder="Search..." class="rounded border px-2 py-1" />
            <label class="flex items-center gap-1 text-sm"> <input type="checkbox" v-model="activeOnly" /> Active only </label>
            <button @click="showItems = !showItems" class="rounded border px-3 py-1">
                {{ showItems ? 'Hide list' : 'Show list' }}
            </button>
            <button @click="deactivateAll" class="rounded border px-3 py-1">Deactivate all</button>
            <button @click="activateAll" class="rounded border px-3 py-1">Activate all</button>
        </div>

        <form @submit.prevent="submit" class="mb-6 flex items-center gap-2" enctype="multipart/form-data">
            <label class="cursor-pointer rounded border bg-gray-200 px-3 py-1 text-gray-800">
                <span>Вибрати CSV</span>
                <input type="file" accept=".csv" class="hidden" @change="handleFile" />
            </label>
            <button type="submit" :disabled="isSubmitting" class="rounded border bg-blue-600 px-3 py-1 text-white">
                {{ isSubmitting ? 'Імпорт...' : 'Імпортувати' }}
            </button>
            <span class="text-sm text-green-600" v-if="successMessage">{{ successMessage }}</span>
            <span class="text-sm text-red-600" v-if="errorMessage">{{ errorMessage }}</span>
        </form>
        <table v-if="showItems" class="mb-6 border text-sm">
            <thead class="bg-gray-300 text-gray-900 dark:bg-gray-700 dark:text-gray-100">
                <tr>
                    <th class="border px-2 py-1">Title</th>
                    <th class="border px-2 py-1">Active</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in items" :key="item.id" class="border-t">
                    <td class="border px-2 py-1">{{ item.title }}</td>
                    <td class="border px-2 py-1 text-center">
                        <input type="checkbox" :checked="item.active" @change="toggleActive(item)" />
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-if="showItems" class="mb-6 flex items-center gap-2">
            <button @click="currentPage = Math.max(1, currentPage - 1)" class="border px-2">Prev</button>
            <span>Page {{ currentPage }} / {{ totalPages }}</span>
            <button @click="currentPage = Math.min(totalPages, currentPage + 1)" class="border px-2">Next</button>
        </div>

        <button @click="loadComparisons" class="mb-4 rounded border px-3 py-1">Refresh Comparison</button>

        <table class="w-full border text-sm">
            <thead class="bg-gray-300 text-gray-900 dark:bg-gray-700 dark:text-gray-100">
            <tr>
                <th class="border px-2 py-1">Title</th>
                <th class="border px-2 py-1">Float</th>
                <th class="border px-2 py-1">Seed</th>
                <th class="border px-2 py-1">Phase</th>
                <th class="border px-2 py-1">Market Min $ (3)</th>
                <th class="border px-2 py-1">Target Max $</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="c in comparisons" :key="c.title + c.floatPartValue + c.paintSeed + c.phase" class="border-t">
                <td class="border px-2 py-1">{{ c.title }}</td>
                <td class="border px-2 py-1 text-right">{{ c.floatPartValue }}</td>
                <td class="border px-2 py-1 text-right">{{ c.paintSeed }}</td>
                <td class="border px-2 py-1 text-right">{{ c.phase }}</td>
                <td class="border px-2 py-1 text-right">
                    <span v-if="Array.isArray(c.market_min_prices_usd) && c.market_min_prices_usd.length">
                        {{ c.market_min_prices_usd.join(', ') }}
                    </span>
                    <span v-else>-</span>
                </td>
                <td class="border px-2 py-1 text-right">{{ c.target_max_price_usd ?? '-' }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>
