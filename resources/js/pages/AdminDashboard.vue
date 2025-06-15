<script setup lang="ts">
import axios from 'axios';
import { onMounted, ref } from 'vue';
import { route } from 'ziggy-js';

const targets = ref<any[]>([]);

const loadTargets = async () => {
    const res = await axios.get(route('dmarket.targets'));
    targets.value = res.data.Items ?? [];
};

onMounted(() => {
    loadTargets();
});
</script>

<template>
    <div class="p-4">
        <h1 class="mb-4 text-xl font-bold">Admin Dashboard</h1>
        <h2 class="mb-2 font-semibold">Active Targets</h2>
        <table class="mb-4 w-full border text-sm">
            <thead class="bg-gray-300">
            <tr>
                <th class="border px-2 py-1">Title</th>
                <th class="border px-2 py-1">Price</th>
                <th class="border px-2 py-1">Amount</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="t in targets" :key="t.TargetId" class="border-t">
                <td class="border px-2 py-1">{{ t.Title }}</td>
                <td class="border px-2 py-1 text-right">{{ t.Price?.Amount }}</td>
                <td class="border px-2 py-1 text-right">{{ t.Amount }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>
