<script setup>
import { Head, usePage, Link } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue';
import Footer from '@/Components/Footer.vue';

// Backend'den gönderilen veriler
const props = defineProps({
    stats: Object,
    sellerStats: Object,
    myBids: Array,
    myOrders: Array,
    watchlists: Array,
    shopProducts: Array,
    recentOrders: Array,
});

// Sipariş Durumlarını Türkçeleştirme ve Renklendirme Yardımcısı
const getStatusData = (status) => {
    const statuses = {
        'pending': { label: 'Bekliyor', color: 'bg-yellow-100 text-yellow-800' },
        'processing': { label: 'Hazırlanıyor', color: 'bg-blue-100 text-blue-800' },
        'shipped': { label: 'Kargolandı', color: 'bg-indigo-100 text-indigo-800' },
        'delivered': { label: 'Teslim Edildi', color: 'bg-green-100 text-green-800' },
        'cancelled': { label: 'İptal Edildi', color: 'bg-red-100 text-red-800' },
    };
    return statuses[status] || { label: 'Bekliyor', color: 'bg-yellow-100 text-yellow-800' };
};

const getImagePath = (path) => {
    if (!path) return '';
    return path.startsWith('http') ? path : '/storage/' + path;
};

const user = usePage().props.auth.user;
</script>

