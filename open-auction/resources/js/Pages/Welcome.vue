<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    categories: Array,
    auctions: Array,
    products: Array,
});
</script>

<template>
    <Head title="Açık Artırma Pazaryeri" />

    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-indigo-600">AçıkArtırma</h1>
                    </div>
                    <div class="flex items-center" v-if="canLogin">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md font-medium">Panelim</Link>
                        <template v-else>
                            <Link :href="route('login')" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md font-medium">Giriş Yap</Link>
                            <Link v-if="canRegister" :href="route('register')" class="ml-4 bg-indigo-600 text-white px-4 py-2 rounded-md font-medium hover:bg-indigo-700">Kayıt Ol</Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <div class="bg-indigo-700 py-16 text-center text-white">
            <h2 class="text-4xl font-extrabold tracking-tight sm:text-5xl">Aradığın Ürüne Kendi Fiyatını Belirle</h2>
            <p class="mt-4 text-lg">Yüzlerce mağazadan binlerce ürün seni bekliyor. Teklif ver, kazan!</p>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Kategoriler</h3>
            <div class="flex space-x-4 overflow-x-auto pb-4">
                <div v-for="category in categories" :key="category.id" class="px-6 py-3 bg-white rounded-full shadow-sm border border-gray-200 text-gray-700 font-medium whitespace-nowrap hover:bg-indigo-50 hover:text-indigo-600 cursor-pointer transition">
                    {{ category.name }}
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Öne Çıkan Açık Artırmalar</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="auction in auctions" :key="auction.id" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition border border-gray-100">
                    <div class="h-48 bg-gray-200 flex items-center justify-center">
                        <span class="text-gray-400">Görsel Yok</span>
                    </div>
                    
                    <div class="p-4">
                        <p class="text-xs text-indigo-600 font-semibold mb-1">{{ auction.product.category.name }}</p>
                        <h4 class="text-lg font-bold text-gray-900 truncate">{{ auction.product.title }}</h4>
                        <p class="text-sm text-gray-500 mt-1 mb-4 truncate">{{ auction.product.shop.store_name }}</p>
                        
                        <div class="flex justify-between items-end border-t pt-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Şu Anki Fiyat</p>
                                <p class="text-xl font-black text-gray-900">₺{{ auction.current_price }}</p>
                            </div>
                            <button class="bg-gray-900 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-800 transition">İncele</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 pb-20">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Hemen Alınabilir Ürünler</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="product in products" :key="'prod-'+product.id" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition border border-gray-100">
                    <div class="h-48 bg-gray-50 flex items-center justify-center">
                        <span class="text-gray-400">Görsel Yok</span>
                    </div>
                    
                    <div class="p-4">
                        <p class="text-xs text-green-600 font-semibold mb-1">{{ product.category.name }}</p>
                        <h4 class="text-lg font-bold text-gray-900 truncate">{{ product.title }}</h4>
                        <p class="text-sm text-gray-500 mt-1 mb-4 truncate">{{ product.shop.store_name }}</p>
                        
                        <div class="flex justify-between items-end border-t pt-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Hemen Al Fiyatı</p>
                                <p class="text-xl font-black text-gray-900">₺{{ product.buy_now_price || '299.00' }}</p>
                            </div>
                            <button class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 transition">Sepete Ekle</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>