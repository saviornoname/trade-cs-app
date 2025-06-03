<script setup lang="ts">
import axios from 'axios';
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';

const REFRESH_INTERVAL = 150000;
const TIMER_START = 60;
// const PRICE_DIVISOR = 100;

const autoRefresh = ref(true);
const timeLeft = ref(60);
const loading = ref(false);
const theme = ref<'light' | 'dark'>('light');
let intervalId: ReturnType<typeof setInterval> | null = null;

const userTargets = ref<any>(null);
const marketDataByTarget = reactive<Record<string, any[]>>({});
const filters = reactive({ phase: '', floatPartValue: '', paintSeed: '', title: '' });
const availablePhases = ref<string[]>([]);
const availableFloatValues = ref<string[]>([]);
const availablePaintSeeds = ref<string[]>([]);

const sortKey = ref('price');
sortKey.value = 'price';
const sortAsc = ref(false);

const gameIdInput = ref('a8db');
const orderLimit = ref(100);

const getAttr = (target: any, name: string) => {
    const attr = target.Attributes?.find((a: any) => a.Name === name);
    return attr?.Value || null;
};

function initTheme() {
    const saved = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = saved === 'dark' || (!saved && prefersDark);
    theme.value = isDark ? 'dark' : 'light';
    document.documentElement.classList.toggle('dark', isDark);
}

const toggleTheme = () => {
    theme.value = theme.value === 'light' ? 'dark' : 'light';
    document.documentElement.classList.toggle('dark', theme.value === 'dark');
    localStorage.setItem('theme', theme.value);
};

const fetchUserTargets = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('dmarket.targets'));
        userTargets.value = res.data;
    } catch (e) {
        console.error('Помилка при отриманні таргетів', e);
    } finally {
        loading.value = false;
    }
};

const fetchMarketForTarget = async (title: string) => {
    try {
        const res = await axios.get(route('dmarket.market'), {
            params: { title, gameId: gameIdInput.value },
        });
        marketDataByTarget[title] = res.data?.orders ?? [];
    } catch (e) {
        console.error(`Помилка при отриманні ринку для ${title}`, e);
        marketDataByTarget[title] = [];
    }
};

const fetchAllMarkets = async () => {
    if (!userTargets.value?.Items) return;
    loading.value = true;
    try {
        await Promise.all(userTargets.value.Items.map((target: any) => fetchMarketForTarget(target.Title)));
        extractFilterOptions();
    } finally {
        loading.value = false;
    }
};

const extractFilterOptions = () => {
    const phases = new Set<string>();
    const floats = new Set<string>();
    const seeds = new Set<string>();
    Object.values(marketDataByTarget).forEach(orders => {
        orders.forEach(order => {
            const attr = order.attributes || {};
            if (attr.phase) phases.add(attr.phase);
            if (attr.floatPartValue) floats.add(attr.floatPartValue);
            if (attr.paintSeed !== undefined) seeds.add(String(attr.paintSeed));
        });
    });
    availablePhases.value = Array.from(phases).sort();
    availableFloatValues.value = Array.from(floats).sort();
    availablePaintSeeds.value = Array.from(seeds).sort((a, b) => +a - +b);
};

const toggleSort = (key: string) => {
    if (sortKey.value === key) {
        sortAsc.value = !sortAsc.value;
    } else {
        sortKey.value = key;
        sortAsc.value = true;
    }
};

const targetsWithOrders = computed(() => {
    if (!userTargets.value?.Items) return [];

    return userTargets.value.Items.map((target: any) => {
        const orders = marketDataByTarget[target.Title] ?? [];
        const filteredOrders = orders.filter(order => {
            const attr = order.attributes || {};
            return (
                (!filters.title || order.title.toLowerCase().includes(filters.title.toLowerCase())) &&
                (!filters.floatPartValue || attr.floatPartValue === filters.floatPartValue) &&
                (!filters.phase || attr.phase === filters.phase) &&
                (!filters.paintSeed || String(attr.paintSeed) === filters.paintSeed)
            );
        });

        filteredOrders.sort((a, b) => {
            const aVal = a[sortKey.value] ?? a.attributes?.[sortKey.value];
            const bVal = b[sortKey.value] ?? b.attributes?.[sortKey.value];
            const parse = (val: any) => typeof val === 'string' ? parseFloat(val) || val : val;
            const aParsed = parse(aVal);
            const bParsed = parse(bVal);
            if (typeof aParsed === 'string' && typeof bParsed === 'string') {
                return sortAsc.value ? aParsed.localeCompare(bParsed) : bParsed.localeCompare(aParsed);
            }
            return sortAsc.value ? aParsed - bParsed : bParsed - aParsed;
        });

        const userPrice = Math.round(target.Price.Amount * 100);
        const limitedOrders = (() => {
            const result: any[] = [];
            for (const order of filteredOrders) {
                result.push(order);
                if (parseInt(order.price) === userPrice || result.length >= orderLimit.value) {
                    break;
                }
            }
            return result;
        })();

        return {
            target,
            userPriceFormatted: (target.Price.Amount).toFixed(2),
            orders: limitedOrders,
        };
    });
});

