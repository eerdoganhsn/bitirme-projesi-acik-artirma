<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    product: Object
});

// Galeride hangi resmin büyük görüneceğini tutan state
const activeImage = ref(props.product.images[0]?.image_path);

// Teklif verme formu
const bidForm = useForm({
    amount: (props.product.auction?.current_price || 0) + 10, // Varsayılan: Mevcut fiyat + 10tl
});

const submitBid = () => {
    bidForm.post(route('auction.bid', props.product.auction.id), {
        preserveScroll: true,
        onSuccess: () => alert('Teklifiniz başarıyla iletildi!')
    });
};
</script>

<template>
    <Head :title="product.title" />

    <div class="min-h-screen bg-gray-50 pb-20">
        <nav class="bg-white border-b py-4 mb-8">
            <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
                <Link href="/" class="text-indigo-600 font-black italic text-xl">AçıkArtırma</Link>
                <Link href="/" class="text-sm text-gray-500 hover:text-indigo-600">← Market'e Dön</Link>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:gap-x-12">
                
                <div class="space-y-4">
                    <div class="aspect-square rounded-3xl overflow-hidden bg-white border border-gray-100 shadow-sm">
                        <img :src="'/storage/' + activeImage" class="w-full h-full object-cover transition-all duration-500" />
                    </div>
                    
                    <div class="grid grid-cols-5 gap-4">
                        <button v-for="img in product.images" :key="img.id" 
                                @click="activeImage = img.image_path"
                                :class="{'ring-2 ring-indigo-500': activeImage === img.image_path}"
                                class="aspect-square rounded-xl overflow-hidden border border-gray-200 bg-white shadow-sm transition-all">
                            <img :src="'/storage/' + img.image_path" class="w-full h-full object-cover" />
                        </button>
                    </div>
                </div>

                <div class="mt-10 lg:mt-0 space-y-8">
                    <div>
                        <nav class="flex mb-4">
                            <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">
                                {{ product.category.name }}
                            </span>
                        </nav>
                        <h1 class="text-3xl font-black text-gray-900 leading-tight">{{ product.title }}</h1>
                        <p class="text-sm text-gray-400 mt-2">Satıcı: <span class="font-bold text-gray-700">{{ product.shop.store_name }}</span></p>
                    </div>

                    <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm space-y-6">
                        
                        <div v-if="product.listing_type === 'auction'" class="space-y-6">
                            <div class="flex justify-between items-end">
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter mb-1">Şu Anki En Yüksek Teklif</p>
                                    <p class="text-4xl font-black text-gray-900">₺{{ product.auction.current_price }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] text-red-500 font-black uppercase">Kalan Süre</p>
                                    <p class="text-lg font-bold text-gray-800">12s : 45d : 10s</p>
                                </div>
                            </div>

                            <div class="pt-6 border-t border-gray-50">
                                <label class="block text-sm font-bold text-gray-700 mb-3">Teklifini Belirle</label>
                                <div class="flex gap-4">
                                    <input v-model="bidForm.amount" type="number" 
                                           class="flex-1 rounded-2xl border-gray-200 py-4 px-6 focus:ring-indigo-500 focus:border-indigo-500 font-bold text-lg shadow-sm" />
                                    <button @click="submitBid" class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-black hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                                        TEKLİF VER
                                    </button>
                                </div>
                                <p class="mt-3 text-[10px] text-gray-400">En az ₺{{ product.auction.current_price + 1 }} teklif vermelisiniz.</p>
                            </div>
                        </div>

                        <div v-else class="space-y-6">
                            <div>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter mb-1">Hemen Al Fiyatı</p>
                                <p class="text-4xl font-black text-gray-900">₺{{ product.buy_now_price }}</p>
                            </div>
                            <button class="w-full bg-gray-900 text-white py-5 rounded-2xl font-black text-lg hover:bg-indigo-600 transition shadow-xl shadow-gray-200">
                                SEPETE EKLE / SATIN AL
                            </button>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm">
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-4">Ürün Açıklaması</h3>
                        <p class="text-gray-600 leading-relaxed">{{ product.description }}</p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>