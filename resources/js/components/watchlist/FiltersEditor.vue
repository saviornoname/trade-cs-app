<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import { route } from 'ziggy-js';

interface FloatRange {
    id: number;
    name: string;
}

interface Filter {
    id: number;
    float_range_id: number | null;
    paint_seed: string | null;
    phase: string | null;
    active: boolean;
}

interface NewFilter {
    float_range_id: number | null;
    paint_seed: string | null;
    phase: string | null;
}

const props = defineProps<{
    show: boolean;
    itemId: number | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'save', filters: Filter[]): void;
}>();

const filters = ref<Filter[]>([]);
const floatRanges = ref<FloatRange[]>([]);
const newFilter = ref<NewFilter>({
    float_range_id: null,
    paint_seed: '',
    phase: '',
});

const editingIndex = ref<number | null>(null);
const editBuffer = ref<Filter | null>(null);

const fetchFloatRanges = async () => {
    try {
        const res = await axios.get(route('float-ranges.list'));
        floatRanges.value = res.data;
    } catch (e) {
        console.error('Failed to load float ranges', e);
    }
};

const fetchExistingFilters = async (id: number) => {
    try {
        const res = await axios.get(route('watchlist.filters', { item: id }));
        filters.value = res.data.map((f: any) => ({
            id: f.id,
            float_range_id: f.paintwear_range_id ?? null,
            paint_seed: f.paint_seed ?? '',
            phase: f.phase ?? '',
            active: !!f.active,
        }));
    } catch (e) {
        console.error('Failed to load filters', e);
    }
};

watch([() => props.show, () => props.itemId], ([val, id]) => {
    if (val && id !== null) {
        fetchExistingFilters(id);
    }
});

const addFilter = async () => {
    if (props.itemId === null) return;
    const res = await axios.post(route('watchlist.filters.add', { item: props.itemId }), {
        paintwear_range_id: newFilter.value.float_range_id,
        paint_seed: newFilter.value.paint_seed,
        phase: newFilter.value.phase,
    });
    filters.value.push({
        id: res.data.id,
        float_range_id: res.data.paintwear_range_id ?? null,
        paint_seed: res.data.paint_seed ?? '',
        phase: res.data.phase ?? '',
        active: !!res.data.active,
    });
    newFilter.value = { float_range_id: null, paint_seed: '', phase: '' };
};

const startEdit = (idx: number) => {
    editingIndex.value = idx;
    editBuffer.value = { ...filters.value[idx] };
};

const cancelEdit = () => {
    editingIndex.value = null;
    editBuffer.value = null;
};

const saveEdit = async (idx: number) => {
    if (!editBuffer.value) return;
    await axios.patch(route('watchlist.filters.update-one', { filter: editBuffer.value.id }), {
        paintwear_range_id: editBuffer.value.float_range_id,
        paint_seed: editBuffer.value.paint_seed,
        phase: editBuffer.value.phase,
        active: editBuffer.value.active ? 1 : 0,
    });
    filters.value[idx] = { ...editBuffer.value };
    cancelEdit();
};

const removeFilter = async (filter: Filter, idx: number) => {
    await axios.delete(route('watchlist.filters.delete', { filter: filter.id }));
    filters.value.splice(idx, 1);
};

const toggleActive = async (filter: Filter, idx: number) => {
    const newStatus = !filter.active;
    await axios.patch(route('watchlist.filters.update-one', { filter: filter.id }), { active: newStatus ? 1 : 0 });
    filters.value[idx].active = newStatus;
};

const saveFilters = () => {
    emit('save', filters.value);
    emit('close');
};

onMounted(fetchFloatRanges);
</script>

<template>
    <Dialog
        :open="props.show"
        @update:open="
            (val) => {
                if (!val) emit('close');
            }
        "
    >
        <DialogContent class="w-[600px]">
            <!-- ✅ aria-describedby на реальному DOM-елементі -->
            <DialogHeader>
                <DialogTitle>Filters</DialogTitle>
            </DialogHeader>
            <DialogDescription>Select a predefined float range, seed, or phase to add a new filter.</DialogDescription>

            <div class="space-y-2">
                <div class="flex gap-2">
                    <select v-model.number="newFilter.float_range_id" class="w-1/3 rounded border px-2 py-1 text-sm">
                        <option disabled :value="null">Select Float Range</option>
                        <option v-for="range in floatRanges" :key="range.id" :value="range.id">
                            {{ range.name }}
                        </option>
                    </select>

                    <Input v-model="newFilter.paint_seed" placeholder="Paint Seed" class="w-1/3" />
                    <Input v-model="newFilter.phase" placeholder="Phase" class="w-1/3" />
                    <Button variant="outline" @click="addFilter">Add</Button>
                </div>

                <div v-if="filters.length > 0" class="overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                        <tr>
                            <th class="border px-2 py-1">Float</th>
                            <th class="border px-2 py-1">Seed</th>
                            <th class="border px-2 py-1">Phase</th>
                            <th class="border px-2 py-1 text-center">Статус</th>
                            <th class="border px-2 py-1 text-center">Дії</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(filter, index) in filters" :key="filter.id" class="border-t">
                            <template v-if="editingIndex === index && editBuffer">
                                <td class="border px-2 py-1">
                                    <select v-model.number="editBuffer.float_range_id" class="w-full rounded border px-2 py-1">
                                        <option :value="null">—</option>
                                        <option v-for="range in floatRanges" :key="range.id" :value="range.id">{{ range.name }}</option>
                                    </select>
                                </td>
                                <td class="border px-2 py-1"><Input v-model="editBuffer.paint_seed" class="w-full" /></td>
                                <td class="border px-2 py-1"><Input v-model="editBuffer.phase" class="w-full" /></td>
                                <td class="border px-2 py-1 text-center">
                                    <Checkbox v-model="editBuffer.active" />
                                </td>
                                <td class="flex gap-1 border px-2 py-1">
                                    <Button size="sm" variant="default" @click="saveEdit(index)">Save</Button>
                                    <Button size="sm" variant="secondary" @click="cancelEdit">Cancel</Button>
                                </td>
                            </template>
                            <template v-else>
                                <td class="border px-2 py-1">
                                    {{ filter.float_range_id ? floatRanges.find((fr) => fr.id === filter.float_range_id)?.name || '—' : '—' }}
                                </td>
                                <td class="border px-2 py-1">{{ filter.paint_seed || '—' }}</td>
                                <td class="border px-2 py-1">{{ filter.phase || '—' }}</td>
                                <td class="border px-2 py-1 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <Checkbox :model-value="filter.active" @click="toggleActive(filter, index)" />
                                        <span class="text-xs" :class="filter.active ? 'text-green-600' : 'text-gray-500'">
                                                {{ filter.active ? 'Active' : 'Inactive' }}
                                            </span>
                                    </div>
                                </td>
                                <td class="flex gap-1 border px-2 py-1">
                                    <Button size="sm" variant="outline" :disabled="editingIndex !== null" @click="startEdit(index)">Edit</Button>
                                    <Button size="sm" variant="destructive" @click="removeFilter(filter, index)">Delete</Button>
                                </td>
                            </template>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <DialogFooter class="gap-2 pt-4">
                <DialogClose as-child>
                    <Button variant="secondary">Cancel</Button>
                </DialogClose>
                <Button variant="default" @click="saveFilters">Save</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
