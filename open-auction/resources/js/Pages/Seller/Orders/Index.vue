<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue'; 

const props = defineProps({
    sellerOrders: Array
});

// Durum güncelleme formu
const statusForm = useForm({
    status: ''
});

// Durumu Türkçeleştirme ve Renklendirme Yardımcısı
const getStatusData = (status) => {
    const statuses = {
        'pending': { label: 'Bekliyor', color: 'bg-yellow-100 text-yellow-800' },
        'processing': { label: 'Hazırlanıyor', color: 'bg-blue-100 text-blue-800' },
        'shipped': { label: 'Kargolandı', color: 'bg-indigo-100 text-indigo-800' },
        'delivered': { label: 'Teslim Edildi', color: 'bg-green-100 text-green-800' },
        'cancelled': { label: 'İptal Edildi', color: 'bg-red-100 text-red-800' },
    };
    return statuses[status] || { label: status, color: 'bg-gray-100 text-gray-800' };
};

const updateStatus = (itemId, newStatus) => {
    statusForm.status = newStatus;
    statusForm.patch(route('seller.orders.update-status', itemId), {
        preserveScroll: true,
        onSuccess: () => alert('Sipariş durumu başarıyla güncellendi!')
    });
};

const getImagePath = (path) => {
    if (!path) return '';
    return path.startsWith('http') ? path : '/storage/' + path;
};
</script>

<template>
    <Head title="Gelen Siparişler" />
    
    <Header />
    
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Başlık ve Geri Dön Butonu -->
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-2 bg-indigo-600 rounded-full"></div>
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight">Gelen Siparişler</h1>
                </div>
                <Link :href="route('dashboard')" class="bg-white text-gray-700 px-6 py-3 rounded-xl border border-gray-200 font-bold hover:bg-gray-50 transition-colors shadow-sm">
                    &larr; Panele Dön
                </Link>
            </div>

            <!-- Sipariş Tablosu -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                <div v-if="!sellerOrders || sellerOrders.length === 0" class="p-20 text-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Henüz Sipariş Yok</h3>
                    <p class="text-gray-500 font-medium text-sm">Mağazanıza henüz bir sipariş gelmemiş.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="p-5 text-xs font-black text-gray-400 uppercase tracking-widest">Sipariş Bilgisi</th>
                                <th class="p-5 text-xs font-black text-gray-400 uppercase tracking-widest min-w-[250px]">Ürün Bilgisi</th>
                                <th class="p-5 text-xs font-black text-gray-400 uppercase tracking-widest">Müşteri / Teslimat</th>
                                <th class="p-5 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Durum</th>
                                <th class="p-5 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Aksiyon</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in sellerOrders" :key="item.id" class="hover:bg-indigo-50/30 transition-colors">
                                
                                <!-- Sipariş No ve Tarih -->
                                <td class="p-5">
                                    <p class="font-bold text-gray-900 text-sm">{{ item.order?.order_number || 'Bilinmiyor' }}</p>
                                    <p class="text-xs text-gray-400 font-medium mt-1">{{ new Date(item.created_at).toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                                </td>

                                <!-- Ürün Görseli ve Adı (RESİM PATLAMASI ÇÖZÜLEN YER) -->
                                <td class="p-5">
                                    <div class="flex items-center gap-4">
                                        <!-- Resmi tutan kutuyu w-14 h-14 ile SIKIŞTIRDIK ve flex-shrink-0 verdik -->
                                        <div class="w-14 h-14 rounded-xl bg-gray-50 border border-gray-100 overflow-hidden flex-shrink-0 flex-none relative">
                                            <img v-if="item.product?.cover_image?.image_path" 
                                                 :src="getImagePath(item.product.cover_image.image_path)" 
                                                 class="absolute inset-0 w-full h-full object-cover"/>
                                            <div v-else class="absolute inset-0 flex items-center justify-center text-[10px] text-gray-300 font-bold">YOK</div>
                                        </div>
                                        <!-- Yazıların olduğu kısım -->
                                        <div class="min-w-0 max-w-[250px]">
                                            <p class="font-bold text-gray-900 text-sm line-clamp-1 truncate" :title="item.product?.title">{{ item.product?.title }}</p>
                                            <p class="text-xs text-indigo-600 font-black mt-1">₺{{ item.price }} <span class="text-gray-400 font-medium">x {{ item.quantity }} Adet</span></p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Müşteri Bilgisi -->
                                <td class="p-5">
                                    <p class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        {{ item.order?.full_name || item.order?.user?.name }}
                                    </p>
                                    <p class="text-xs text-gray-500 font-medium mt-1 truncate max-w-[200px]" :title="item.order?.shipping_address">
                                        {{ item.order?.city || 'Şehir Belirtilmemiş' }}
                                    </p>
                                </td>

                                <!-- Durum Etiketi -->
                                <td class="p-5 text-center">
                                    <span :class="['px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest shadow-sm', getStatusData(item.status || 'pending').color]">
                                        {{ getStatusData(item.status || 'pending').label }}
                                    </span>
                                </td>

                                <!-- Aksiyon (Durum Güncelleme) -->
                                <td class="p-5 text-right">
                                    <select 
                                        @change="updateStatus(item.id, $event.target.value)"
                                        :value="item.status || 'pending'"
                                        class="text-xs font-bold rounded-xl border-gray-200 text-gray-700 focus:ring-indigo-500 focus:border-indigo-500 bg-white py-2 pl-3 pr-8 shadow-sm cursor-pointer hover:bg-gray-50 transition-colors"
                                    >
                                        <option value="pending">⏳ Bekliyor</option>
                                        <option value="processing">📦 Hazırlanıyor</option>
                                        <option value="shipped">🚚 Kargolandı</option>
                                        <option value="delivered">✅ Teslim Edildi</option>
                                        <option value="cancelled">❌ İptal Et</option>
                                    </select>
                                </td>

                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
</template>