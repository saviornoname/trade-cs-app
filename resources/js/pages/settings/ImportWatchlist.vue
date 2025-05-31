<template>
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 mt-10">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Імпорт Watchlist з CSV</h2>

            <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 mb-1"
                        for="csv_file"
                    >
                        Виберіть CSV-файл
                    </label>

                    <div class="flex items-center">
                        <label
                            class="cursor-pointer inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-200"
                        >
                            <svg
                                class="w-5 h-5 mr-2"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 4v16h16V4H4zm4 8h8M8 12l4-4 4 4"
                                ></path>
                            </svg>
                            <span>Обрати файл</span>
                            <input
                                id="csv_file"
                                type="file"
                                accept=".csv"
                                name="csv_file"
                                class="hidden"
                                required
                                @change="handleFile"
                            />
                        </label>

                        <span class="ml-3 text-sm text-gray-600 truncate" v-if="file">
              {{ file.name }}
            </span>
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-300 disabled:opacity-50"
                    >
                        {{ isSubmitting ? 'Імпортується...' : 'Імпортувати' }}
                    </button>
                </div>

                <div v-if="successMessage" class="text-green-600 text-sm">
                    {{ successMessage }}
                </div>

                <div v-if="errorMessage" class="text-red-600 text-sm">
                    {{ errorMessage }}
                </div>
            </form>
        </div>
    </div>
</template>


<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import { route } from 'ziggy-js'

const file = ref<File | null>(null)
const isSubmitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

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
    } catch (e: any) {
        errorMessage.value = e.response?.data?.message || 'Сталася помилка під час імпорту.'
        console.error(e.response?.data || e)
    } finally {
        isSubmitting.value = false
    }
}
</script>
