<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { route } from 'ziggy-js'

interface WatchItem {
    id: number
    title: string
    active: boolean
}

const items = ref<WatchItem[]>([])
const comparisons = ref<any[]>([])
const file = ref<File | null>(null)
const isSubmitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const loadItems = async () => {
    const res = await axios.get(route('watchlist.items'))
    items.value = res.data.filter((i: WatchItem) => i.active)
}

const toggleActive = async (item: WatchItem) => {
    await axios.patch(route('watchlist.toggle', { item: item.id }))
    item.active = !item.active
}

const loadComparisons = async () => {
    const res = await axios.get(route('dmarket.compare'))
    comparisons.value = res.data
}

onMounted(async () => {
    await loadItems()
    await loadComparisons()
})

const handleFile = (e: Event) => {
    const target = e.target as HTMLInputElement
    if (target.files && target.files.length > 0) {
        file.value = target.files[0]
    }
}

const submit = async () => {
    if (!file.value) return

    isSubmitting.value = true
    successMessage.value = ''
    errorMessage.value = ''

    const formData = new FormData()
    formData.append('csv_file', file.value)

    try {
        await axios.post(route('watchlist.import'), formData)
        successMessage.value = 'Імпорт завершено успішно.'
        file.value = null
        await loadItems()
    } catch (e: any) {

        errorMessage.value = e.response?.data?.message || 'Сталася помилка під час імпорту.'
        console.error(e.response?.data || e)
    } finally {
        isSubmitting.value = false
    }
}
</script>

<template>
    <div class="p-4">
        <h1 class="text-xl font-bold mb-4">Watchlist Items</h1>

        <form @submit.prevent="submit" class="mb-6 flex items-center gap-2" enctype="multipart/form-data">
            <label class="border px-3 py-1 rounded cursor-pointer bg-gray-200 text-gray-800">
                <span>Вибрати CSV</span>
                <input type="file" accept=".csv" class="hidden" @change="handleFile" />
            </label>
            <button type="submit" :disabled="isSubmitting" class="border px-3 py-1 rounded bg-blue-600 text-white">
                {{ isSubmitting ? 'Імпорт...' : 'Імпортувати' }}
            </button>
            <span class="text-green-600 text-sm" v-if="successMessage">{{ successMessage }}</span>
            <span class="text-red-600 text-sm" v-if="errorMessage">{{ errorMessage }}</span>
        </form>
        <table class="border mb-6 text-sm">
            <thead class="bg-gray-300 text-gray-900 dark:bg-gray-700 dark:text-gray-100">
            <tr>
                <th class="px-2 py-1 border">Title</th>
                <th class="px-2 py-1 border">Active</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="item in items" :key="item.id" class="border-t">
                <td class="px-2 py-1 border">{{ item.title }}</td>
                <td class="px-2 py-1 border text-center">
                    <input type="checkbox" :checked="item.active" @change="toggleActive(item)" />
                </td>
            </tr>
            </tbody>
        </table>

        <button @click="loadComparisons" class="border px-3 py-1 rounded mb-4">Refresh Comparison</button>

        <table class="border text-sm w-full">
            <thead class="bg-gray-300 text-gray-900 dark:bg-gray-700 dark:text-gray-100">
            <tr>
                <th class="px-2 py-1 border">Title</th>
                <th class="px-2 py-1 border">Phase</th>
                <th class="px-2 py-1 border">Float</th>
                <th class="px-2 py-1 border">DMarket $</th>
                <th class="px-2 py-1 border">Buff ¥</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="c in comparisons" :key="c.title + c.float + c.phase" class="border-t">
                <td class="px-2 py-1 border">{{ c.title }}</td>
                <td class="px-2 py-1 border">{{ c.phase || '/' }}</td>
                <td class="px-2 py-1 border">{{ c.float || '/' }}</td>
                <td class="px-2 py-1 border text-right">{{ c.dmarket_price_usd ?? '-' }}</td>
                <td class="px-2 py-1 border text-right">{{ c.best_buff_price_cny ?? '-' }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>
