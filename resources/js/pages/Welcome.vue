<script setup lang="ts">
import {  Link } from '@inertiajs/vue3'
</script>

<template>
<!--    <Head title="Welcome">-->
<!--        <link rel="preconnect" href="https://rsms.me/" />-->
<!--        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />-->
<!--    </Head>-->

    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a] lg:justify-center lg:p-8">
        <header class="mb-6 w-full max-w-[335px] text-sm lg:max-w-4xl">
<!--            <nav class="flex items-center justify-end gap-4">-->
<!--                <Link-->
<!--                    v-if="$page.props.auth.user"-->
<!--                    :href="route('dashboard')"-->
<!--                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"-->
<!--                >-->
<!--                    Dashboard-->
<!--                </Link>-->
<!--                <template v-else>-->
<!--                    <Link-->
<!--                        :href="route('login')"-->
<!--                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"-->
<!--                    >-->
<!--                        Log in-->
<!--                    </Link>-->
<!--                    <Link-->
<!--                        :href="route('register')"-->
<!--                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"-->
<!--                    >-->
<!--                        Register-->
<!--                    </Link>-->
<!--                </template>-->
<!--            </nav>-->
        </header>

        <main class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-4xl lg:flex-row">
            <div class="flex-1 bg-white p-6 pb-12 text-sm dark:bg-[#161615] dark:text-[#EDEDEC] lg:p-20">
                <h1 class="mb-4 text-lg font-semibold">👋 Ласкаво просимо до Trade-CS</h1>

                <p class="mb-4 text-[#706f6c] dark:text-[#A1A09A]">
                    Швидкий старт:
                </p>

                <ul class="mb-4 space-y-3">
<!--                    <li>-->
<!--                        <Link-->
<!--                            :href="route('watchlist.import.view')"-->
<!--                            class="text-[#f53003] underline underline-offset-4 dark:text-[#FF4433] hover:text-red-700"-->
<!--                        >-->
<!--                            ➤ Імпортувати Watchlist (CSV)-->
<!--                        </Link>-->
<!--                    </li>-->
                    <li>
                        <Link
                            :href="route('dmarket.index')"
                            class="text-[#f53003] underline underline-offset-4 dark:text-[#FF4433] hover:text-red-700"
                        >
                            ➤ Api test
                        </Link>
                    </li>
<!--                    <li>-->
<!--                        <Link-->
<!--                            :href="route('dashboard')"-->
<!--                            class="text-[#f53003] underline underline-offset-4 dark:text-[#FF4433] hover:text-red-700"-->
<!--                        >-->
<!--                            ➤ Перейти в Dashboard-->
<!--                        </Link>-->
<!--                    </li>-->
                    <!-- Додай інші сторінки за потребою -->
                </ul>

                <div class="mt-8">
                    <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">
                        Корисні посилання:
                    </p>
                    <ul class="space-y-2 text-sm">
<!--                        <li>-->
<!--                            <a href="https://laravel.com/docs" target="_blank" class="text-[#f53003] underline dark:text-[#FF4433]">-->
<!--                                Laravel Documentation-->
<!--                            </a>-->
<!--                        </li>-->
<!--                        <li>-->
<!--                            <a href="https://laracasts.com" target="_blank" class="text-[#f53003] underline dark:text-[#FF4433]">-->
<!--                                Відеоуроки на Laracasts-->
<!--                            </a>-->
<!--                        </li>-->
<!--                        <li>-->
<!--                            <a href="https://cloud.laravel.com" target="_blank" class="inline-block mt-2 rounded border px-4 py-1 bg-black text-white dark:bg-white dark:text-black">-->
<!--                                🌐 Deploy зараз-->
<!--                            </a>-->
<!--                        </li>-->
                    </ul>
                </div>
            </div>
        </main>
    </div>
</template>
