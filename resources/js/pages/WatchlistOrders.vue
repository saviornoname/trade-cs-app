<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import FiltersModal from '../components/FiltersModal.vue';

interface WatchItem {
    id: number;
    title: string;
    active: boolean;
    min_float: number | null;
    max_float: number | null;
    phase: string | null;
    paint_seed: string | null;
}

const items = ref<WatchItem[]>([]);
const search = ref('');
const showItems = ref(true);
const currentPage = ref(1);
const perPage = 10;
const totalPages = ref(1);
const activeOnly = ref(true);
const comparisons = ref<any[]>([]);
const filterItemId = ref<number | null>(null);
const compareFilters = reactive({ float: '', seed: '', phase: '' });
const sortKey = ref<'target_max_price_usd' | 'profit_percent'>('target_max_price_usd');
const sortAsc = ref(false);
const comparePage = ref(1);
const comparePerPage = 20;
const compareTotalPages = computed(() => {
    let arr = comparisons.value;
    if (compareFilters.float) arr = arr.filter((c) => String(c.floatPartValue) === compareFilters.float);
    if (compareFilters.seed) arr = arr.filter((c) => String(c.paintSeed) === compareFilters.seed);
    if (compareFilters.phase) arr = arr.filter((c) => String(c.phase) === compareFilters.phase);
    return Math.max(1, Math.ceil(arr.length / comparePerPage));
});
const availableFloats = ref<string[]>([]);
const availableSeeds = ref<string[]>([]);
const availablePhases = ref<string[]>([]);
const file = ref<File | null>(null);
const isSubmitting = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const loadItems = async (append = false) => {
    const res = await axios.get(route('watchlist.items'), {
        params: {
            page: currentPage.value,
            per_page: perPage,
            search: search.value,
            active: activeOnly.value ? 1 : 0,
        },
    });
    if (append) {
        items.value = [...items.value, ...res.data.data];
    } else {
        items.value = res.data.data;
    }
    totalPages.value = res.data.last_page;
};

const loadMore = async () => {
    if (currentPage.value >= totalPages.value) return;
    currentPage.value += 1;
    await loadItems(true);
};

const toggleActive = async (item: WatchItem) => {
    await axios.patch(route('watchlist.toggle', { item: item.id }));
    item.active = !item.active;
};

const saveItem = async (item: WatchItem) => {
    await axios.patch(route('watchlist.update', { item: item.id }), {
        min_float: item.min_float,
        max_float: item.max_float,
        phase: item.phase,
        paint_seed: item.paint_seed,
    });
};

watch([search, activeOnly], () => {
    currentPage.value = 1;
    loadItems();
});

