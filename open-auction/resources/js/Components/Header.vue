<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

// Menünün açık/kapalı durumunu tutan state
const isProfileMenuOpen = ref(false);

// Menü dışına tıklandığında kapatmak için
const closeMenu = (e) => {
    if (!e.target.closest('#profile-menu-container')) {
        isProfileMenuOpen.value = false;
    }
};

onMounted(() => window.addEventListener('click', closeMenu));
onUnmounted(() => window.removeEventListener('click', closeMenu));
</script>

<template>
    <header class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                
                <Link href="/" class="text-3xl font-black text-indigo-600 italic tracking-tighter">
                    BidBod<span class="text-gray-900">.</span>
                </Link>

                <div class="flex items-center gap-4 sm:gap-6">
                    <Link :href="route('cart.index')" class="relative p-2 group transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gray-700 group-hover:text-indigo-600 group-hover:scale-110 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span v-if="$page.props.cartCount > 0" class="absolute top-0 right-0 bg-indigo-600 text-white text-[10px] font-black w-5 h-5 flex items-center justify-center rounded-full border-2 border-white shadow-sm">
                            {{ $page.props.cartCount }}
                        </span>
                    </Link>

                    <div v-if="$page.props.auth.user" id="profile-menu-container" class="relative">
                        <button 
                            @click="isProfileMenuOpen = !isProfileMenuOpen"
                            class="flex items-center gap-3 bg-gray-50 px-3 py-2 sm:px-4 sm:py-2.5 rounded-2xl border border-gray-100 hover:bg-gray-100 transition-all active:scale-95"
                        >
                            <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center font-black text-white text-xs shadow-lg shadow-indigo-100">
                                {{ $page.props.auth.user.name[0] }}
                            </div>
                            <span class="hidden sm:block text-xs font-black text-gray-700 uppercase tracking-tighter">
                                {{ $page.props.auth.user.name.split(' ')[0] }}
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transition-transform duration-300" :class="{'rotate-180': isProfileMenuOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <transition
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="transform scale-95 opacity-0"
                            enter-to-class="transform scale-100 opacity-100"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="transform scale-100 opacity-100"
                            leave-to-class="transform scale-95 opacity-0"
                        >
                            <div v-if="isProfileMenuOpen" class="absolute right-0 mt-3 w-64 bg-white rounded-[2rem] shadow-2xl border border-gray-100 py-4 overflow-hidden z-50">
    
    <div class="px-6 py-3 border-b border-gray-50 mb-2">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Hoş Geldin</p>
        <p class="text-sm font-black text-gray-900 truncate">{{ $page.props.auth.user.name }}</p>
        <span v-if="$page.props.auth.user.is_seller" class="text-[9px] bg-green-50 text-green-600 px-2 py-0.5 rounded-full font-bold uppercase tracking-tighter">Mağaza Yöneticisi</span>
        <span v-else class="text-[9px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-bold uppercase tracking-tighter">Müşteri</span>
    </div>

    <template v-if="$page.props.auth.user.is_seller">
        <Link :href="route('dashboard')" class="flex items-center gap-3 px-6 py-3 text-sm font-black text-indigo-600 hover:bg-indigo-50 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" /></svg>
            Yönetim Paneli
        </Link>
        <Link href="#" class="flex items-center gap-3 px-6 py-3 text-sm font-bold text-gray-600 hover:bg-gray-50 transition">
            Ürünlerimi Yönet
        </Link>
        <div class="h-px bg-gray-50 my-2 mx-6"></div>
    </template>

    
    
    <Link :href="route('profile.edit')" class="flex items-center gap-3 px-6 py-3 text-sm font-bold text-gray-600 hover:bg-gray-50 transition">
        Hesap Ayarları
    </Link>

    <div class="h-px bg-gray-50 my-2 mx-6"></div>

    <Link :href="route('logout')" method="post" as="button" class="w-full text-left flex items-center gap-3 px-6 py-3 text-sm font-black text-red-500 hover:bg-red-50 transition">
        Güvenli Çıkış
    </Link>
</div>
                        </transition>
                    </div>

                    <div v-else class="flex items-center gap-4">
                        <Link :href="route('login')" class="text-sm font-black text-gray-900 uppercase tracking-widest hover:text-indigo-600 transition">Giriş</Link>
                        <Link :href="route('register')" class="hidden sm:block bg-gray-900 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 transition shadow-lg shadow-gray-200">Kayıt Ol</Link>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>