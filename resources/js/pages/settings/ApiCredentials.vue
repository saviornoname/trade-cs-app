<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Props {
    credentials: {
        buff_cookie: string | null;
        dmarket_public_key: string | null;
        dmarket_secret_key: string | null;
    } | null;
}

const props = defineProps<Props>();

const form = useForm({
    buff_cookie: props.credentials?.buff_cookie || '',
    dmarket_public_key: props.credentials?.dmarket_public_key || '',
    dmarket_secret_key: props.credentials?.dmarket_secret_key || '',
});

const submit = () => {
    form.patch(route('api-credentials.update'));
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'API credentials', href: '/settings/api-credentials' }]">
        <Head title="API credentials" />
        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="API credentials" description="Update API access keys" />
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="buff_cookie">BUFF_COOKIE</Label>
                        <Input id="buff_cookie" v-model="form.buff_cookie" />
                        <InputError :message="form.errors.buff_cookie" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="dmarket_public_key">DMARKET_PUBLIC_KEY</Label>
                        <Input id="dmarket_public_key" v-model="form.dmarket_public_key" />
                        <InputError :message="form.errors.dmarket_public_key" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="dmarket_secret_key">DMARKET_SECRET_KEY</Label>
                        <Input id="dmarket_secret_key" v-model="form.dmarket_secret_key" />
                        <InputError :message="form.errors.dmarket_secret_key" />
                    </div>
                    <Button type="submit" :disabled="form.processing">Save</Button>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
