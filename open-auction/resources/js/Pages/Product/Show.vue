<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue';
import Footer from '@/Components/Footer.vue';
import Countdown from '@/Components/Countdown.vue';

const props = defineProps({
    product: Object,
    relatedProducts: Array,
    comments: Array,
    auth: Object, // Auth bilgisine erişmek için ekledik
});

// --- YARDIMCI FONKSİYONLAR ---

const maskName = (name) => {
    if (!name) return 'Gizli Kullanıcı';
    return name.split(' ').map(word => word.charAt(0) + '***').join(' ');
};

const formatTime = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('tr-TR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
};

// Galeride hangi resmin büyük görüneceğini tutan state
const activeImage = ref(props.product?.images?.[0]?.image_path || null);

// --- FORMLAR VE MANTIK ---

// Yorum Formu
const commentForm = useForm({
    product_id: props.product?.id,
    content: '',
});

const submitComment = () => {
    commentForm.post(route('comments.store'), {
        preserveScroll: true,
        onSuccess: () => commentForm.reset('content'),
    });
};

// Sepete Ekleme Formu (Hemen Al için)
const addToCartForm = useForm({
    product_id: props.product?.id
});

const addToCart = () => {
    addToCartForm.post(route('cart.store'), {
        onSuccess: () => alert('Ürün sepetinize eklendi!')
    });
};

// --- TEKLİF VERME MANTIĞI (GÜNCELLENDİ) ---

// Minimum teklif miktarını hesapla (Mevcut fiyat + 10)
const minBidAmount = computed(() => {
    if (!props.product?.auction) return 0;
    return Number(props.product.auction.current_price) + 10;
});

// Teklif Formu (Tek bir isimde birleştirdik: bidForm)
const bidForm = useForm({
    amount: minBidAmount.value,
});

// Hızlı Teklif Artırma Fonksiyonu
const quickBid = (extraAmount) => {
    bidForm.amount = Number(bidForm.amount) + extraAmount;
};

// Teklifi Gönder
const placeBid = () => {
    if (!props.product.auction) return;
    
    // Kullanıcı giriş yapmamışsa uyar veya yönlendir
    if (!props.auth?.user) {
        alert('Teklif verebilmek için giriş yapmalısınız.');
        return;
    }

    bidForm.post(route('auctions.bid', props.product.auction.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Başarılı olursa input miktarını yeni minimuma güncelle
            bidForm.amount = minBidAmount.value;
        },
    });
};
</script>

