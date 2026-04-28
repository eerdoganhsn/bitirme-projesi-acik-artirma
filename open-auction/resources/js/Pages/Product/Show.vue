<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    product: Object,
    relatedProducts: Array,
    comments: Array,
});

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

// Galeride hangi resmin büyük görüneceğini tutan state (GÜVENLİ)
const activeImage = ref(props.product?.images?.[0]?.image_path || null);

// Teklif verme formu (GÜVENLİ)
const bidForm = useForm({
    amount: (props.product?.auction?.current_price || props.product?.auction?.starting_price || 0) + 10,
});

const submitBid = () => {
    if(!props.product?.auction?.id) return;
    bidForm.post(route('auction.bid', props.product.auction.id), {
        preserveScroll: true,
        onSuccess: () => alert('Teklifiniz başarıyla iletildi!')
    });
};
</script>

<template>
    <Head :title="product?.title || 'Ürün Detayı'" />
    <Header />
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
                        
                        <div v-if="product?.listing_type === 'auction'" class="space-y-6">
                            <div class="flex justify-between items-end">
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter mb-1">Şu Anki Teklif</p>
                                    <p class="text-4xl font-black text-gray-900">₺{{ product?.auction?.current_price || product?.auction?.starting_price }}</p>
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