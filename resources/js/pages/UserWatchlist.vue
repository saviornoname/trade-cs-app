<script setup lang="ts">
import FiltersModal from '@/components/FiltersModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import { route } from 'ziggy-js';

interface Filter {
    id: number;
    min_float: number | null;
    max_float: number | null;
    paint_seed: string | null;
    phase: string | null;
}
interface Item {
    id: number;
    title: string;
    active: boolean;
    filters: Filter[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Watchlist Items', href: '/dashboard/watchlist' },
];

const items = ref<Item[]>([]);
const search = ref('');
const activeOnly = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = 10;
const filterItemId = ref<number | null>(null);
const deleteItemId = ref<number | null>(null);

const loadItems = async (append = false) => {
    const res = await axios.get(route('watchlist.items'), {
        params: {
            page: currentPage.value,
            per_page: perPage,
            search: search.value,
            active: activeOnly.value ? 1 : 0,
        },
    });
    if (append) {
        items.value = [...items.value, ...res.data.data];
    } else {
        items.value = res.data.data;
    }
    totalPages.value = res.data.last_page;
};

const loadMore = async () => {
    if (currentPage.value >= totalPages.value) return;
    currentPage.value += 1;
    await loadItems(true);
};

const toggleActive = async (item: Item) => {
    await axios.patch(route('watchlist.toggle', { item: item.id }));
    item.active = !item.active;
};

const deleteItem = async () => {
    if (deleteItemId.value === null) return;
    await axios.delete(route('watchlist.delete', { item: deleteItemId.value }));
    items.value = items.value.filter((i) => i.id !== deleteItemId.value);
    deleteItemId.value = null;
};

watch([search, activeOnly], () => {
    currentPage.value = 1;
    loadItems();
});

onMounted(loadItems);

const filterText = (filters: Filter[]) => {
    if (!filters || filters.length === 0) return '—';
    return filters
        .map((f) => {
            const float = f.min_float !== null || f.max_float !== null ? `Float: ${f.min_float ?? ''}-${f.max_float ?? ''}` : '';
            const phase = f.phase ? `Phase: ${f.phase}` : '';
            const seed = f.paint_seed ? `Seed: ${f.paint_seed}` : '';
            return [float, phase, seed].filter(Boolean).join(', ');
        })
        .join('; ');
};
</script>

<template>
    <Head title="Watchlist Items" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center gap-2">
                <Input v-model="search" placeholder="Search..." class="max-w-xs" />
                <label class="flex items-center gap-1 text-sm">
                    <Checkbox v-model:checked="activeOnly" /> Active only
                </label>
            </div>
            <div class="overflow-auto">
                <table class="min-w-full text-sm">
                    <thead>
                    <tr class="bg-muted text-muted-foreground">
                        <th class="border px-2 py-1 text-left">Item Name</th>
                        <th class="border px-2 py-1 text-left">Filters</th>
                        <th class="border px-2 py-1 text-center">Active</th>
                        <th class="border px-2 py-1 text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in items" :key="item.id" class="border-t">
                        <td class="border px-2 py-1">{{ item.title }}</td>
                        <td class="border px-2 py-1">{{ filterText(item.filters) }}</td>
                        <td class="border px-2 py-1 text-center">
                            <Checkbox :checked="item.active" @update:checked="toggleActive(item)" />
                        </td>
                        <td class="border px-2 py-1 text-center space-x-1">
                            <Button size="sm" variant="outline" @click="filterItemId = item.id">Edit</Button>
                            <Button size="sm" variant="destructive" @click="deleteItemId = item.id">Delete</Button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex items-center gap-2">
                <Button size="sm" variant="outline" @click="currentPage = Math.max(1, currentPage - 1); loadItems();">Prev</Button>
                <span>Page {{ currentPage }} / {{ totalPages }}</span>
                <Button v-if="currentPage < totalPages" size="sm" variant="outline" @click="loadMore">Load more</Button>
            </div>
        </div>
        <FiltersModal :show="filterItemId !== null" :item-id="filterItemId" @close="filterItemId = null; loadItems();" />
        <Dialog :open="deleteItemId !== null" @update:open="val => { if(!val) deleteItemId = null }">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete item?</DialogTitle>
                </DialogHeader>
                <p class="text-sm">Are you sure you want to remove this item from watchlist?</p>
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary">Cancel</Button>
                    </DialogClose>
                    <Button variant="destructive" @click="deleteItem">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
