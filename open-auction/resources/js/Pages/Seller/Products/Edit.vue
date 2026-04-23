<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    product: Object,
    categories: Array
});

// Görsel önizlemelerini ve yükleme statülerini tutan ana dizi
const imagePreviews = ref([]);

const form = useForm({
    _method: 'put', // Laravel'in PUT isteklerinde dosya/array alabilmesi için şart
    title: props.product.title,
    category_id: props.product.category_id,
    listing_type: props.product.listing_type,
    price: props.product.listing_type === 'direct' 
            ? props.product.buy_now_price 
            : props.product.auction?.starting_price,
    stock: props.product.stock || 1,
    end_date: props.product.auction?.end_time ? props.product.auction.end_time.slice(0, 16) : '',
    images: [], // Veritabanına kaydedilecek dosya yolları (String dizisi)
    cover_index: 0,
});

// Sayfa açıldığında mevcut resimleri "yüklenmiş" olarak içeri al
onMounted(() => {
    if (props.product.images && props.product.images.length > 0) {
        props.product.images.forEach((img, index) => {
            imagePreviews.value.push({
                id: img.id,
                url: `/storage/${img.image_path}`,
                loading: false, // Mevcut resim olduğu için yükleme barı çıkmaz
                progress: 100,
                success: true
            });
            // Mevcut yolu form dizisine ekle
            form.images.push(img.image_path);
            // Mevcut kapak hangisiyse işaretle
            if (img.is_cover) form.cover_index = index;
        });
    }
});

// Yeni resim seçildiğinde asenkron yükleme başlat
const handleImageUpload = (e) => {
    const files = Array.from(e.target.files);
    
    if (imagePreviews.value.length + files.length > 8) {
        alert('Toplam resim sayısı 8\'i geçemez!');
        return;
    }

    files.forEach((file) => {
        const uniqueId = 'new_' + Math.random().toString(36).substring(7);
        const previewUrl = URL.createObjectURL(file);
        
        imagePreviews.value.push({
            id: uniqueId,
            url: previewUrl,
            loading: true,
            progress: 0,
            success: false
        });

        const formData = new FormData();
        formData.append('image', file);

        axios.post(route('seller.products.temp-upload'), formData, {
            onUploadProgress: (p) => {
                const percentage = Math.round((p.loaded * 100) / p.total);
                const item = imagePreviews.value.find(img => img.id === uniqueId);
                if (item) item.progress = percentage;
            }
        }).then(res => {
            const item = imagePreviews.value.find(img => img.id === uniqueId);
            if (item) {
                item.loading = false;
                item.success = true;
                form.images.push(res.data.path);
            }
        }).catch(err => {
            alert("Resim yüklenirken hata oluştu.");
            removeImage(imagePreviews.value.findIndex(img => img.id === uniqueId));
        });
    });
};

const removeImage = (index) => {
    imagePreviews.value.splice(index, 1);
    form.images.splice(index, 1);
    
    if (form.cover_index === index) {
        form.cover_index = 0;
    } else if (form.cover_index > index) {
        form.cover_index--;
    }
};

const submit = () => {
    form.post(route('seller.products.update', props.product.id), {
        preserveScroll: true,
        onSuccess: () => alert('Değişiklikler kaydedildi!')
    });
};
</script>