watch([() => compareFilters.float, () => compareFilters.seed, () => compareFilters.phase], () => {
    comparePage.value = 1;
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
    const f = new Set<string>();
    const s = new Set<string>();
    const p = new Set<string>();
    comparisons.value.forEach((c: any) => {
        if (c.floatPartValue) f.add(String(c.floatPartValue));
        if (c.paintSeed !== undefined && c.paintSeed !== null) s.add(String(c.paintSeed));
        if (c.phase) p.add(String(c.phase));
    });
    availableFloats.value = Array.from(f).sort();
    availableSeeds.value = Array.from(s).sort((a, b) => +a - +b);
    availablePhases.value = Array.from(p).sort();
    comparePage.value = 1;
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

const displayComparisons = computed(() => {
    let arr = comparisons.value.map((c) => {
        const minMarket = Array.isArray(c.market_min_prices_usd) && c.market_min_prices_usd.length ? Math.min(...c.market_min_prices_usd) : null;
        const profit =
            minMarket !== null && c.target_max_price_usd !== undefined && c.target_max_price_usd !== null
                ? ((c.target_max_price_usd - minMarket) / minMarket) * 100
                : null;
        return { ...c, minMarket, profit_percent: profit };
    });

    if (compareFilters.float) arr = arr.filter((c) => String(c.floatPartValue) === compareFilters.float);
    if (compareFilters.seed) arr = arr.filter((c) => String(c.paintSeed) === compareFilters.seed);
    if (compareFilters.phase) arr = arr.filter((c) => String(c.phase) === compareFilters.phase);

    arr.sort((a, b) => {
        const aVal = a[sortKey.value] ?? 0;
        const bVal = b[sortKey.value] ?? 0;
        return sortAsc.value ? aVal - bVal : bVal - aVal;
    });

    return arr.slice((comparePage.value - 1) * comparePerPage, comparePage.value * comparePerPage);
});
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
                <th class="border px-2 py-1">Min Float</th>
                <th class="border px-2 py-1">Max Float</th>
                <th class="border px-2 py-1">Seed</th>
                <th class="border px-2 py-1">Phase</th>
                <th class="border px-2 py-1">Save</th>
                <th class="border px-2 py-1">Filters</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="item in items" :key="item.id" class="border-t">
                <td class="border px-2 py-1">{{ item.title }}</td>
                <td class="border px-2 py-1 text-center">
                    <input type="checkbox" :checked="item.active" @change="toggleActive(item)" />
                </td>
                <td class="border px-2 py-1">
                    <input v-model.number="item.min_float" type="number" step="0.0001" class="w-24 rounded border px-1" />
                </td>
                <td class="border px-2 py-1">
                    <input v-model.number="item.max_float" type="number" step="0.0001" class="w-24 rounded border px-1" />
                </td>
                <td class="border px-2 py-1"><input v-model="item.paint_seed" class="w-20 rounded border px-1" /></td>
                <td class="border px-2 py-1"><input v-model="item.phase" class="w-20 rounded border px-1" /></td>
                <td class="border px-2 py-1 text-center"><button @click="saveItem(item)" class="rounded border px-2">💾</button></td>
                <td class="border px-2 py-1 text-center"><button @click="filterItemId = item.id" class="rounded border px-2">⚙</button></td>
            </tr>
            </tbody>
        </table>

        <div v-if="showItems" class="mb-6 flex items-center gap-2">
            <button
                @click="
                    currentPage = Math.max(1, currentPage - 1);
                    loadItems();
                "
                class="border px-2"
            >
                Prev
            </button>
            <span>Page {{ currentPage }} / {{ totalPages }}</span>
            <button v-if="currentPage < totalPages" @click="loadMore" class="border px-2">Load more</button>
        </div>

        <button @click="loadComparisons" class="mb-4 rounded border px-3 py-1">Refresh Comparison</button>

        <div class="mb-2 flex flex-wrap gap-2">
            <select v-model="compareFilters.float" class="rounded border px-2 py-1">
                <option value="">Float</option>
                <option v-for="f in availableFloats" :key="f" :value="f">{{ f }}</option>
            </select>
            <select v-model="compareFilters.seed" class="rounded border px-2 py-1">
                <option value="">Seed</option>
                <option v-for="s in availableSeeds" :key="s" :value="s">{{ s }}</option>
            </select>
            <select v-model="compareFilters.phase" class="rounded border px-2 py-1">
                <option value="">Phase</option>
                <option v-for="p in availablePhases" :key="p" :value="p">{{ p }}</option>
            </select>
            <select v-model="sortKey" class="rounded border px-2 py-1">
                <option value="target_max_price_usd">Target Max $</option>
                <option value="profit_percent">Profit %</option>
            </select>
            <button @click="sortAsc = !sortAsc" class="rounded border px-2">Sort {{ sortAsc ? '↑' : '↓' }}</button>
        </div>

        <table class="w-full border text-sm">
            <thead class="bg-gray-300 text-gray-900 dark:bg-gray-700 dark:text-gray-100">
            <tr>
                <th class="border px-2 py-1">Title</th>
                <th class="border px-2 py-1">Float</th>
                <th class="border px-2 py-1">Seed</th>
                <th class="border px-2 py-1">Phase</th>
                <th class="border px-2 py-1">Market Min $ (3)</th>
                <th class="border px-2 py-1">Target Max $</th>
                <th class="border px-2 py-1">Profit %</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="c in displayComparisons" :key="c.title + c.floatPartValue + c.paintSeed + c.phase" class="border-t">
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
                <td class="border px-2 py-1 text-right">{{ c.profit_percent !== null ? c.profit_percent.toFixed(2) + '%' : '-' }}</td>
            </tr>
            </tbody>
        </table>
        <div class="mt-2 flex items-center gap-2">
            <button @click="comparePage = Math.max(1, comparePage - 1)" class="border px-2">Prev</button>
            <span>Page {{ comparePage }} / {{ compareTotalPages }}</span>
            <button @click="comparePage = Math.min(compareTotalPages, comparePage + 1)" class="border px-2">Next</button>
        </div>
        <FiltersModal :show="filterItemId !== null" :item-id="filterItemId" @close="filterItemId = null" />
    </div>
</template>
