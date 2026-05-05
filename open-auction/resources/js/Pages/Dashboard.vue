<script setup>
import { Head, usePage, Link } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue';
import Footer from '@/Components/Footer.vue';

// Backend'den (Controller/Route) gönderilen veriler
const props = defineProps({
    stats: Object,
    sellerStats: Object,
    myBids: Array,
    myOrders: Array,
});
const getImagePath = (path) => {
    if (!path) return '';
    // Eğer yol http ile başlıyorsa (Seeder verisi), olduğu gibi döndür.
    // Başlamıyorsa (Kullanıcının yüklediği gerçek resim), başına /storage/ ekle.
    return path.startsWith('http') ? path : '/storage/' + path;
};
// Oturum açmış kullanıcı bilgisini alıyoruz
const user = usePage().props.auth.user;
</script>

<template>
    <Head title="Panelim" />

    <!-- Tüm sayfayı kaplayan ve footer'ı alta iten ana kapsayıcı (flex flex-col) -->
    <div class="bg-gray-50 min-h-screen flex flex-col">
        
        <!-- Üst Menü (Header) -->
        <Header />

        <!-- Orta İçerik Alanı (flex-grow ile boşluğu doldurur) -->
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

                            <!-- HIZLI İŞLEMLER BUTONLARI -->
                            <div class="flex gap-3">
                                <!-- DÜZELTİLDİ: route('seller.products.create') -->
                                <Link :href="route('seller.products.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-bold shadow-md transition-colors flex items-center gap-2">
                                    <span>➕</span> Yeni Ürün Ekle
                                </Link>
                                <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-bold transition-colors">
                                    Mağaza Ayarları
                                </button>
                            </div>
                        </div>

                        <!-- İstatistikler -->
                        <div class="px-8 pb-8 mt-4 grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                            <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100 hover:shadow-md transition">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Aktif Ürünler</p>
                                <p class="text-4xl font-black text-indigo-700">{{ sellerStats?.activeProducts || 0 }}</p>
                            </div>
                            <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100 hover:shadow-md transition">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Açık Artırmalar</p>
                                <p class="text-4xl font-black text-indigo-700">{{ sellerStats?.activeAuctions || 0 }}</p>
                            </div>
                            <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100 hover:shadow-md transition">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Bekleyen Siparişler</p>
                                <p class="text-4xl font-black text-indigo-700">{{ sellerStats?.pendingOrders || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- SATICININ ÜRÜNLERİ TABLOSU -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <h3 class="text-lg font-black text-gray-800">Son Eklenen Ürünlerim</h3>
                            <!-- DÜZELTİLDİ: route('seller.products.index') -->
                            <Link :href="route('seller.products.index')" class="text-sm font-bold text-indigo-600 hover:underline">Tümünü Gör &rarr;</Link>
                        </div>
                        
                        <div class="divide-y divide-gray-100">
                            <div v-if="!shopProducts || shopProducts.length === 0" class="p-8 text-center text-gray-400 font-medium text-sm">
                                Henüz mağazanıza hiç ürün eklemediniz. "Yeni Ürün Ekle" butonunu kullanarak başlayabilirsiniz.
                            </div>

                            <div v-for="product in shopProducts" :key="product.id" class="p-6 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <!-- Ürün Resmi -->
                                    <div class="w-16 h-16 flex-shrink-0">
                                        <img v-if="product.cover_image?.image_path" src="getImagePath(product.cover_image.image_path)" class="w-full h-full rounded-xl object-cover border border-gray-100" />
                                        <div v-else class="w-full h-full rounded-xl bg-gray-100 flex items-center justify-center text-xl">📷</div>
                                    </div>
                                    
                                    <div>
                                        <h4 class="font-bold text-gray-900">{{ product.title }}</h4>
                                        <p class="text-xs text-gray-500 mt-1">Eklenme: {{ new Date(product.created_at).toLocaleDateString('tr-TR') }}</p>
                                    </div>
                                </div>
                                
                                <!-- Aksiyon Butonları (Düzenle) -->
                                <div class="flex gap-2">
                                    <!-- DÜZELTİLDİ: route('seller.products.edit', product.id) -->
                                    <Link :href="route('seller.products.edit', product.id)" class="px-4 py-2 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-lg hover:bg-indigo-100 transition">
                                        Düzenle
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ========================================== -->
                <!-- DURUM 2: KULLANICI NORMAL BİR MÜŞTERİYSE -->
                <!-- ========================================== -->
                <div v-else class="space-y-8">
                    
                    <!-- Müşteri İstatistik Kutuları -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border-l-4 border-green-500">
                        <div class="p-8 text-gray-900">
                            <h3 class="text-2xl font-black text-green-600 mb-2 flex items-center gap-2">
                                Hoş geldin, {{ user.name }}! 🛍️
                            </h3>
                            <p class="text-gray-500 font-medium">Açık artırmaları takip edebilir ve favori ürünlerine teklif verebilirsin.</p>

                            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                                <div class="bg-green-50 p-6 rounded-2xl border border-green-100 hover:shadow-md transition">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Verilen Teklifler</p>
                                    <p class="text-4xl font-black text-green-700">{{ stats?.totalBids || 0 }}</p>
                                </div>
                                <div class="bg-green-50 p-6 rounded-2xl border border-green-100 hover:shadow-md transition">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Takip Edilenler</p>
                                    <p class="text-4xl font-black text-green-700">{{ stats?.watchlistCount || 0 }}</p>
                                </div>
                                <div class="bg-green-50 p-6 rounded-2xl border border-green-100 hover:shadow-md transition">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Siparişlerim</p>
                                    <p class="text-4xl font-black text-green-700">{{ stats?.ordersCount || 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- İKİ SÜTUNLU LİSTELER -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
                        
                        <!-- SOL SÜTUN: AKTİF TEKLİFLERİM -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                                <h3 class="text-lg font-black text-gray-800">Aktif Tekliflerim</h3>
                                <span class="text-xs font-bold bg-green-100 text-green-700 px-3 py-1 rounded-full">{{ myBids?.length || 0 }} İhale</span>
                            </div>
                            
                            <div class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto">
                                <div v-if="!myBids || myBids.length === 0" class="p-8 text-center text-gray-400 font-medium text-sm">
                                    Henüz hiçbir ihaleye katılmadınız.
                                </div>

                                <div v-for="bid in myBids" :key="bid.id" class="p-6 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center gap-4">
                                        <!-- Ürün Resmi -->
                                        <div class="w-16 h-16 flex-shrink-0">
                                            <img v-if="bid.auction?.product?.cover_image?.image_path" 
     :src="bid.auction.product.cover_image.image_path.startsWith('http') ? bid.auction.product.cover_image.image_path : '/storage/' + bid.auction.product.cover_image.image_path" 
     class="w-full h-full rounded-xl object-cover border border-gray-100 shadow-sm" />
                                            <div v-else class="w-full h-full rounded-xl bg-gray-100 flex items-center justify-center text-xl border border-gray-200">📷</div>
                                        </div>
                                        
                                        <div class="overflow-hidden">
                                            <!-- Ürün Adı Linki -->
                                            <Link v-if="bid.auction?.product" :href="route('product.show', bid.auction.product.id)" class="block truncate">
                                                <h4 class="font-bold text-gray-900 text-sm hover:text-indigo-600 hover:underline cursor-pointer transition-colors">
                                                    {{ bid.auction.product.title }}
                                                </h4>
                                            </Link>
                                            <p class="text-xs text-gray-500 mt-1 font-medium">Senin Teklifin: <strong class="text-green-600 font-black">₺{{ bid.amount }}</strong></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Lider/Geçildi Etiketi -->
                                    <div class="text-right flex-shrink-0 ml-4">
                                        <span v-if="bid.amount >= bid.auction?.current_price" class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-widest text-green-700 bg-green-100 px-3 py-1 rounded-lg border border-green-200">
                                            👑 Lider
                                        </span>
                                        <span v-else class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-widest text-red-700 bg-red-100 px-3 py-1 rounded-lg border border-red-200">
                                            ⚠️ Geçildi
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SAĞ SÜTUN: SİPARİŞLERİM VE SATIN ALINAN ÜRÜNLER -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                                <h3 class="text-lg font-black text-gray-800">Siparişlerim</h3>
                                <span class="text-xs font-bold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full">{{ stats?.ordersCount || 0 }} Sipariş</span>
                            </div>
                            
                            <div class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto">
                                <div v-if="!myOrders || myOrders.length === 0" class="p-8 text-center text-gray-400 font-medium text-sm">
                                    Henüz bir siparişiniz bulunmuyor.
                                </div>

                                <div v-for="order in myOrders" :key="order.id" class="p-6 hover:bg-gray-50 transition-colors">
                                    <!-- Sipariş Üst Bilgisi -->
                                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 border-dashed">
                                        <div>
                                            <h4 class="font-black text-gray-900 text-sm">{{ order.order_number || 'Sipariş #' + order.id }}</h4>
                                            <p class="text-[11px] text-gray-400 font-medium mt-0.5">{{ new Date(order.created_at).toLocaleDateString('tr-TR') }}</p>
                                        </div>
                                        
                                        <!-- Sipariş Durum Etiketi -->
                                        <div class="text-right">
                                            <span v-if="order.status === 'completed'" class="text-[10px] font-black uppercase tracking-widest text-green-700 bg-green-100 px-3 py-1 rounded-lg border border-green-200">Teslim Edildi</span>
                                            <span v-else-if="order.status === 'shipping'" class="text-[10px] font-black uppercase tracking-widest text-blue-700 bg-blue-100 px-3 py-1 rounded-lg border border-blue-200">Kargoda</span>
                                            <span v-else class="text-[10px] font-black uppercase tracking-widest text-yellow-700 bg-yellow-100 px-3 py-1 rounded-lg border border-yellow-200">Hazırlanıyor</span>
                                        </div>
                                    </div>

                                    <!-- Sipariş İçindeki Ürünler (Eğer items ilişkisi yüklüyse) -->
                                    <div v-if="order.items && order.items.length > 0" class="space-y-3">
                                        <div v-for="item in order.items" :key="item.id" class="flex items-center gap-3">
                                            <!-- Ürün Resmi -->
                                            <div class="w-10 h-10 flex-shrink-0 rounded-lg overflow-hidden border border-gray-200">
                                                <img v-if="item.product?.cover_image?.image_path" :src="getImagePath(item.product.cover_image.image_path)" class="w-full h-full object-cover" />
                                                <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center text-xs">📷</div>
                                            </div>
                                            <!-- Ürün Adı -->
                                            <div class="overflow-hidden flex-1">
                                                <Link v-if="item.product" :href="route('product.show', item.product.id)" class="block truncate">
                                                    <span class="text-sm font-semibold text-gray-800 hover:text-indigo-600 transition-colors">{{ item.product.title }}</span>
                                                </Link>
                                                <p class="text-xs text-gray-500">Adet: {{ item.quantity }} x ₺{{ item.price }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-xs text-gray-400 italic mb-3">
                                        Ürün detayları yüklenemedi.
                                    </div>

                                    <!-- Sipariş Toplam Tutar -->
                                    <div class="mt-4 pt-3 border-t border-gray-100 flex justify-between items-center">
                                        <span class="text-xs font-bold text-gray-500 uppercase">Sipariş Toplamı:</span>
                                        <strong class="text-gray-900 font-black">₺{{ order.total_amount }}</strong>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- Alt Menü (Footer) mt-auto ile her zaman en altta kalır -->
        <Footer class="mt-auto" />
    </div>
</template>
