<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import Header from '@/Components/Header.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    cartItems: Array
});

const totalPrice = computed(() => {
    return props.cartItems.reduce((total, item) => {
        const price = item.product.buy_now_price || 0;
        return total + (price * item.quantity);
    }, 0);
});

const deleteForm = useForm({});
const removeItem = (id) => {
    if (confirm('Sepetten çıkarmak istiyor musunuz?')) {
        deleteForm.delete(route('cart.destroy', id));
    }
};
</script>

<template>
    <Head title="Sepetim" />

    <div class="min-h-screen bg-gray-50 flex flex-col">
        <Header />

        <main class="flex-grow py-12 sm:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="flex items-center gap-4 mb-12">
                    <div class="h-10 w-2 bg-indigo-600 rounded-full"></div>
                    <h1 class="text-4xl font-black text-gray-900 tracking-tighter">Sepetim</h1>
                </div>

                <div v-if="cartItems.length > 0" class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
                    
                    <div class="lg:col-span-8 space-y-6">
                        <div v-for="item in cartItems" :key="item.id" 
                             class="bg-white rounded-[2.5rem] p-6 sm:p-8 border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center gap-8 transition-all hover:shadow-xl hover:shadow-indigo-100/30">
                            
                            <div class="w-32 h-32 rounded-3xl overflow-hidden bg-gray-50 border border-gray-100 flex-shrink-0">
                                <img v-if="item.product.cover_image" :src="'/storage/' + item.product.cover_image.image_path" class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center text-[10px] font-black text-gray-300 uppercase tracking-widest">Resim Yok</div>
                            </div>

                            <div class="flex-1 text-center sm:text-left">
                                <h3 class="text-xl font-black text-gray-900 mb-1">{{ item.product.title }}</h3>
                                <p class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-4">{{ item.product.shop?.store_name }}</p>
                                <div class="flex items-center justify-center sm:justify-start gap-6">
                                    <span class="text-2xl font-black text-gray-900">₺{{ item.product.buy_now_price }}</span>
                                    <span class="bg-gray-100 px-4 py-1.5 rounded-xl text-xs font-black text-gray-500 uppercase tracking-tighter">Adet: {{ item.quantity }}</span>
                                </div>
                            </div>

                            <button @click="removeItem(item.id)" class="p-5 bg-red-50 text-red-500 rounded-2xl hover:bg-red-500 hover:text-white transition-all shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="lg:col-span-4">
                        <div class="bg-white rounded-[3rem] p-10 border border-gray-100 shadow-2xl shadow-indigo-100/50 sticky top-28">
                            <h2 class="text-2xl font-black text-gray-900 mb-8 tracking-tight">Sipariş Özeti</h2>
                            
                            <div class="space-y-5 mb-10">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="font-bold text-gray-400 uppercase tracking-widest">Ara Toplam</span>
                                    <span class="font-black text-gray-900">₺{{ totalPrice }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="font-bold text-gray-400 uppercase tracking-widest">Kargo</span>
                                    <span class="font-black text-green-500 bg-green-50 px-3 py-1 rounded-full text-[10px]">ÜCRETSİZ</span>
                                </div>
                                <div class="pt-5 border-t border-gray-50 flex justify-between items-end">
                                    <span class="font-black text-gray-900 uppercase text-xs tracking-widest">Toplam</span>
                                    <span class="text-4xl font-black text-indigo-600 tracking-tighter">₺{{ totalPrice }}</span>
                                </div>
                            </div>

                            <Link 
                                :href="route('checkout.index')" 
                                class="w-full bg-indigo-600 text-white py-6 rounded-3xl font-black text-lg hover:bg-gray-900 transition-all shadow-xl shadow-indigo-100 flex items-center justify-center"
                            >
                                ÖDEMEYE GEÇ
                            </Link>

                            <p class="text-center mt-6 text-[10px] text-gray-400 font-bold uppercase tracking-widest flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                256-bit Güvenli Ödeme
                            </p>
                        </div>
                    </div>

                </div>

                <div v-else class="bg-white rounded-[4rem] border border-gray-100 py-32 text-center shadow-sm">
                    <div class="text-7xl mb-8">🛒</div>
                    <h2 class="text-3xl font-black text-gray-900 mb-4">Sepetin Bomboş!</h2>
                    <Link href="/" class="inline-block bg-indigo-600 text-white px-12 py-5 rounded-3xl font-black hover:bg-gray-900 transition-all">KEŞFETMEYE BAŞLA</Link>
                </div>

            </div>
        </main>

        <Footer />
    </div>
</template>