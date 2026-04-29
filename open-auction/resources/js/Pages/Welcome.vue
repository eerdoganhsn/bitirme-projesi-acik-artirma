<script setup>
import { Head, Link } from "@inertiajs/vue3";
import Header from '@/Components/Header.vue';
import Footer from '@/Components/Footer.vue';
import CardCountdown from '@/Components/CardCountdown.vue'; // İsmi 'CardCountdown' olarak düzeltildi

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    categories: Array,
    auctions: Array,
    products: Array,
    finishedAuctions: Array, // Biten ihaleler için eklendi
});
</script>

<template>
    <Head title="Açık Artırma Pazaryeri" />
    <Header />
    
    <div class="min-h-screen bg-gray-50">
        
        <!-- HERO BÖLÜMÜ -->
        <div class="bg-indigo-700 py-24 text-center text-white relative overflow-hidden">
            <div class="relative z-10 px-4">
                <h2 class="text-4xl font-black tracking-tight sm:text-6xl mb-6">
                    Aradığın Ürüne Kendi Fiyatını Belirle
                </h2>
                <p class="text-lg text-indigo-100 max-w-2xl mx-auto font-medium">
                    Yüzlerce mağazadan binlerce antika, teknoloji ve koleksiyon
                    ürünü seni bekliyor. Hemen teklif ver!
                </p>
            </div>
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-600 rounded-full blur-3xl opacity-50"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-blue-600 rounded-full blur-3xl opacity-30"></div>
        </div>

        <!-- KATEGORİLER -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12">
            <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-6">
                Kategoriler
            </h3>
            <div class="flex space-x-4 overflow-x-auto pb-6 scrollbar-hide">
                <Link
                    v-for="category in categories"
                    :key="category.id"
                    :href="route('category.show', category.id)"
                    class="px-8 py-3.5 bg-white rounded-2xl shadow-sm border border-gray-100 text-gray-700 font-bold whitespace-nowrap hover:bg-indigo-600 hover:text-white cursor-pointer transition-all duration-300 shadow-indigo-100/50 hover:shadow-xl hover:-translate-y-1 block"
                >
                    {{ category.name }}
                </Link>
            </div>
        </div>

        <!-- ÖNE ÇIKAN AÇIK ARTIRMALAR (AKTİF İHALELER) -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16">
            <div class="flex items-center gap-3 mb-10">
                <div class="h-10 w-2 bg-indigo-600 rounded-full"></div>
                <h3 class="text-3xl font-black text-gray-900 tracking-tight">
                    Öne Çıkan Açık Artırmalar
                </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="auction in auctions" :key="auction.id">
                    <div
                        v-if="auction && auction.product"
                        class="bg-white rounded-[2rem] shadow-sm overflow-hidden hover:shadow-2xl transition-all duration-500 border border-gray-100 group"
                    >
                        <div class="h-60 overflow-hidden relative">
                            <img
                                v-if="auction.product.cover_image"
                                :src="'/storage/' + auction.product.cover_image.image_path"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                            />
                            <div
                                v-else
                                class="h-full w-full bg-gray-100 flex items-center justify-center text-gray-400 font-bold text-xs uppercase tracking-widest"
                            >
                                Görsel Yok
                            </div>

                            <!-- DÜZELTİLEN SAYAÇ KISMI -->
                            <div class="absolute bottom-4 left-4 z-10" v-if="auction.end_time">
                                <CardCountdown :endTime="auction.end_time" />
                            </div>
                        </div>

                        <div class="p-7">
                            <div class="flex justify-between items-center mb-3">
                                <p class="text-[10px] text-indigo-600 font-black uppercase tracking-[0.15em]">
                                    {{ auction.product.category?.name || "Genel" }}
                                </p>
                                <span class="bg-purple-100 text-purple-700 text-[9px] px-2 py-1 rounded-md font-black uppercase">İHALE</span>
                            </div>
                            <h4 class="text-lg font-bold text-gray-900 truncate mb-1">
                                {{ auction.product.title }}
                            </h4>
                            <p class="text-xs text-gray-400 mb-6 font-medium">
                                Satıcı: {{ auction.product.shop?.store_name || "Mağaza Bilgisi Yok" }}
                            </p>

                            <div class="flex justify-between items-center border-t border-gray-50 pt-6">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-black tracking-tighter mb-1">
                                        En Yüksek Teklif
                                    </p>
                                    <p class="text-2xl font-black text-gray-900 tracking-tight">
                                        ₺{{ auction.current_price }}
                                    </p>
                                </div>
                                <Link
                                    :href="route('product.show', auction.product.id)"
                                    class="bg-gray-900 text-white px-6 py-3 rounded-2xl text-[10px] font-black hover:bg-indigo-600 transition-all shadow-lg active:scale-95 uppercase"
                                >
                                    Teklif Ver
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- HEMEN ALINABİLİR ÜRÜNLER -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-24 pb-12">
            <div class="flex items-center gap-3 mb-10">
                <div class="h-10 w-2 bg-green-500 rounded-full"></div>
                <h3 class="text-3xl font-black text-gray-900 tracking-tight">
                    Hemen Alınabilir Ürünler
                </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="product in products" :key="'prod-' + product.id">
                    <div
                        v-if="product"
                        class="bg-white rounded-[2rem] shadow-sm overflow-hidden hover:shadow-2xl transition-all duration-500 border border-gray-100 group"
                    >
                        <div class="h-60 overflow-hidden relative">
                            <img
                                v-if="product.cover_image"
                                :src="'/storage/' + product.cover_image.image_path"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                            />
                            <div
                                v-else
                                class="h-full w-full bg-gray-100 flex items-center justify-center text-gray-400 font-bold text-xs uppercase tracking-widest"
                            >
                                Görsel Yok
                            </div>
                        </div>

                        <div class="p-7">
                            <p class="text-[10px] text-green-600 font-black uppercase tracking-[0.15em] mb-3">
                                {{ product.category?.name || "Genel" }}
                            </p>
                            <h4 class="text-lg font-bold text-gray-900 truncate mb-1">
                                {{ product.title }}
                            </h4>
                            <p class="text-xs text-gray-400 mb-6 font-medium">
                                Mağaza: {{ product.shop?.store_name || "Bilinmiyor" }}
                            </p>

                            <div class="flex justify-between items-center border-t border-gray-50 pt-6">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-black tracking-tighter mb-1">
                                        Fiyat
                                    </p>
                                    <p class="text-2xl font-black text-gray-900 tracking-tight">
                                        ₺{{ product.buy_now_price }}
                                    </p>
                                </div>
                                <Link
                                    :href="route('product.show', product.id)"
                                    class="bg-indigo-600 text-white px-6 py-3 rounded-2xl text-[10px] font-black hover:bg-green-600 transition-all shadow-lg active:scale-95 uppercase"
                                >
                                    Satın Al
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SONA EREN İHALELER (YENİ BÖLÜM) -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 pb-24">
            <div class="flex items-center gap-3 mb-10">
                <div class="h-10 w-2 bg-red-500 rounded-full"></div>
                <h3 class="text-3xl font-black text-gray-900 tracking-tight">
                    Sona Eren İhaleler
                </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="auction in finishedAuctions" :key="'fin-' + auction.id">
                    <div v-if="auction && auction.product" class="bg-gray-50 rounded-[2rem] shadow-sm overflow-hidden border border-gray-200 opacity-75 grayscale hover:grayscale-0 transition-all duration-500">
                        <div class="h-60 overflow-hidden relative">
                            <img v-if="auction.product.cover_image" :src="'/storage/' + auction.product.cover_image.image_path" class="w-full h-full object-cover" />
                            <div v-else class="h-full w-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold text-xs uppercase tracking-widest">Görsel Yok</div>
                            
                            <div class="absolute bottom-4 left-4 z-10 bg-gray-900 text-white px-3 py-1.5 rounded-full text-xs font-bold tracking-widest shadow-lg">
                                SÜRESİ DOLDU
                            </div>
                        </div>

                        <div class="p-7">
                            <h4 class="text-lg font-bold text-gray-900 truncate mb-1 line-through">{{ auction.product.title }}</h4>
                            <div class="flex justify-between items-center border-t border-gray-200 pt-6 mt-4">
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase font-black tracking-tighter mb-1">Kapanış Fiyatı</p>
                                    <p class="text-xl font-black text-gray-900 tracking-tight">₺{{ auction.current_price }}</p>
                                </div>
                                <Link :href="route('product.show', auction.product.id)" class="bg-gray-300 text-gray-700 px-6 py-3 rounded-2xl text-[10px] font-black hover:bg-gray-400 transition-all uppercase">
                                    İncele
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Footer />
    </div>
</template>

<style scoped>
/* Kategori kaydırma çubuğunu gizle */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>