<template>
    <Head title="Ürünü Düzenle" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Ürünü Düzenle <span class="text-gray-400 text-sm ml-2">#{{ product.id }}</span>
                </h2>
                <Link :href="route('seller.products.index')" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                    ← Ürünlere Geri Dön
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-xl border border-gray-100 overflow-hidden">
                    
                    <form @submit.prevent="submit" class="p-6 md:p-10 space-y-12">
                        
                        <section>
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Ürün Görselleri</h3>
                                    <p class="text-xs text-gray-500 mt-1">En az 1, en fazla 8 görsel ekleyebilirsiniz.</p>
                                </div>
                                <span class="text-xs font-bold px-2 py-1 bg-gray-100 rounded text-gray-600">{{ imagePreviews.length }} / 8</span>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                <div v-for="(img, index) in imagePreviews" :key="img.id" 
                                     class="relative aspect-square rounded-2xl border border-gray-200 overflow-hidden group bg-gray-50 shadow-sm transition-all duration-300">
                                    
                                    <img :src="img.url" class="w-full h-full object-cover" :class="{'opacity-40 grayscale': img.loading}" />

                                    <div v-if="img.loading" class="absolute inset-0 flex flex-col items-center justify-center p-4 bg-white/40">
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 mb-2 overflow-hidden">
                                            <div class="bg-indigo-600 h-full transition-all duration-300" :style="{ width: img.progress + '%' }"></div>
                                        </div>
                                        <span class="text-[10px] font-black text-indigo-700">%{{ img.progress }}</span>
                                    </div>

                                    <div v-if="!img.loading" class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
                                        <button @click.prevent="form.cover_index = index" 
                                                :class="form.cover_index === index ? 'bg-green-500 text-white' : 'bg-white text-gray-900 hover:bg-green-500 hover:text-white'"
                                                class="px-3 py-1.5 text-[10px] font-bold rounded-lg shadow-sm transition-all transform hover:scale-105">
                                            {{ form.cover_index === index ? '★ KAPAK' : 'Kapak Yap' }}
                                        </button>
                                        <button @click.prevent="removeImage(index)" 
                                                class="px-3 py-1.5 text-[10px] font-bold bg-white text-red-600 hover:bg-red-600 hover:text-white rounded-lg shadow-sm transition-all transform hover:scale-105">
                                            Görseli Sil
                                        </button>
                                    </div>

                                    <div v-if="form.cover_index === index" class="absolute top-3 left-3 bg-indigo-600 text-white text-[9px] font-black px-2 py-1 rounded-md shadow-lg">KAPAK</div>
                                </div>

                                <label v-if="imagePreviews.length < 8" class="aspect-square flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-2xl hover:bg-indigo-50 hover:border-indigo-400 cursor-pointer transition-all group">
                                    <div class="p-3 rounded-full bg-gray-50 group-hover:bg-indigo-100 transition-colors mb-2">
                                        <svg class="w-6 h-6 text-gray-400 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </div>
                                    <span class="text-xs font-bold text-gray-500 group-hover:text-indigo-600 uppercase tracking-tighter">Yeni Resim Ekle</span>
                                    <input type="file" @change="handleImageUpload" multiple accept="image/*" class="hidden" />
                                </label>
                            </div>
                        </section>

                        <hr class="border-gray-100">

                        <section class="grid grid-cols-1 md:grid-cols-2 gap-10">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Ürün Adı</label>
                                    <input v-model="form.title" type="text" class="block w-full border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-sm py-3 px-4 shadow-sm" placeholder="iPhone 15 Pro...">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Kategori</label>
                                    <select v-model="form.category_id" class="block w-full border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-sm py-3 px-4 shadow-sm">
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">
                                        {{ form.listing_type === 'direct' ? 'Satış Fiyatı' : 'Başlangıç Fiyatı' }} (₺)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-4 flex items-center text-gray-400 font-bold">₺</span>
                                        <input v-model="form.price" type="number" class="block w-full border-gray-200 rounded-xl pl-10 focus:ring-indigo-500 focus:border-indigo-500 text-sm py-3 shadow-sm">
                                    </div>
                                </div>
                                <div v-if="form.listing_type === 'direct'">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Stok Adedi</label>
                                    <input v-model="form.stock" type="number" class="block w-full border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-sm py-3 px-4 shadow-sm">
                                </div>
                                <div v-if="form.listing_type === 'auction'">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">İhale Bitiş Zamanı</label>
                                    <input v-model="form.end_date" type="datetime-local" class="block w-full border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-sm py-3 px-4 shadow-sm">
                                </div>
                            </div>
                        </section>

                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-[11px] text-gray-500 leading-relaxed font-medium italic">
                                Bu ürün şu an <b>{{ form.listing_type === 'direct' ? 'HEMEN AL' : 'AÇIK ARTIRMA' }}</b> tipinde satıştadır. Ürün yayınlandıktan sonra satış tipi değiştirilemez. Eğer tipi değiştirmek isterseniz ürünü silip yeniden eklemelisiniz.
                            </span>
                        </div>

                    </form>

                    <div class="bg-white px-6 py-6 border-t border-gray-50 flex items-center justify-between md:px-10">
                        <button @click.prevent="history.back()" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors">Vazgeç</button>
                        <button @click="submit" :disabled="form.processing" class="bg-indigo-600 text-white px-10 py-3.5 rounded-xl text-sm font-black shadow-lg hover:bg-indigo-700 disabled:opacity-50 transition-all transform active:scale-95">
                            {{ form.processing ? 'DEĞİŞİKLİKLER KAYDEDİLİYOR...' : 'DEĞİŞİKLİKLERİ KAYDET' }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>