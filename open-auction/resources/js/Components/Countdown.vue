<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    endTime: {
        type: String,
        required: true
    }
});

const timeLeft = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 });
const isEnded = ref(false);
let timer;

const calculateTimeLeft = () => {
    // Laravel'den gelen tarihi JavaScript objesine çeviriyoruz
    const end = new Date(props.endTime).getTime();
    const now = new Date().getTime();
    const difference = end - now;

    if (difference > 0) {
        timeLeft.value = {
            days: Math.floor(difference / (1000 * 60 * 60 * 24)),
            hours: Math.floor((difference / (1000 * 60 * 60)) % 24),
            minutes: Math.floor((difference / 1000 / 60) % 60),
            seconds: Math.floor((difference / 1000) % 60)
        };
    } else {
        isEnded.value = true;
        clearInterval(timer);
    }
};

onMounted(() => {
    calculateTimeLeft(); // Sayfa yüklenir yüklenmez hesapla
    timer = setInterval(calculateTimeLeft, 1000); // Her saniye başı güncelle
});

onUnmounted(() => {
    clearInterval(timer); // Bileşenden çıkıldığında belleği temizle
});
</script>

<template>
    <div v-if="!isEnded" class="flex gap-2 text-center">
        <div class="bg-red-50 text-red-600 px-3 py-2 rounded-xl border border-red-100 min-w-[50px]">
            <p class="text-xl font-black">{{ timeLeft.days }}</p>
            <p class="text-[8px] uppercase tracking-widest font-bold">Gün</p>
        </div>
        <div class="bg-red-50 text-red-600 px-3 py-2 rounded-xl border border-red-100 min-w-[50px]">
            <p class="text-xl font-black">{{ timeLeft.hours }}</p>
            <p class="text-[8px] uppercase tracking-widest font-bold">Saat</p>
        </div>
        <div class="bg-red-50 text-red-600 px-3 py-2 rounded-xl border border-red-100 min-w-[50px]">
            <p class="text-xl font-black">{{ timeLeft.minutes }}</p>
            <p class="text-[8px] uppercase tracking-widest font-bold">Dk</p>
        </div>
        <div class="bg-red-50 text-red-600 px-3 py-2 rounded-xl border border-red-100 min-w-[50px] animate-pulse">
            <p class="text-xl font-black">{{ timeLeft.seconds }}</p>
            <p class="text-[8px] uppercase tracking-widest font-bold">Sn</p>
        </div>
    </div>
    <div v-else class="bg-gray-900 text-white px-6 py-3 rounded-xl text-center">
        <p class="font-black tracking-widest uppercase text-sm">İHALE SONA ERDİ</p>
    </div>
</template>