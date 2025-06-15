<script setup lang="ts">
import axios from 'axios';
import { ref, watch, onMounted } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{ itemId: number | null; show: boolean }>();
const emit = defineEmits(['close']);

const filters = ref<any[]>([]);
const fnRanges = ref<Record<string, { min: number; max: number }>>({});

const newFilter = ref({
    min_float: '',
    max_float: '',
    paint_seed: '',
    phase: '',
    fn_group: '',
});

const loadRanges = async () => {
    const res = await axios.get(route('float-ranges.list'));
    const map: Record<string, { min: number; max: number }> = {};
    res.data.forEach((r: any) => {
        map[r.name] = { min: parseFloat(r.min), max: parseFloat(r.max) };
    });
    fnRanges.value = map;
};

onMounted(() => {
    loadRanges();
});
const loadFilters = async () => {
    if (props.itemId === null) return;
    const res = await axios.get(route('watchlist.filters', { item: props.itemId }));
    filters.value = res.data;
};

const addFilter = async () => {
    if (props.itemId === null) return;
    await axios.post(route('watchlist.filters.add', { item: props.itemId }), newFilter.value);
    newFilter.value = { min_float: '', max_float: '', paint_seed: '', phase: '', fn_group: '' };
    await loadFilters();
};

const deleteFilter = async (id: number) => {
    await axios.delete(route('watchlist.filters.delete', { filter: id }));
    await loadFilters();
};

watch(
    () => newFilter.value.fn_group,
    (val) => {
        if (val && fnRanges.value[val]) {
            newFilter.value.min_float = fnRanges.value[val].min;
            newFilter.value.max_float = fnRanges.value[val].max;
        } else {
            newFilter.value.min_float = '';
            newFilter.value.max_float = '';
        }
    },
);
</script>

<template>
    <div v-if="show" class="fixed inset-0 flex items-center justify-center bg-black/50">
        <div class="rounded bg-white p-4 text-black dark:bg-neutral-800 dark:text-white">
            <h2 class="mb-2 font-bold">Filters</h2>
            <table class="mb-2 border text-sm">
                <thead>
                <tr>
                    <th class="border px-2 py-1">Min</th>
                    <th class="border px-2 py-1">Max</th>
                    <th class="border px-2 py-1">Seed</th>
                    <th class="border px-2 py-1">Phase</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="f in filters" :key="f.id">
                    <td class="border px-2 py-1 text-right">{{ f.min_float ?? '-' }}</td>
                    <td class="border px-2 py-1 text-right">{{ f.max_float ?? '-' }}</td>
                    <td class="border px-2 py-1">{{ f.paint_seed ?? '-' }}</td>
                    <td class="border px-2 py-1">{{ f.phase ?? '-' }}</td>
                    <td class="border px-2 py-1"><button @click="deleteFilter(f.id)" class="border px-2">x</button></td>
                </tr>
                </tbody>
            </table>
            <div class="mb-2 flex flex-wrap gap-1">
                <select v-model="newFilter.fn_group" class="w-24 border px-1">
                    <option value="">FN Group</option>
                    <option v-for="(r, key) in fnRanges" :key="key" :value="key">
                        {{ key }}
                    </option>
                </select>
                <input v-model.number="newFilter.min_float" placeholder="Min" class="w-20 border px-1" />
                <input v-model.number="newFilter.max_float" placeholder="Max" class="w-20 border px-1" />
                <input v-model="newFilter.paint_seed" placeholder="Seed" class="w-20 border px-1" />
                <input v-model="newFilter.phase" placeholder="Phase" class="w-20 border px-1" />
                <button @click="addFilter" class="border px-2">Add</button>
            </div>
            <button @click="emit('close')" class="border px-2">Close</button>
        </div>
    </div>
</template>
