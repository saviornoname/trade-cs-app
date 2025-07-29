<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref } from 'vue';

interface InventoryItem {
    id: string;
    title: string;
    image?: string;
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
        const invItems = invRes.data?.Items ?? invRes.data?.items ?? invRes.data?.data ?? [];
        inventory.value = invItems.map((it: any) => ({
            id: it.AssetID ?? it.id ?? it.assetId ?? '',
            title: it.Title ?? it.title ?? '',
            image: it.ImageURL ?? it.image,
        }));

        const orders = offersRes.data?.Orders ?? offersRes.data?.orders ?? offersRes.data ?? [];
        activeIds.value = new Set(
            orders.map((o: any) => o.ItemID ?? o.itemId ?? o.assetId ?? o.id)
        );
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
                        <td class="border px-2 py-1 flex items-center gap-2">
                            <img
                                v-if="item.image"
                                :src="item.image"
                                :alt="item.title"
                                class="h-8 w-8 object-contain"
                            />
                            <span>{{ item.title }}</span>
                        </td>
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