<template>
    <Head title="Panelim" />

    <div class="bg-gray-50 min-h-screen flex flex-col">
        <Header />

        <div class="flex-grow py-12 pb-24">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- ========================================== -->
                <!-- DURUM 1: KULLANICI BİR SATICIYSA (MAĞAZA)  -->
                <!-- ========================================== -->
                <div v-if="user.is_seller" class="space-y-8">
                    
                    <!-- Satıcı Özet Kutusu -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border-l-4 border-indigo-500">
                        <div class="p-8 text-gray-900 flex flex-col md:flex-row justify-between items-center gap-6">
                            <div>
                                <h3 class="text-2xl font-black text-indigo-600 mb-2 flex items-center gap-2">
                                    Hoş geldin, {{ user.shop?.store_name || user.name }}! 🏪
                                </h3>
                                <p class="text-gray-500 font-medium">Burası senin satıcı panelin. Mağazanın durumu şu an: <span class="font-bold uppercase text-indigo-500">{{ user.shop?.status || 'APPROVED' }}</span></p>
                            </div>
                            <div class="flex gap-3">
                                <Link :href="route('seller.products.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-bold shadow-md transition-colors flex items-center gap-2">
                                    <span>➕</span> Yeni Ürün Ekle
                                </Link>
                                <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-bold transition-colors">
                                    Mağaza Ayarları
                                </button>
                            </div>
                        </div>

                        <div class="px-8 pb-8 mt-4 grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                            <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Aktif Ürünler</p>
                                <p class="text-4xl font-black text-indigo-700">{{ sellerStats?.activeProducts || 0 }}</p>
                            </div>
                            <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Açık Artırmalar</p>
                                <p class="text-4xl font-black text-indigo-700">{{ sellerStats?.activeAuctions || 0 }}</p>
                            </div>
                            <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Bekleyen Siparişler</p>
                                <p class="text-4xl font-black text-indigo-700">{{ sellerStats?.pendingOrders || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Satıcının Son Ürünleri -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <h3 class="text-lg font-black text-gray-800">Son Eklenen Ürünlerim</h3>
                            <Link :href="route('seller.products.index')" class="text-sm font-bold text-indigo-600 hover:underline">Tümünü Gör &rarr;</Link>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div v-if="!shopProducts || shopProducts.length === 0" class="p-8 text-center text-gray-400">Henüz ürün eklenmedi.</div>
                            <div v-for="product in shopProducts" :key="product.id" class="p-6 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-4 text-left">
                                    <img :src="getImagePath(product.cover_image?.image_path)" class="w-16 h-16 rounded-xl object-cover border border-gray-100" />
                                    <div>
                                        <h4 class="font-bold text-gray-900">{{ product.title }}</h4>
                                        <p class="text-xs text-gray-500 mt-1">Eklenme: {{ new Date(product.created_at).toLocaleDateString('tr-TR') }}</p>
                                    </div>
                                </div>
                                <Link :href="route('seller.products.edit', product.id)" class="px-4 py-2 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-lg hover:bg-indigo-100">Düzenle</Link>
                            </div>
                        </div>
                    </div>

                    <!-- Satıcının Son Siparişleri -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-8 text-left">
                        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <h3 class="text-lg font-black text-gray-800">Son Gelen Siparişler</h3>
                            <Link :href="route('seller.orders.index')" class="text-sm font-bold text-indigo-600 hover:underline">Tümünü Gör &rarr;</Link>
                        </div>
                        <div class="p-0">
                            <div v-if="!recentOrders || recentOrders.length === 0" class="p-8 text-center text-gray-400">Henüz sipariş gelmedi.</div>
                            <ul v-else class="divide-y divide-gray-100">
                                <li v-for="order in recentOrders" :key="order.id" class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl bg-gray-100 overflow-hidden border border-gray-200">
                                            <img :src="getImagePath(order.product?.cover_image?.image_path)" class="w-full h-full object-cover"/>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900 line-clamp-1">{{ order.product?.title }}</p>
                                            <p class="text-[11px] text-gray-500 font-medium">
                                                <span class="text-indigo-600 font-bold">{{ order.order?.full_name || order.order?.user?.name }}</span> • {{ new Date(order.created_at).toLocaleDateString('tr-TR') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 text-right">
                                        <p class="text-sm font-black text-gray-900">₺{{ order.price }}</p>
                                        <span :class="['px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest', getStatusData(order.status).color]">
                                            {{ getStatusData(order.status).label }}
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- DURUM 2: KULLANICI NORMAL BİR MÜŞTERİYSE -->
                <!-- ========================================== -->
                <div v-else class="space-y-8 text-left">
                    
                    <!-- Müşteri İstatistikleri -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border-l-4 border-green-500">
                        <div class="p-8 text-gray-900">
                            <h3 class="text-2xl font-black text-green-600 mb-2 flex items-center gap-2">
                                Hoş geldin, {{ user.name }}! 🛍️
                            </h3>
                            <p class="text-gray-500 font-medium text-left">Açık artırmaları takip edebilir ve favori ürünlerine teklif verebilirsin.</p>
                            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                                <div class="bg-green-50 p-6 rounded-2xl border border-green-100">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 text-center">Verilen Teklifler</p>
                                    <p class="text-4xl font-black text-green-700 text-center">{{ stats?.totalBids || 0 }}</p>
                                </div>
                                <div class="bg-green-50 p-6 rounded-2xl border border-green-100">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 text-center">Takip Edilenler</p>
                                    <p class="text-4xl font-black text-green-700 text-center">{{ stats?.watchlistCount || 0 }}</p>
                                </div>
                                <div class="bg-green-50 p-6 rounded-2xl border border-green-100">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 text-center">Siparişlerim</p>
                                    <p class="text-4xl font-black text-green-700 text-center">{{ stats?.ordersCount || 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- İKİ SÜTUNLU LİSTELER (TEKLİFLER VE SİPARİŞLER) -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        
                        <!-- AKTİF TEKLİFLERİM -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden text-left">
                            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                                <h3 class="text-lg font-black text-gray-800">Aktif Tekliflerim</h3>
                                <span class="text-xs font-bold bg-green-100 text-green-700 px-3 py-1 rounded-full">{{ myBids?.length || 0 }} İhale</span>
                            </div>
                            <div class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto">
                                <div v-if="!myBids || myBids.length === 0" class="p-8 text-center text-gray-400">Teklif bulunamadı.</div>
                                <div v-for="bid in myBids" :key="bid.id" class="p-6 flex items-center justify-between hover:bg-gray-50 transition-colors text-left">
                                    <div class="flex items-center gap-4">
                                        <img :src="getImagePath(bid.auction?.product?.cover_image?.image_path)" class="w-16 h-16 rounded-xl object-cover border border-gray-100 shadow-sm" />
                                        <div>
                                            <Link :href="route('product.show', bid.auction.product.id)">
                                                <h4 class="font-bold text-gray-900 text-sm hover:text-indigo-600 hover:underline">{{ bid.auction.product.title }}</h4>
                                            </Link>
                                            <p class="text-xs text-gray-500 mt-1">Senin Teklifin: <strong class="text-green-600">₺{{ bid.amount }}</strong></p>
                                        </div>
                                    </div>
                                    <span v-if="bid.amount >= bid.auction?.current_price" class="text-[9px] font-black uppercase text-green-700 bg-green-100 px-2 py-1 rounded tracking-tighter">👑 Lider</span>
                                    <span v-else class="text-[9px] font-black uppercase text-red-700 bg-red-100 px-2 py-1 rounded tracking-tighter">⚠️ Geçildi</span>
                                </div>
                            </div>
                        </div>

                        <!-- MÜŞTERİ SİPARİŞLERİM -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden text-left">
                            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                                <h3 class="text-lg font-black text-gray-800">Siparişlerim</h3>
                                <span class="text-xs font-bold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full">{{ myOrders?.length || 0 }} Sipariş</span>
                            </div>
                            <div class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto">
                                <div v-if="!myOrders || myOrders.length === 0" class="p-8 text-center text-gray-400">Henüz siparişiniz yok.</div>
                                <div v-for="order in myOrders" :key="order.id" class="p-6">
                                    <!-- Sipariş No & Tarih -->
                                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-dashed border-gray-100">
                                        <div>
                                            <h4 class="font-black text-gray-900 text-sm">{{ order.order_number }}</h4>
                                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">{{ new Date(order.created_at).toLocaleDateString('tr-TR') }}</p>
                                        </div>
                                        <strong class="text-gray-900 font-black text-sm">₺{{ order.total_amount }}</strong>
                                    </div>
                                    <!-- Sipariş Ürünleri ve Durumları -->
                                    <div class="space-y-3">
                                        <div v-for="item in order.items" :key="item.id" class="flex items-center justify-between p-2 rounded-xl bg-gray-50/50 border border-gray-100">
                                            <div class="flex items-center gap-3">
                                                <img :src="getImagePath(item.product?.cover_image?.image_path)" class="w-10 h-10 rounded-lg object-cover border border-white shadow-xs" />
                                                <div class="max-w-[150px]">
                                                    <p class="text-xs font-bold text-gray-900 truncate">{{ item.product?.title }}</p>
                                                    <p class="text-[10px] text-gray-500">₺{{ item.price }} x {{ item.quantity }}</p>
                                                </div>
                                            </div>
                                            <span :class="['px-2 py-1 rounded text-[9px] font-black uppercase tracking-tighter shadow-xs', getStatusData(item.status).color]">
                                                {{ getStatusData(item.status).label }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- İZLEME LİSTESİM (FAVORİLER) -->
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mt-8 text-left">
                        <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <h3 class="text-xl font-black text-gray-900 tracking-tight flex items-center gap-3 text-left">
                                <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>
                                İzleme Listem
                            </h3>
                            <span class="bg-red-100 text-red-700 text-xs font-bold px-3 py-1 rounded-full">{{ watchlists?.length || 0 }} Ürün</span>
                        </div>
                        <div class="p-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-if="!watchlists || watchlists.length === 0" class="col-span-full text-center p-12 text-gray-400 font-medium">Takip edilen ürün bulunamadı.</div>
                            <div v-for="item in watchlists" :key="item.id" class="group flex gap-4 p-4 rounded-2xl border border-gray-100 hover:shadow-lg transition-all bg-white relative">
                                <div class="w-24 h-24 rounded-xl overflow-hidden flex-shrink-0 bg-gray-50 relative">
                                    <img :src="getImagePath(item.product?.cover_image?.image_path)" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                                    <div class="absolute top-2 left-2">
                                        <span v-if="item.product?.listing_type === 'auction'" class="bg-purple-600 text-white text-[9px] font-black px-2 py-0.5 rounded">İHALE</span>
                                        <span v-else class="bg-green-500 text-white text-[9px] font-black px-2 py-0.5 rounded">HEMEN AL</span>
                                    </div>
                                </div>
                                <div class="flex-1 flex flex-col justify-between py-1 text-left">
                                    <h4 class="font-bold text-gray-900 text-sm line-clamp-2">{{ item.product?.title }}</h4>
                                    <p class="text-xs font-black text-indigo-600 mt-2">
                                        ₺{{ item.product?.listing_type === 'auction' ? item.product.auction?.current_price : item.product?.buy_now_price }}
                                    </p>
                                    <Link :href="route('product.show', item.product?.id)" class="text-xs font-black text-indigo-600 hover:underline mt-2">İncele &rarr;</Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <Footer class="mt-auto" />
    </div>
</template>