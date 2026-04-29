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

// Sayıları iki haneli yapmak için yardımcı fonksiyon (Örn: 5 yerine 05)
const pad = (num) => num.toString().padStart(2, '0');

onMounted(() => {
    calculateTimeLeft();
    timer = setInterval(calculateTimeLeft, 1000);
});

onUnmounted(() => {
    clearInterval(timer);
});
</script>

<template>
    <div v-if="!isEnded" class="bg-gray-700/80 backdrop-blur-sm text-white px-3 py-1.5 rounded-full text-xs font-bold tracking-widest flex items-center gap-1 shadow-lg">
        <span v-if="timeLeft.days > 0">{{ pad(timeLeft.days) }}G : </span>
        <span>{{ pad(timeLeft.hours) }}S : </span>
        <span>{{ pad(timeLeft.minutes) }}D : </span>
        <span class="text-red-400">{{ pad(timeLeft.seconds) }}Sn</span>
    </div>
    <div v-else class="bg-red-600/90 backdrop-blur-sm text-white px-3 py-1.5 rounded-full text-xs font-bold tracking-widest shadow-lg">
        SONA ERDİ
    </div>
</template>