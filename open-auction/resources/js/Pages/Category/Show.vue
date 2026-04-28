<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    currentCategory: Object,
    products: Object,
    categories: Array
});

const getPrice = (product) => {
    if (product.buy_now_price) return product.buy_now_price;
    if (product.auction?.current_price) return product.auction.current_price;
    if (product.auction?.starting_price) return product.auction.starting_price;
    return 0;
};
</script>

<template>
    <Head :title="currentCategory.name + ' Ürünleri'" />

    <div class="min-h-screen bg-gray-50 pb-20">
        <nav class="bg-white border-b py-4 mb-8">
            <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
                <Link href="/" class="text-indigo-600 font-black italic text-xl">AçıkArtırma</Link>
                <div class="text-sm text-gray-500 font-medium">
                    <Link href="/" class="hover:text-indigo-600">Anasayfa</Link>
                    <span class="mx-2">/</span>
                    <span class="text-gray-900 font-bold">{{ currentCategory.name }}</span>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row gap-8 items-start">
                
                <div class="w-full md:w-[280px] lg:w-[300px] flex-shrink-0 space-y-6 sticky top-8">
                    
                    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
                        <h3 class="text-sm font-black text-gray-900 mb-4 uppercase tracking-widest border-b border-gray-100 pb-3">Kategoriler</h3>
                        <ul class="space-y-1">
                            <li v-for="cat in categories" :key="cat.id">
                                <Link :href="route('category.show', cat.id)" class="block px-4 py-2.5 rounded-xl text-sm font-bold transition-all" :class="cat.id === currentCategory.id ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600'">
                                    {{ cat.name }}
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
                        <h3 class="text-sm font-black text-gray-900 mb-4 uppercase tracking-widest border-b border-gray-100 pb-3">Filtrele</h3>
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-3">İlan Tipi</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 w-4 h-4">
                                    <span class="text-sm font-bold text-gray-700">Açık Artırma</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 w-4 h-4">
                                    <span class="text-sm font-bold text-gray-700">Hemen Al</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-3">Fiyat Aralığı</label>
                            <div class="flex items-center gap-2">
                                <input type="number" placeholder="Min" class="w-full text-sm rounded-xl border-gray-200 py-2 px-3 focus:ring-indigo-500 font-bold">
                                <span class="text-gray-400">-</span>
                                <input type="number" placeholder="Max" class="w-full text-sm rounded-xl border-gray-200 py-2 px-3 focus:ring-indigo-500 font-bold">
                            </div>
                            <button class="w-full mt-4 bg-gray-900 text-white text-xs font-black uppercase tracking-widest py-3 rounded-xl hover:bg-indigo-600 transition">Uygula</button>
                        </div>
                    </div>
                </div>

                <div class="flex-1 w-full min-w-0">
                    <div class="mb-8 flex justify-between items-end">
                        <div>
                            <h1 class="text-3xl font-black text-gray-900">{{ currentCategory.name }} Ürünleri</h1>
                            <p class="text-gray-500 mt-1 font-medium">Bu kategoride {{ products.total }} ürün bulundu.</p>
                        </div>
                        <select class="border-gray-200 rounded-xl text-sm font-bold text-gray-700 py-2 pl-4 pr-10 focus:ring-indigo-500">
                            <option>En Yeniler</option>
                            <option>Fiyat: Düşükten Yükseğe</option>
                            <option>Fiyat: Yüksekten Düşüğe</option>
                        </select>
                    </div>

                    <div v-if="products.data.length === 0" class="bg-white rounded-[3rem] border-2 border-dashed border-gray-200 py-32 text-center">
                        <span class="text-6xl mb-4 block">📦</span>
                        <h3 class="text-xl font-black text-gray-900 mb-2">Buralar Çok Issız</h3>
                        <p class="text-gray-500">Bu kategoride henüz hiç ürün listelenmemiş.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div v-for="product in products.data" :key="product.id">
                            <Link :href="route('product.show', product.id)" class="group block bg-white rounded-[2rem] p-4 border border-gray-100 hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 h-full flex flex-col">
                                
                                <div class="h-64 rounded-3xl overflow-hidden mb-5 relative bg-gray-50">
                                    <img v-if="product.cover_image" :src="'/storage/' + product.cover_image.image_path" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                                    <div v-else class="h-full w-full flex items-center justify-center text-gray-300 font-black text-sm uppercase tracking-widest">Görsel Yok</div>
                                    <div v-if="product.listing_type === 'auction'" class="absolute top-4 left-4 bg-red-500 text-white text-[10px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest shadow-lg animate-pulse">İhalede</div>
                                </div>
                                
                                <div class="px-2 flex-1 flex flex-col justify-between">
                                    <div>
                                        <h4 class="text-xl font-bold text-gray-900 leading-tight mb-2 truncate">{{ product.title }}</h4>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-4">Satıcı: {{ product.shop?.store_name }}</p>
                                    </div>
                                    <div class="flex justify-between items-center pt-4 border-t border-gray-50 mt-auto">
                                        <p class="text-2xl font-black text-indigo-600">₺{{ getPrice(product) }}</p>
                                        <span class="bg-gray-100 text-gray-900 text-[10px] font-black uppercase px-4 py-2 rounded-xl group-hover:bg-indigo-600 group-hover:text-white transition-colors">İncele</span>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>

                    <div v-if="products.last_page > 1" class="mt-16 flex justify-center gap-2">
                        <Link v-for="link in products.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="px-5 py-3 rounded-xl text-sm font-bold transition-all" :class="link.active ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'"></Link>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>