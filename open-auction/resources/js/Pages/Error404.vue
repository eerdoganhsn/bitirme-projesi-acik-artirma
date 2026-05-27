<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Sayfa Bulunamadı - 404" />

    <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute top-[20%] right-[-10%] w-96 h-96 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-[-20%] left-[20%] w-96 h-96 bg-pink-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>

        <div class="text-center z-10">
            <h1 class="text-[150px] font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 leading-none tracking-tighter drop-shadow-sm select-none">
                404
            </h1>
            
            <div class="mt-4">
                <h3 class="text-3xl font-black text-gray-900 tracking-tight">Olamaz! Kaybolmuş gibisin.</h3>
                <p class="text-gray-500 mt-4 font-medium max-w-md mx-auto">
                    Aradığın açık artırma ürünü satılmış, sayfa kaldırılmış veya URL'yi yanlış yazmış olabilirsin.
                </p>
            </div>

            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <Link :href="route('home')" class="w-full sm:w-auto px-8 py-4 bg-indigo-600 text-white rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-gray-900 transition-all shadow-xl shadow-indigo-200 hover:-translate-y-1">
                    Ana Sayfaya Dön
                </Link>
                <button @click="window.history.back()" class="w-full sm:w-auto px-8 py-4 bg-white text-gray-700 border border-gray-200 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-gray-50 transition-all">
                    Geri Git
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Arka plan animasyonu için özel CSS */
.animate-blob {
    animation: blob 7s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
.animation-delay-4000 {
    animation-delay: 4s;
}
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
</style>