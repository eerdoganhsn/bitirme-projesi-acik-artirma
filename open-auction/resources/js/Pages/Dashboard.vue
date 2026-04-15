<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';


const user = usePage().props.auth.user;
</script>

<template>
    <Head title="Panelim" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Panelim</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="user.is_seller" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-indigo-500 mb-6">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-2xl font-bold text-indigo-600 mb-2">Hoş geldin, {{ user.shop.store_name }}! 🏪</h3>
                        <p class="text-gray-600">Burası senin satıcı panelin. Mağazanın durumu şu an: <span class="font-bold uppercase">{{ user.shop.status }}</span></p>
                        
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                            <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                                <p class="text-sm text-gray-500 uppercase">Aktif Ürünler</p>
                                <p class="text-2xl font-black text-indigo-700">0</p>
                            </div>
                            <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                                <p class="text-sm text-gray-500 uppercase">Açık Artırmalar</p>
                                <p class="text-2xl font-black text-indigo-700">0</p>
                            </div>
                            <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                                <p class="text-sm text-gray-500 uppercase">Bekleyen Siparişler</p>
                                <p class="text-2xl font-black text-indigo-700">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-green-500">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-2xl font-bold text-green-600 mb-2">Hoş geldin, {{ user.name }}! 🛍️</h3>
                        <p class="text-gray-600">Açık artırmaları takip edebilir ve favori ürünlerine teklif verebilirsin.</p>

                        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                            <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                                <p class="text-sm text-gray-500 uppercase">Verilen Teklifler</p>
                                <p class="text-2xl font-black text-green-700">0</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                                <p class="text-sm text-gray-500 uppercase">Takip Edilenler</p>
                                <p class="text-2xl font-black text-green-700">0</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                                <p class="text-sm text-gray-500 uppercase">Kargom Nerede?</p>
                                <p class="text-2xl font-black text-green-700">0</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>