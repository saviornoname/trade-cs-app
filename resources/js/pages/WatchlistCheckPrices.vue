<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Watchlist Prices', href: '/dashboard/watchlist/prices' },
];

interface MarketItem {
    title: string;
    target_max_price_usd?: number | null;
    market_min_prices_usd?: number[];
}

const items = ref<MarketItem[]>([]);
const search = ref('');
const sortAsc = ref(false);

const fetchData = async () => {
    const res = await axios.get(route('dmarket.targets-market'));
    items.value = res.data || [];
};

onMounted(fetchData);

const processed = computed(() => {
    const term = search.value.toLowerCase();
    return items.value
        .map((item) => {
            const prices = Array.isArray(item.market_min_prices_usd) ? item.market_min_prices_usd : [];
            const marketPrice = prices.length > 0 ? prices[0] : null;
            const nextPrice = prices.length > 1 ? prices[1] : null;
            const target = item.target_max_price_usd ?? null;
            const profitUsd = marketPrice !== null && target !== null ? marketPrice - target : null;
            const profitPercent = profitUsd !== null && target !== null ? (profitUsd / target) * 100 : null;
            const difference = marketPrice !== null && nextPrice !== null ? nextPrice - marketPrice : null;
            return { ...item, marketPrice, target, profitUsd, profitPercent, difference };
        })
        .filter((i) => i.title.toLowerCase().includes(term))
        .sort((a, b) => {
            const aVal = a.profitUsd ?? -Infinity;
            const bVal = b.profitUsd ?? -Infinity;
            return sortAsc.value ? aVal - bVal : bVal - aVal;
        });
});
</script>

<template>
    <Head title="Watchlist Prices" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center gap-2">
                <Input v-model="search" placeholder="Search..." class="max-w-xs" />
                <Button variant="outline" size="sm" @click="sortAsc = !sortAsc">Sort {{ sortAsc ? '↑' : '↓' }}</Button>
            </div>
            <div class="overflow-auto">
                <table class="min-w-full text-sm">
                    <thead>
                    <tr class="bg-muted text-muted-foreground">
                        <th class="border px-2 py-1 text-left">Item</th>
                        <th class="border px-2 py-1 text-right">Market Price</th>
                        <th class="border px-2 py-1 text-right">Target Price</th>
                        <th class="border px-2 py-1 text-right">
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <span class="cursor-help">Profit $</span>
                                    </TooltipTrigger>
                                    <TooltipContent><p>Potential profit in USD</p></TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </th>
                        <th class="border px-2 py-1 text-right">
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <span class="cursor-help">Profit %</span>
                                    </TooltipTrigger>
                                    <TooltipContent><p>Potential profit percentage</p></TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </th>
                        <th class="border px-2 py-1 text-right">
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <span class="cursor-help">Difference</span>
                                    </TooltipTrigger>
                                    <TooltipContent><p>Difference between lowest orders</p></TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in processed" :key="item.title" class="border-t">
                        <td class="border px-2 py-1">{{ item.title }}</td>
                        <td class="border px-2 py-1 text-right">
                            {{ item.marketPrice !== null ? item.marketPrice.toFixed(2) : '-' }}
                        </td>
                        <td class="border px-2 py-1 text-right">
                            {{ item.target !== null ? item.target.toFixed(2) : '-' }}
                        </td>
                        <td class="border px-2 py-1 text-right">
                            {{ item.profitUsd !== null ? item.profitUsd.toFixed(2) : '-' }}
                        </td>
                        <td class="border px-2 py-1 text-right">
                            {{ item.profitPercent !== null ? item.profitPercent.toFixed(2) + '%' : '-' }}
                        </td>
                        <td class="border px-2 py-1 text-right">
                            {{ item.difference !== null ? item.difference.toFixed(2) : '-' }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