let countdownIntervalId: ReturnType<typeof setInterval> | null = null;

onMounted(async () => {
    initTheme();
    await fetchUserTargets();
    await fetchAllMarkets();

    // Зворотній відлік щосекунди
    countdownIntervalId = setInterval(() => {
        if (autoRefresh.value && timeLeft.value > 0) {
            timeLeft.value--;
        }
    }, 1000);

    // Автооновлення раз на 150 сек
    intervalId = setInterval(async () => {
        if (autoRefresh.value) {
            await fetchUserTargets();
            await fetchAllMarkets();
            timeLeft.value = TIMER_START; // перезапуск таймера
        }
    }, REFRESH_INTERVAL);
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
    if (countdownIntervalId) clearInterval(countdownIntervalId);
});

</script>

<template>
    <div class="p-4 text-sm">
        <div class="flex flex-wrap gap-2 mb-4">
            <input v-model="filters.title" placeholder="Назва" class="border px-2 py-1 rounded" />
            <select v-model="filters.phase" class="border px-2 py-1 rounded">
                <option value="">Фаза</option>
                <option v-for="phase in availablePhases" :key="phase" :value="phase">{{ phase }}</option>
            </select>
            <select v-model="filters.floatPartValue" class="border px-2 py-1 rounded">
                <option value="">Float</option>
                <option v-for="float in availableFloatValues" :key="float" :value="float">{{ float }}</option>
            </select>
            <select v-model="filters.paintSeed" class="border px-2 py-1 rounded">
                <option value="">Seed</option>
                <option v-for="seed in availablePaintSeeds" :key="seed" :value="seed">{{ seed }}</option>
            </select>
            <button @click="toggleTheme" class="ml-auto border px-2 py-1 rounded">
                Змінити тему
            </button>
        </div>
        <div class="mb-2 text-sm text-gray-700 dark:text-gray-300">
            ⏳ Оновлення через: {{ timeLeft }} сек
        </div>
        <div v-if="loading" class="text-center py-4">Завантаження...</div>

        <div v-for="entry in targetsWithOrders" :key="entry.target.Title" class="mb-6 border rounded p-2 shadow">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h2 class="text-lg font-semibold">{{ entry.target.Title }}</h2>
                    <div class="text-xs text-gray-600 dark:text-gray-400">
                        {{ getAttr(entry.target, 'phase') || '/' }} /
                        {{ getAttr(entry.target, 'floatPartValue') || '/' }} /
                        {{ getAttr(entry.target, 'paintSeed') || '/' }}
                    </div>
                </div>
                <div class="text-right text-sm">
                    🎯 Ваш ордер: <span class="font-bold">{{ entry.userPriceFormatted }} $</span>
                </div>
            </div>

            <table class="w-full text-sm border">
                <thead>
                <tr class="bg-gray-200 dark:bg-gray-700">
                    <th class="px-2 py-1 text-left cursor-pointer" @click="toggleSort('price')">Ціна (центи)</th>
                    <th class="px-2 py-1 text-left">Float</th>
                    <th class="px-2 py-1 text-left">Фаза</th>
                    <th class="px-2 py-1 text-left">Seed</th>
                </tr>
                </thead>
                <tbody>
                <tr
                    v-for="order in entry.orders"
                    :key="order.id"
                    class="border-b"
                    :class="{
              'bg-green-100 dark:bg-green-900': parseInt(order.price) === Math.round(entry.target.Price.Amount * 100)
            }"
                >
                    <td class="px-2 py-1 border">{{ (parseInt(order.price) / 100).toFixed(2) }}</td>
                    <td class="px-2 py-1 border">{{ order.attributes?.floatPartValue }}</td>
                    <td class="px-2 py-1 border">{{ order.attributes?.phase }}</td>
                    <td class="px-2 py-1 border">{{ order.attributes?.paintSeed }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
