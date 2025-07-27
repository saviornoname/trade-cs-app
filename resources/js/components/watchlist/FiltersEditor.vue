<script setup lang="ts">
import { Button } from '@/components/ui/button';
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
const newFilter = ref<Filter>({
    float_range_id: null,
    paint_seed: '',
    phase: '',
});

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
        filters.value = res.data;
    } catch (e) {
        console.error('Failed to load filters', e);
    }
};

watch([() => props.show, () => props.itemId], ([val, id]) => {
    if (val && id !== null) {
        fetchExistingFilters(id);
    }
});

const addFilter = () => {
    filters.value.push({ ...newFilter.value });
    newFilter.value = { float_range_id: null, paint_seed: '', phase: '' };
};

const saveFilters = async () => {
    if (props.itemId === null) return;
    await axios.put(route('watchlist.filters.update', { item: props.itemId }), {
        filters: filters.value.map((f) => ({
            paintwear_range_id: f.float_range_id,
            paint_seed: f.paint_seed,
            phase: f.phase,
        })),
    });
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

                <div v-if="filters.length > 0" class="space-y-1 text-sm">
                    <div v-for="(filter, index) in filters" :key="index" class="bg-muted flex items-center gap-2 rounded border px-2 py-1">
                        <span v-if="filter.float_range_id">
                            Float: {{ floatRanges.find((fr) => fr.id === filter.float_range_id)?.name || '—' }}
                        </span>
                        <span v-if="filter.paint_seed">Seed: {{ filter.paint_seed }}</span>
                        <span v-if="filter.phase">Phase: {{ filter.phase }}</span>
                    </div>
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
