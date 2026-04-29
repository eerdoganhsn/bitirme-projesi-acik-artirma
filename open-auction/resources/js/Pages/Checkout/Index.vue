<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import Header from '@/Components/Header.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    cartItems: Array
});

// Form verilerini tanımlıyoruz (Backend'deki CheckoutController@store metodunun beklediği veriler)
const form = useForm({
    full_name: '',
    address: '',
    city: '',
    phone: '',
    // Kredi kartı bilgileri (Ödev olduğu için sadece simülasyon, veritabanına kaydetmeyeceğiz)
    card_no: '',
    card_expiry: '',
    card_cvv: '',
});

const submitOrder = () => {
    // route('checkout.store') rotasına POST isteği atıyoruz
    form.post(route('checkout.store'), {
        onBefore: () => confirm('Siparişi onaylıyor musunuz?'),
        onSuccess: () => {
            // Başarılı olduğunda Controller bizi Dashboard'a atacak, 
            // istersen buraya bir başarı mesajı alert ekleyebilirsin.
            alert('Siparişiniz alındı, sepetiniz boşaltıldı!');
        },
        onError: (errors) => {
            console.log(errors);
            alert('Lütfen tüm alanları eksiksiz doldurun.');
        }
    });
};
</script>

<template>
    <Head title="Güvenli Ödeme" />

    <div class="min-h-screen bg-gray-50 flex flex-col">
        <Header />

        <main class="flex-grow py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4 mb-12">
                    <Link :href="route('cart.index')" class="p-3 bg-white rounded-2xl border border-gray-100 text-gray-400 hover:text-indigo-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </Link>
                    <h1 class="text-4xl font-black text-gray-900 tracking-tighter">Güvenli Ödeme</h1>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                    
                    <div class="lg:col-span-8 space-y-8">
                        <div class="bg-white rounded-[3rem] p-10 border border-gray-100 shadow-sm">
                            <h3 class="text-xl font-black text-gray-900 mb-8">Teslimat Bilgileri</h3>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="col-span-2">
                                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2 block">Ad Soyad</label>
                                    <input v-model="form.full_name" type="text" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-600 font-bold" placeholder="Örn: Ahmet Yılmaz">
                                </div>
                                <div class="col-span-2">
                                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2 block">Adres</label>
                                    <textarea v-model="form.address" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-600 font-bold h-32" placeholder="Sokak, Mahalle, Kapı No..."></textarea>
                                </div>
                                <div>
                                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2 block">Şehir</label>
                                    <input v-model="form.city" type="text" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-600 font-bold" placeholder="Örn: İstanbul">
                                </div>
                                <div>
                                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2 block">Telefon</label>
                                    <input v-model="form.phone" type="text" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-600 font-bold" placeholder="05xx xxx xx xx">
                                </div>
                            </div>
                        </div>

                        <div class="bg-indigo-600 rounded-[3rem] p-10 text-white shadow-2xl shadow-indigo-200">
                            <h3 class="text-xl font-black mb-8 italic">Kart Bilgileri</h3>
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] font-black text-indigo-200 uppercase tracking-widest mb-2 block">Kart Numarası</label>
                                    <input v-model="form.card_no" type="text" class="w-full bg-white/10 border-none rounded-2xl p-5 text-white placeholder-indigo-300 font-black text-xl tracking-widest focus:ring-2 focus:ring-white" placeholder="**** **** **** ****">
                                </div>
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label class="text-[10px] font-black text-indigo-200 uppercase tracking-widest mb-2 block">S.K.T</label>
                                        <input v-model="form.card_expiry" type="text" class="w-full bg-white/10 border-none rounded-2xl p-4 font-bold" placeholder="AA/YY">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black text-indigo-200 uppercase tracking-widest mb-2 block">CVV</label>
                                        <input v-model="form.card_cvv" type="text" class="w-full bg-white/10 border-none rounded-2xl p-4 font-bold" placeholder="***">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4">
                        <div class="bg-white rounded-[3rem] p-10 border border-gray-100 shadow-xl shadow-indigo-100/30 sticky top-28">
                            <h2 class="text-2xl font-black text-gray-900 mb-8 uppercase italic tracking-tighter">Siparişin<span class="text-indigo-600">.</span></h2>
                            
                            <div class="space-y-4 mb-8 max-h-60 overflow-y-auto pr-2">
                                <div v-for="item in cartItems" :key="item.id" class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-gray-50 rounded-xl overflow-hidden flex-shrink-0">
                                        <img :src="'/storage/' + item.product.cover_image?.image_path" class="w-full h-full object-cover" />
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs font-black text-gray-900 truncate w-32">{{ item.product.title }}</p>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ item.quantity }} Adet</p>
                                    </div>
                                    <p class="text-sm font-black text-gray-900">₺{{ item.product.buy_now_price * item.quantity }}</p>
                                </div>
                            </div>

                            <div class="pt-6 border-t border-gray-50 space-y-4 mb-8">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="font-bold text-gray-400 uppercase tracking-widest">Kargo</span>
                                    <span class="font-black text-green-500">ÜCRETSİZ</span>
                                </div>
                                <div class="flex justify-between items-end">
                                    <span class="text-sm font-black text-gray-900 uppercase tracking-widest">Genel Toplam</span>
                                    <span class="text-4xl font-black text-indigo-600 tracking-tighter">₺{{ totalPrice }}</span>
                                </div>
                            </div>

                            <button 
                                @click="submitOrder" 
                                :disabled="form.processing" 
                                class="w-full bg-indigo-600 text-white py-6 rounded-3xl font-black text-lg hover:bg-gray-900 transition-all shadow-xl shadow-indigo-100 active:scale-95 disabled:opacity-50"
                            >
                                <span v-if="form.processing">İŞLENİYOR...</span>
                                <span v-else>SİPARİŞİ TAMAMLA</span>
                            </button>

                            <!-- Hata mesajlarını göstermek istersen (Örn: Telefon zorunlu) -->
                            <div v-if="form.errors.phone" class="text-red-500 text-xs mt-2">{{ form.errors.phone }}</div>

                            <p class="text-center mt-6 text-[10px] text-gray-400 font-bold uppercase tracking-widest flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                Güvenli 3D Secure Ödeme
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>