<template>
    <Head :title="product?.title || 'Ürün Detayı'" />
    <Header />
    <div class="min-h-screen bg-gray-50 pb-20">
        

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:gap-x-12">
                
                <div class="space-y-4">
                    <div class="aspect-square rounded-3xl overflow-hidden bg-white border border-gray-100 shadow-sm flex items-center justify-center">
                        <img v-if="activeImage" :src="'/storage/' + activeImage" class="w-full h-full object-cover transition-all duration-500" />
                        <span v-else class="text-gray-300 font-bold tracking-widest uppercase">Görsel Yok</span>
                    </div>
                    
                    <div v-if="product?.images?.length > 0" class="grid grid-cols-5 gap-4">
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
                                {{ product?.category?.name || 'Kategori Yok' }}
                            </span>
                        </nav>
                        <h1 class="text-3xl font-black text-gray-900 leading-tight">{{ product?.title }}</h1>
                        <p class="text-sm text-gray-400 mt-2">Satıcı: <span class="font-bold text-gray-700">{{ product?.shop?.store_name || 'Bilinmeyen Satıcı' }}</span></p>
                    </div>

                    <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm space-y-6">
                        
                        <!-- Açık Artırma Modu -->
                        <div v-if="product.listing_type === 'auction'" class="space-y-8">
                            <div class="flex justify-between items-end">
                                <div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Güncel Teklif</p>
                                    <p class="text-5xl font-black text-gray-900 tracking-tighter">₺{{ product.auction?.current_price }}</p>
                                </div>
                                
                                <div class="text-right flex flex-col items-end">
                                    <p class="text-[10px] font-black text-red-500 uppercase tracking-widest mb-2">Kalan Zaman</p>
                                    <!-- SAYAÇ BİLEŞENİ BURAYA GELİYOR -->
                                    <Countdown v-if="product.auction?.end_time" :endTime="product.auction.end_time" />
                                </div>
                            </div>

                            <!-- Teklif Verme Formu -->
                            <div v-if="product.listing_type === 'auction'">
                                
                                <!-- DURUM 1: İHALE AKTİF VE SÜRE DEVAM EDİYOR -->
                                <div v-if="product.auction?.status === 'active' && new Date(product.auction?.end_time) > new Date()">
                                    <p class="text-xs font-bold text-gray-400 mb-3 uppercase tracking-widest">Senin Teklifin</p>
                                    
                                    <div class="flex gap-4">
                                        <input 
                                            v-model="bidForm.amount" 
                                            type="number" 
                                            :min="minBidAmount"
                                            class="flex-1 bg-white border-none rounded-2xl p-5 font-black text-xl shadow-sm focus:ring-2 focus:ring-indigo-600"
                                            placeholder="0.00"
                                        >
                                        <button 
                                            @click="placeBid" 
                                            :disabled="bidForm.processing" 
                                            class="bg-indigo-600 text-white px-10 py-5 rounded-2xl font-black hover:bg-gray-900 transition-all shadow-xl shadow-indigo-100 uppercase tracking-widest text-xs"
                                        >
                                            {{ bidForm.processing ? 'İşleniyor...' : 'Teklif Ver' }}
                                        </button>
                                    </div>
                                    
                                    <!-- Hata Mesajı Gösterimi (Opsiyonel) -->
                                    <p v-if="bidForm.errors.amount" class="text-red-500 text-xs mt-2 font-bold">{{ bidForm.errors.amount }}</p>
                                </div>

                                <!-- DURUM 2: İHALE SONA ERDİ -->
                                <div v-else class="bg-gray-100 border border-gray-200 rounded-2xl p-6 text-center">
                                    <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-200 rounded-full mb-3 text-gray-500">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    </div>
                                    <h3 class="text-gray-900 font-black uppercase tracking-tight">Bu İhale Sona Erdi</h3>
                                    <p class="text-gray-500 text-sm mt-1 font-medium">Artık yeni teklif kabul edilmemektedir.</p>
                                    
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <span class="text-xs text-gray-400 uppercase font-bold">Kazanan Teklif</span>
                                        <p class="text-2xl font-black text-indigo-600">₺{{ product.auction?.current_price }}</p>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div v-else class="space-y-6">
                            <div>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter mb-1">Hemen Al Fiyatı</p>
                                <p class="text-4xl font-black text-gray-900">₺{{ product?.buy_now_price }}</p>
                            </div>
                            <button 
                                @click="addToCart"
                                :disabled="addToCartForm.processing"
                                class="w-full bg-gray-900 text-white py-5 rounded-2xl font-black text-lg hover:bg-indigo-600 transition shadow-xl shadow-gray-200 disabled:opacity-50"
                            >
                                <span v-if="addToCartForm.processing">EKLENİYOR...</span>
                                <span v-else>SEPETE EKLE / SATIN AL</span>
                            </button>
                        </div>
                    </div>
                    <!-- TEKLİF GEÇMİŞİ BÖLÜMÜ -->
                    <div class="mt-10 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" v-if="product.listing_type === 'auction' && product.auction">
                        
                        <div class="bg-gray-50 border-b border-gray-100 px-6 py-4 flex justify-between items-center">
                            <h3 class="text-lg font-black text-gray-900 tracking-tight flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Teklif Geçmişi
                            </h3>
                            <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-3 py-1 rounded-full">
                                {{ product.auction.bids?.length || 0 }} Teklif
                            </span>
                        </div>

                        <div class="p-0">
                            <!-- Eğer teklif varsa listele -->
                            <ul v-if="product.auction.bids && product.auction.bids.length > 0" class="divide-y divide-gray-100 max-h-72 overflow-y-auto">
                                <li v-for="(bid, index) in product.auction.bids" :key="bid.id" class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                    
                                    <div class="flex items-center gap-3">
                                        <!-- En yüksek teklif ise ufak bir taç/kupa ikonu koyalım -->
                                        <div v-if="index === 0" class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 shadow-sm">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                        </div>
                                        <!-- Diğer teklifler için standart ikon -->
                                        <div v-else class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>

                                        <div>
                                            <p class="text-sm font-bold text-gray-900">{{ maskName(bid.user?.name) }}</p>
                                            <p class="text-[11px] text-gray-400 font-medium">{{ formatTime(bid.created_at) }}</p>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <p :class="['text-base font-black', index === 0 ? 'text-green-600' : 'text-gray-900']">
                                            ₺{{ bid.amount }}
                                        </p>
                                    </div>

                                </li>
                            </ul>

                            <!-- Eğer hiç teklif yoksa -->
                            <div v-else class="px-6 py-12 text-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <h4 class="text-gray-900 font-bold text-sm">Henüz Teklif Yok</h4>
                                <p class="text-gray-400 text-xs mt-1">İlk teklifi veren sen ol ve öne geç!</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm">
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-4">Ürün Açıklaması</h3>
                        <p class="text-gray-600 leading-relaxed">{{ product?.description }}</p>
                    </div>

                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-24 border-t border-gray-100 pt-16 pb-20">
            <div v-if="relatedProducts?.length > 0" class="mb-24">
                <div class="flex items-center gap-3 mb-10">
                    <div class="h-8 w-2 bg-indigo-600 rounded-full"></div>
                    <h3 class="text-3xl font-black text-gray-900 tracking-tight">Bu Kategorideki Diğer Ürünler</h3>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div v-for="related in relatedProducts" :key="related.id">
                        <Link :href="route('product.show', related.id)" class="group block bg-white rounded-[2.5rem] p-4 border border-gray-100 hover:shadow-2xl transition-all duration-500">
                            <div class="h-52 rounded-[2rem] overflow-hidden mb-6">
                                <img v-if="related.cover_image" :src="'/storage/' + related.cover_image.image_path" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                                <div v-else class="h-full bg-gray-50 flex items-center justify-center text-gray-400 font-bold text-[10px] uppercase tracking-widest text-center">Görsel Yok</div>
                            </div>
                            <div class="px-2 pb-2 text-center">
                                <h4 class="text-lg font-bold text-gray-900 truncate">{{ related.title }}</h4>
                                <p class="text-xl font-black text-indigo-600 mt-2">₺{{ related.buy_now_price || related.starting_price }}</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-16">
                <div class="lg:col-span-1">
                    <h3 class="text-2xl font-black text-gray-900 mb-6">Deneyimini Paylaş</h3>
                    <form @submit.prevent="submitComment" class="space-y-4">
                        <textarea v-model="commentForm.content" placeholder="Yorumunuzu yazın..." class="w-full bg-gray-50 border-none rounded-3xl p-5 text-sm focus:ring-2 focus:ring-indigo-600 min-h-[140px]" required></textarea>
                        <button type="submit" :disabled="commentForm.processing" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black text-sm hover:bg-black transition-all">
                            Yorumu Gönder
                        </button>
                    </form>
                </div>

                <div class="lg:col-span-2">
                    <h3 class="text-2xl font-black text-gray-900 mb-10">Değerlendirmeler ({{ comments?.length || 0 }})</h3>
                    <div v-if="!comments || comments.length === 0" class="text-center py-20 bg-gray-50 rounded-[3rem] border-2 border-dashed">
                        <p class="text-gray-400 italic">Henüz yorum yapılmamış.</p>
                    </div>
                    <div v-else class="space-y-6">
                        <div v-for="comment in comments" :key="comment.id" class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center font-black text-indigo-600">
                                    {{ comment.user?.name?.charAt(0) || 'U' }}
                                </div>
                                <h5 class="font-bold text-gray-900">{{ comment.user?.name || 'Anonim' }}</h5>
                            </div>
                            <p class="text-gray-600 text-sm leading-relaxed">{{ comment.content }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Footer />
    </div>
</template>