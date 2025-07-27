<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    DialogClose
} from '@/components/ui/dialog';
import axios from 'axios';
import { route } from 'ziggy-js';

interface FloatRange {
    id: number;
    name: string; // e.g., "0.00 - 0.07"
}

interface Filter {
    float_range_id: number | null;
    paint_seed: string | null;
    phase: string | null;
}

defineProps<{
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
        console.log(res.data);
        floatRanges.value = res.data;
    } catch (e) {
        console.error('Failed to load float ranges', e);
    }
};

const addFilter = () => {
    filters.value.push({ ...newFilter.value });
    newFilter.value = { float_range_id: null, paint_seed: '', phase: '' };
};

const saveFilters = () => {
    emit('save', filters.value);
    emit('close');
};

onMounted(fetchFloatRanges);
</script>

<template>
    <Dialog :open="show" @update:open="(val) => { if (!val) emit('close') }">
        <DialogContent class="w-[600px]" aria-describedby="filters-description">
            <DialogHeader>
                <DialogTitle>Filters</DialogTitle>
            </DialogHeader>

            <p id="filters-description" class="text-sm text-muted-foreground mb-2">
                Select a predefined float range, seed, or phase to add a new filter.
            </p>

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
                    <div
                        v-for="(filter, index) in filters"
                        :key="index"
                        class="flex gap-2 items-center border px-2 py-1 rounded bg-muted"
                    >
            <span v-if="filter.float_range_id">
              Float: {{ floatRanges.find(fr => fr.id === filter.float_range_id)?.label || '—' }}
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
