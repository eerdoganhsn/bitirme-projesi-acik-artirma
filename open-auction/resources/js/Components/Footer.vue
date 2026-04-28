<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="bg-gray-900 text-white pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
                <div class="col-span-1 md:col-span-1">
                    <Link href="/" class="text-3xl font-black text-indigo-500 italic mb-6 block">BidBod.</Link>
                    <p class="text-gray-400 text-sm leading-relaxed font-medium">
                        Türkiye'nin en güvenilir yeni nesil açık artırma ve pazar yeri platformu. Değerli ürünleri keşfet, teklif ver ve kazan.
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-black uppercase tracking-widest text-xs mb-6">Kurumsal</h4>
                    <ul class="space-y-4 text-sm text-gray-400 font-bold">
                        <li><Link href="#" class="hover:text-white transition">Hakkımızda</Link></li>
                        <li><Link href="#" class="hover:text-white transition">Kullanım Koşulları</Link></li>
                        <li><Link href="#" class="hover:text-white transition">KVKK Aydınlatma</Link></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-black uppercase tracking-widest text-xs mb-6">Destek</h4>
                    <ul class="space-y-4 text-sm text-gray-400 font-bold">
                        <li><Link href="#" class="hover:text-white transition">Yardım Merkezi</Link></li>
                        <li><Link href="#" class="hover:text-white transition">Sıkça Sorulanlar</Link></li>
                        <li><Link href="#" class="hover:text-white transition">İletişim</Link></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-black uppercase tracking-widest text-xs mb-6">Bülten</h4>
                    <p class="text-gray-400 text-xs mb-4 font-bold">Fırsatlardan haberdar olmak için abone ol.</p>
                    <div class="flex gap-2">
                        <input type="email" placeholder="E-posta adresi" class="bg-gray-800 border-none rounded-xl text-sm px-4 py-3 w-full focus:ring-2 focus:ring-indigo-500" />
                        <button class="bg-indigo-600 p-3 rounded-xl hover:bg-indigo-500 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="pt-10 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center gap-6">
                <p class="text-gray-500 text-[10px] font-black uppercase tracking-widest">© 2024 BidBod INC. TÜM HAKLARI SAKLIDIR.</p>
                <div class="flex gap-6">
                    <div class="w-8 h-8 bg-gray-800 rounded-lg"></div>
                    <div class="w-8 h-8 bg-gray-800 rounded-lg"></div>
                    <div class="w-8 h-8 bg-gray-800 rounded-lg"></div>
                </div>
            </div>
        </div>
    </footer>
</template>