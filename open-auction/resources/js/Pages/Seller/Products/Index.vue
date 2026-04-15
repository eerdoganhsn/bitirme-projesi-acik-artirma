<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    products: Array
});
</script>

<template>
    <Head title="Ürünlerim" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ürünlerim</h2>
                <Link :href="route('seller.products.create')" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                    + Yeni Ürün Ekle
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ürün</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tip</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fiyat / Teklif</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="product in products" :key="product.id">
                                    <td class="px-6 py-4 font-medium">{{ product.title }}</td>
                                    <td class="px-6 py-4">{{ product.category.name }}</td>
                                    <td class="px-6 py-4 uppercase text-xs">{{ product.listing_type }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded-full text-xs" :class="product.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100'">
                                            {{ product.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-bold">
                                        {{ product.listing_type === 'auction' ? '₺' + product.auction?.current_price : '₺' + product.buy_now_price }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button class="text-indigo-600 hover:text-indigo-900">Düzenle</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>