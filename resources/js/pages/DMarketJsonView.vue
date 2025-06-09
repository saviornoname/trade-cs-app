<script setup lang="ts">
import { onMounted, ref } from 'vue';
import axios from 'axios';

const allUserOrders = ref([]);    // всі ордери
const userOrders = ref([]);       // поточний ордер (масив з 1 елементом для шаблону)
const marketOrders = ref({});
const loading = ref(true);
const currentIndex = ref(0);

async function loadMarketOrdersForOrder(order) {
    try {
        const resMarket = await axios.get(route('dmarket.market'), {
            params: {
                title: order.Title,
                gameId: 'a8db',
            },
        });
        marketOrders.value = {}; // очищаємо старі
        marketOrders.value[order.Title] = resMarket.data?.orders ?? [];

    } catch (e) {
        console.error('❌ Error loading market orders', e);
        marketOrders.value = {};
    }
}

async function fetchData() {
    loading.value = true;
    try {
        const resUser = await axios.get(route('dmarket.targets'));
        allUserOrders.value = resUser.data?.Items ?? [];

        if (allUserOrders.value.length > 0) {
            currentIndex.value = 0;
            userOrders.value = [allUserOrders.value[currentIndex.value]];
            await loadMarketOrdersForOrder(userOrders.value[0]);
        }
    } catch (e) {
        console.error('❌ Error loading data', e);
    } finally {
        loading.value = false;
    }
}

function nextOrder() {
    if (allUserOrders.value.length === 0) return;

    currentIndex.value++;
    if (currentIndex.value >= allUserOrders.value.length) {
        currentIndex.value = 0; // зациклити, або можна заблокувати кнопку
    }

    userOrders.value = [allUserOrders.value[currentIndex.value]];
    loadMarketOrdersForOrder(userOrders.value[0]);
}

onMounted(fetchData);
</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">📦 JSON Viewer: User Orders & Market Orders</h1>

        <div v-if="loading" class="mb-4 text-blue-500">Завантаження даних...</div>

        <div class="mb-4">
            <button
                @click="nextOrder"
                class="bg-yellow-400 hover:bg-yellow-500 text-white font-bold py-2 px-4 rounded"
                :disabled="loading || allUserOrders.length === 0"
            >
                Наступний ордер
            </button>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <h2 class="text-xl font-semibold mb-2">🎯 User Order</h2>
                <pre class="bg-gray-100 p-3 rounded overflow-auto max-h-[600px] text-xs text-yellow-500">{{ JSON.stringify(userOrders[0], null, 2) }}</pre>
            </div>
            <div>
                <h2 class="text-xl font-semibold mb-2">🌐 Market Orders</h2>
                <pre class="bg-gray-100 p-3 rounded overflow-auto max-h-[600px] text-xs text-yellow-500">
          {{ JSON.stringify(marketOrders[userOrders[0]?.Title] ?? [], null, 2) }}
        </pre>
            </div>
        </div>
    </div>
</template>
