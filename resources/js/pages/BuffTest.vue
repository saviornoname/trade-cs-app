<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Buff API Test</h1>

        <div class="flex items-center gap-4 mb-4">
            <input
                v-model="customItemId"
                type="text"
                placeholder="Введіть item_id (необов’язково)"
                class="border px-3 py-2 rounded w-64"
            />
            <button
                @click="fetchData"
                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
            >
                Завантажити ціни
            </button>
        </div>

        <div v-if="loading" class="mb-4 text-gray-600">Завантаження...</div>
        <div v-if="error" class="mb-4 text-red-600">{{ error }}</div>

        <table v-if="items.length" class="w-full border-collapse">
            <thead class="bg-gray-200">
            <tr>
                <th class="p-2 border">Зображення</th>
                <th class="p-2 border">Ціна</th>
                <th class="p-2 border">Paintwear</th>
                <th class="p-2 border">Asset ID</th>
                <th class="p-2 border">Inspect</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="item in items" :key="item.id" class="border-t">
                <td class="p-2 border">
                    <img
                        :src="item.asset_info.info.icon_url"
                        class="w-16 h-16 object-contain"
                        alt="icon"
                    />
                </td>
                <td class="p-2 border text-center">{{ item.price }} ¥</td>
                <td class="p-2 border text-center">{{ item.asset_info.paintwear }}</td>
                <td class="p-2 border text-center">{{ item.asset_info.assetid }}</td>
                <td class="p-2 border text-center">
                    <a
                        :href="item.asset_info.info.inspect_url"
                        target="_blank"
                        class="text-blue-600 underline"
                    >
                        Перегляд
                    </a>
                </td>
            </tr>
            </tbody>
        </table>

        <div v-else-if="!loading" class="text-gray-500">
            Натисни "Завантажити ціни" для перегляду результатів.
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'

const items = ref([])
const loading = ref(false)
const error = ref(null)
const customItemId = ref('')

const fetchData = async () => {
    loading.value = true
    error.value = null

    try {
        const res = await axios.get('/api/buff-test', {
            params: {
                item_id: customItemId.value || undefined,
            },
        })
        items.value = res.data.data?.items || []
    } catch (err) {
        error.value = 'Помилка при завантаженні даних'
        console.error(err)
    } finally {
        loading.value = false
    }
}
</script>
