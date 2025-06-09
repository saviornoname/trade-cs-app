<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
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
const comparisons = ref<any[]>([]);
const file = ref<File | null>(null);
const isSubmitting = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const loadItems = async () => {
    const res = await axios.get(route('watchlist.items'));
    items.value = res.data;
};

const toggleActive = async (item: WatchItem) => {
    await axios.patch(route('watchlist.toggle', { item: item.id }));
    item.active = !item.active;
};

const filteredItems = computed(() => {
    const query = search.value.toLowerCase();
    const all = items.value.filter((i) => i.title.toLowerCase().includes(query));
    const start = (currentPage.value - 1) * perPage;
    return all.slice(start, start + perPage);
});

const totalPages = computed(() => {
    const query = search.value.toLowerCase();
    const count = items.value.filter((i) => i.title.toLowerCase().includes(query)).length;
    return Math.max(1, Math.ceil(count / perPage));
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
    const res = await axios.get(route('dmarket.compare'));
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
    if (!file.value) return;

    isSubmitting.value = true;
    successMessage.value = '';
    errorMessage.value = '';

    const formData = new FormData();
    formData.append('csv_file', file.value);

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
            <tr v-for="item in filteredItems" :key="item.id" class="border-t">
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
                <th class="border px-2 py-1">Phase</th>
                <th class="border px-2 py-1">Float</th>
                <th class="border px-2 py-1">DMarket $</th>
                <th class="border px-2 py-1">Buff ¥</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="c in comparisons" :key="c.title + c.float + c.phase" class="border-t">
                <td class="border px-2 py-1">{{ c.title }}</td>
                <td class="border px-2 py-1">{{ c.phase || '/' }}</td>
                <td class="border px-2 py-1">{{ c.float || '/' }}</td>
                <td class="border px-2 py-1 text-right">{{ c.dmarket_price_usd ?? '-' }}</td>
                <td class="border px-2 py-1 text-right">{{ c.best_buff_price_cny ?? '-' }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>
