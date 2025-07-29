<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref } from 'vue';

interface InventoryItem {
    id: string;
    title: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Inventory', href: '/dashboard/inventory' },
];

const inventory = ref<InventoryItem[]>([]);
const activeIds = ref<Set<string>>(new Set());
const loading = ref(false);

const fetchData = async () => {
    loading.value = true;
    try {
        const [invRes, offersRes] = await Promise.all([
            axios.get(route('dmarket.inventory')),
            axios.get(route('dmarket.offers')),
        ]);
        inventory.value = invRes.data?.items ?? [];
        const orders = offersRes.data?.orders ?? [];
        activeIds.value = new Set(orders.map((o: any) => o.itemId || o.assetId || o.id));
    } catch (e) {
        console.error('Failed to load inventory', e);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchData);
</script>

<template>
    <Head title="Inventory" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 space-y-4">
            <div v-if="loading">Loading...</div>
            <div v-else class="overflow-auto">
                <table class="min-w-full text-sm" v-if="inventory.length">
                    <thead>
                    <tr class="bg-muted text-muted-foreground">
                        <th class="border px-2 py-1 text-left">Item</th>
                        <th class="border px-2 py-1 text-left">Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr
                        v-for="item in inventory"
                        :key="item.id"
                        class="border-t"
                        :class="{
                                'bg-yellow-100 dark:bg-yellow-900': activeIds.has(item.id),
                            }"
                    >
                        <td class="border px-2 py-1">{{ item.title }}</td>
                        <td class="border px-2 py-1">
                            <span v-if="activeIds.has(item.id)">Виставлено на продаж</span>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div v-else>Інвентар порожній.</div>
            </div>
        </div>
    </AppLayout>
</template>
